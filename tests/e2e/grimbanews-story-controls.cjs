#!/usr/bin/env node
'use strict';

const assert = require('node:assert/strict');

function loadPlaywright() {
    const candidates = [
        process.env.PLAYWRIGHT_MODULE,
        'playwright',
        '/Users/vb/kaizen/kaizen/node_modules/playwright',
    ].filter(Boolean);

    for (const candidate of candidates) {
        try {
            return require(candidate);
        } catch (error) {
            if (error && error.code !== 'MODULE_NOT_FOUND') {
                throw error;
            }
        }
    }

    throw new Error('Playwright is not installed. Run npm install --no-save playwright, or set PLAYWRIGHT_MODULE.');
}

async function collectComponent(page, selector) {
    return page.evaluate((selector) => {
        const node = document.querySelector(selector);
        const viewportWidth = window.innerWidth;
        const ignored = '.phpdebugbar, #admin_bar, .grimba-mobile-nav, .grimba-command-palette, .grimba-chips__row, .grimba-breaking';
        const rect = node ? node.getBoundingClientRect() : null;

        const childOffenders = node
            ? Array.from(node.querySelectorAll('*'))
                .filter((child) => {
                    if (child.closest(ignored)) {
                        return false;
                    }

                    const style = getComputedStyle(child);
                    if (style.display === 'none' || style.visibility === 'hidden' || Number(style.opacity) === 0) {
                        return false;
                    }

                    const box = child.getBoundingClientRect();

                    return box.width > 1 && box.height > 1 && (box.left < rect.left - 2 || box.right > rect.right + 2);
                })
                .slice(0, 8)
                .map((child) => ({
                    tag: child.tagName.toLowerCase(),
                    className: String(child.className || '').slice(0, 120),
                    left: Math.round(child.getBoundingClientRect().left),
                    right: Math.round(child.getBoundingClientRect().right),
                }))
            : [];

        const documentOffenders = Array.from(document.body.querySelectorAll('*'))
            .filter((child) => {
                if (child.closest(ignored)) {
                    return false;
                }

                const style = getComputedStyle(child);
                if (style.display === 'none' || style.visibility === 'hidden' || style.position === 'fixed' || Number(style.opacity) === 0) {
                    return false;
                }

                const box = child.getBoundingClientRect();

                return box.width > 1 && box.height > 1 && (box.left < -2 || box.right > viewportWidth + 2);
            })
            .slice(0, 8)
            .map((child) => ({
                tag: child.tagName.toLowerCase(),
                className: String(child.className || '').slice(0, 120),
                left: Math.round(child.getBoundingClientRect().left),
                right: Math.round(child.getBoundingClientRect().right),
            }));

        const readableText = node
            ? (() => {
                const clone = node.cloneNode(true);
                clone.querySelectorAll('script, style').forEach(child => child.remove());

                return clone.textContent.replace(/\s+/g, ' ').trim().slice(0, 300);
            })()
            : '';

        return {
            exists: Boolean(node),
            selector,
            text: readableText,
            viewportWidth,
            scrollWidth: document.documentElement.scrollWidth,
            rect: rect ? {
                left: Math.round(rect.left),
                right: Math.round(rect.right),
                width: Math.round(rect.width),
                height: Math.round(rect.height),
                top: Math.round(rect.top),
            } : null,
            display: node ? getComputedStyle(node).display : null,
            gridTemplateColumns: node ? getComputedStyle(node).gridTemplateColumns : null,
            childOffenders,
            documentOffenders,
        };
    }, selector);
}

function assertComponent(metrics, scenarioKey) {
    assert.equal(metrics.exists, true, `${scenarioKey} ${metrics.selector} exists`);
    assert(metrics.rect.width <= metrics.viewportWidth + 1, `${scenarioKey} ${metrics.selector} width ${metrics.rect.width}/${metrics.viewportWidth}`);
    assert(metrics.rect.left >= -1, `${scenarioKey} ${metrics.selector} left ${metrics.rect.left}`);
    assert(metrics.rect.right <= metrics.viewportWidth + 1, `${scenarioKey} ${metrics.selector} right ${metrics.rect.right}/${metrics.viewportWidth}`);
    assert(metrics.scrollWidth <= metrics.viewportWidth + 1, `${scenarioKey} document overflow ${metrics.scrollWidth}/${metrics.viewportWidth}`);
    assert.deepEqual(metrics.childOffenders, [], `${scenarioKey} ${metrics.selector} child overflow`);
    assert.deepEqual(metrics.documentOffenders, [], `${scenarioKey} page overflow`);
}

async function collectActionMetrics(page, selector) {
    return page.locator(selector).first().evaluate((actions) => {
        const compare = actions.querySelector('.grimba-story-page__compare');
        const save = actions.querySelector('.grimba-save-btn');
        const compareShort = actions.querySelector('.grimba-story-page__compare-label--short');
        const compareFull = actions.querySelector('.grimba-story-page__compare-label--full');
        const box = node => {
            const rect = node.getBoundingClientRect();

            return {
                top: Math.round(rect.top),
                width: Math.round(rect.width),
                height: Math.round(rect.height),
                right: Math.round(rect.right),
            };
        };

        return {
            display: getComputedStyle(actions).display,
            compare: compare ? box(compare) : null,
            save: save ? box(save) : null,
            shortDisplay: compareShort ? getComputedStyle(compareShort).display : '',
            fullDisplay: compareFull ? getComputedStyle(compareFull).display : '',
        };
    });
}

async function exerciseBiasFilter(page, scenarioKey) {
    await page.locator('.grimba-story-distribution').first().scrollIntoViewIfNeeded();

    const rowCount = await page.locator('[data-grimba-voices-row]').count();
    const panelCount = await page.locator('[data-grimba-voices-panel]').count();
    const chipCount = await page.locator('[data-grimba-spectrum-chip]').count();
    assert(rowCount > 0, `${scenarioKey} has filterable source rows`);
    assert(panelCount >= 3, `${scenarioKey} has per-side voice panels`);
    assert(chipCount > 0, `${scenarioKey} has spectrum chips`);

    const selectedSide = await page.evaluate(() => {
        const rowBiases = new Set(Array.from(document.querySelectorAll('[data-grimba-voices-row]'))
            .map(row => row.dataset.bias)
            .filter(Boolean));
        const segments = Array.from(document.querySelectorAll('[data-grimba-bar-side]'));
        const matchingSegment = segments.find(segment => rowBiases.has(segment.dataset.grimbaBarSide)) || segments[0];

        return matchingSegment?.dataset.grimbaBarSide || '';
    });
    assert(selectedSide, `${scenarioKey} has a distribution segment with a side`);
    const segment = page.locator(`[data-grimba-bar-side="${selectedSide}"]`).first();

    await segment.click();
    await page.waitForFunction(side => document.querySelector('[data-grimba-voices]')?.dataset.activeBias === side, selectedSide);

    const filtered = await page.evaluate((side) => {
        const visible = node => !node.hidden && getComputedStyle(node).display !== 'none';

        return {
            activeBias: document.querySelector('[data-grimba-voices]')?.dataset.activeBias || '',
            pressed: document.querySelector(`[data-grimba-bar-side="${side}"]`)?.getAttribute('aria-pressed') || '',
            panels: Array.from(document.querySelectorAll('[data-grimba-voices-panel]')).map(panel => ({
                bias: panel.dataset.bias,
                visible: visible(panel),
                ariaHidden: panel.getAttribute('aria-hidden'),
            })),
            rows: Array.from(document.querySelectorAll('[data-grimba-voices-row]')).map(row => ({
                bias: row.dataset.bias,
                visible: visible(row),
                ariaHidden: row.getAttribute('aria-hidden'),
            })),
            chips: Array.from(document.querySelectorAll('[data-grimba-spectrum-chip]')).map(chip => ({
                bias: chip.dataset.bias,
                active: chip.getAttribute('data-bias-active'),
                pressed: chip.getAttribute('aria-pressed'),
            })),
        };
    }, selectedSide);

    assert.equal(filtered.activeBias, selectedSide, `${scenarioKey} voices receive active bias`);
    assert.equal(filtered.pressed, 'true', `${scenarioKey} distribution segment is pressed`);
    assert(filtered.panels.some(panel => panel.bias === selectedSide && panel.visible), `${scenarioKey} selected voice panel remains visible`);
    assert(filtered.panels.filter(panel => panel.bias !== selectedSide).every(panel => !panel.visible && panel.ariaHidden === 'true'), `${scenarioKey} non-selected voice panels hide`);
    assert(filtered.rows.some(row => row.visible), `${scenarioKey} side filter leaves visible source rows`);
    assert(filtered.rows.filter(row => row.visible).every(row => row.bias === selectedSide), `${scenarioKey} source rows filter to ${selectedSide}`);
    assert(filtered.chips.filter(chip => chip.bias === selectedSide).every(chip => chip.active === 'true' && chip.pressed === 'true'), `${scenarioKey} selected spectrum chips are active`);
    assert(filtered.chips.filter(chip => chip.bias !== selectedSide).every(chip => chip.active === 'false' && chip.pressed === 'false'), `${scenarioKey} non-selected spectrum chips dim`);

    await segment.click();
    await page.waitForFunction(() => document.querySelector('[data-grimba-voices]')?.dataset.activeBias === 'all');

    const reset = await page.evaluate((side) => {
        const visible = node => !node.hidden && getComputedStyle(node).display !== 'none';

        return {
            activeBias: document.querySelector('[data-grimba-voices]')?.dataset.activeBias || '',
            pressed: document.querySelector(`[data-grimba-bar-side="${side}"]`)?.getAttribute('aria-pressed') || '',
            panels: Array.from(document.querySelectorAll('[data-grimba-voices-panel]')).map(panel => visible(panel)),
            rows: Array.from(document.querySelectorAll('[data-grimba-voices-row]')).map(row => visible(row)),
            chips: Array.from(document.querySelectorAll('[data-grimba-spectrum-chip]')).map(chip => ({
                active: chip.getAttribute('data-bias-active'),
                pressed: chip.getAttribute('aria-pressed'),
            })),
        };
    }, selectedSide);

    assert.equal(reset.activeBias, 'all', `${scenarioKey} second click resets voices`);
    assert.equal(reset.pressed, 'false', `${scenarioKey} distribution segment resets`);
    assert(reset.panels.every(Boolean), `${scenarioKey} reset shows all voice panels`);
    assert(reset.rows.every(Boolean), `${scenarioKey} reset shows all source rows`);
    assert(reset.chips.every(chip => chip.active === 'true' && chip.pressed === 'false'), `${scenarioKey} reset reactivates spectrum chips`);

    return { selectedSide, rowCount, panelCount, chipCount };
}

(async () => {
    const { chromium } = loadPlaywright();
    const baseUrl = (process.env.GRIMBANEWS_BASE_URL || 'http://127.0.0.1:8003').replace(/\/$/, '');
    const storyPath = process.env.GRIMBANEWS_STORY_ARTICLE_PATH
        || '/article/en-direct-guerre-au-moyen-orient-le-chef-de-la-diplomatie-iranienne-abbas-araghtchi-est-attendu-au-pakistan-pour-des-pourparlers-le-hezbollah-appelle-le-liban-a-se-retirer-des-negociations-avec-israel';
    const storyActionsSelector = '.grimba-story-page__bar-actions, .grimba-story-page__actions, .grimba-orphan-actions';
    const launchOptions = { headless: process.env.PLAYWRIGHT_HEADLESS !== '0' };

    if (process.env.PLAYWRIGHT_CHROMIUM_EXECUTABLE) {
        launchOptions.executablePath = process.env.PLAYWRIGHT_CHROMIUM_EXECUTABLE;
    }

    const browser = await chromium.launch(launchOptions);
    const results = {};

    for (const scenario of [
        { key: 'desktopLight', viewport: { width: 1440, height: 1100 }, theme: 'light' },
        { key: 'mobileLight', viewport: { width: 390, height: 844 }, theme: 'light' },
        { key: 'mobileDark', viewport: { width: 390, height: 844 }, theme: 'dark' },
    ]) {
        const context = await browser.newContext({
            baseURL: baseUrl,
            viewport: scenario.viewport,
            deviceScaleFactor: scenario.key.startsWith('mobile') ? 2 : 1,
        });

        await context.addCookies([
            { name: 'grimba_lang', value: 'fr', url: baseUrl },
            { name: 'grimba_onboarded', value: '1', url: baseUrl },
            { name: 'grimba_cookie_consent', value: 'necessary', url: baseUrl },
            { name: 'grimba_theme', value: scenario.theme, url: baseUrl },
        ]);

        const page = await context.newPage();
        const response = await page.goto(storyPath, { waitUntil: 'networkidle' });
        assert(response && response.ok(), `${scenario.key} response ${response && response.status()}`);

        await page.locator(storyActionsSelector).first().scrollIntoViewIfNeeded();
        const actions = await collectComponent(page, storyActionsSelector);
        const articleCard = await collectComponent(page, '.grimba-article-card');
        const shareKit = await collectComponent(page, '.grimba-share-kit');
        assertComponent(actions, scenario.key);
        assertComponent(articleCard, scenario.key);
        assertComponent(shareKit, scenario.key);
        assert(/Sauvegarder/.test(actions.text), `${scenario.key} action bar includes save`);
        assert(/Lu chez|Source|ARTICLE/.test(articleCard.text), `${scenario.key} article card keeps source context`);

        const actionMetrics = await collectActionMetrics(page, storyActionsSelector);
        assert(actionMetrics.compare, `${scenario.key} compare/source-analysis action exists`);
        assert(actionMetrics.save, `${scenario.key} save action exists`);

        if (scenario.key.startsWith('mobile')) {
            assert.equal(actionMetrics.display, 'grid', `${scenario.key} action bar uses mobile grid`);
            assert(actionMetrics.compare.height >= 44, `${scenario.key} compare action tap target`);
            assert(actionMetrics.save.height >= 44, `${scenario.key} save action tap target`);
            assert.notEqual(actionMetrics.shortDisplay, 'none', `${scenario.key} concise compare label is visible`);
            assert.equal(actionMetrics.fullDisplay, 'none', `${scenario.key} long compare label is hidden`);
        } else {
            assert.equal(actionMetrics.display, 'flex', `${scenario.key} action bar uses desktop row`);
            assert(actionMetrics.compare.height >= 36, `${scenario.key} compare action desktop target`);
            assert(actionMetrics.save.height >= 36, `${scenario.key} save action desktop target`);
            assert.notEqual(actionMetrics.fullDisplay, 'none', `${scenario.key} full compare label is visible`);
        }

        await page.locator('.grimba-story-page__compare').first().click();
        await page.waitForFunction((height) => {
            const node = document.querySelector('.grimba-story-distribution');
            if (!node) return false;

            const rect = node.getBoundingClientRect();
            return rect.top < height && rect.bottom > 0;
        }, scenario.viewport.height);
        const distributionTop = await page.locator('.grimba-story-distribution').first().evaluate(node => Math.round(node.getBoundingClientRect().top));
        assert(distributionTop < scenario.viewport.height, `${scenario.key} source-analysis action scrolls to distribution`);

        await page.locator('.grimba-voices').first().scrollIntoViewIfNeeded();
        const voices = await collectComponent(page, '.grimba-voices');
        const voiceGrid = await collectComponent(page, '.grimba-voices__grid');
        const voiceTable = await collectComponent(page, '.grimba-voices__table-wrap');
        assertComponent(voices, scenario.key);
        assertComponent(voiceGrid, scenario.key);
        assertComponent(voiceTable, scenario.key);
        assert(/Trois angles|Toutes les sources/.test(voices.text), `${scenario.key} voices module copy`);

        await page.locator('.grimba-story-distribution').first().scrollIntoViewIfNeeded();
        const distribution = await collectComponent(page, '.grimba-story-distribution');
        const spectrum = await collectComponent(page, '.grimba-story-spectrum');
        assertComponent(distribution, scenario.key);
        assertComponent(spectrum, scenario.key);
        assert(/Distribution des biais|Signal/.test(distribution.text), `${scenario.key} distribution module copy`);

        const filter = await exerciseBiasFilter(page, scenario.key);

        await page.locator('.grimba-story-timeline').first().scrollIntoViewIfNeeded();
        const timeline = await collectComponent(page, '.grimba-story-timeline');
        assertComponent(timeline, scenario.key);
        assert(/Chronologie/.test(timeline.text), `${scenario.key} timeline module copy`);

        const externalSourceLinks = await page.locator('.grimba-voices__cta[href^="http"], .grimba-voices__row-headline[href^="http"]').evaluateAll((links, origin) => links
            .map(link => link.href)
            .filter(href => !href.startsWith(origin)), `${baseUrl}/`);
        assert.equal(externalSourceLinks.length, 0, `${scenario.key} voices links stay inside GrimbaNews`);

        results[scenario.key] = {
            actions: actions.rect,
            articleCard: articleCard.rect,
            voices: voices.rect,
            distribution: distribution.rect,
            spectrum: spectrum.rect,
            timeline: timeline.rect,
            filter,
        };

        await context.close();
    }

    await browser.close();
    console.log(JSON.stringify({ ok: true, baseUrl, results }, null, 2));
})().catch((error) => {
    console.error(error);
    process.exit(1);
});
