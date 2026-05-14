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

function parseRgb(value) {
    const match = String(value).match(/rgba?\((\d+),\s*(\d+),\s*(\d+)/);

    if (! match) {
        throw new Error(`Cannot parse CSS color: ${value}`);
    }

    return match.slice(1, 4).map(Number);
}

function luminance([r, g, b]) {
    const channel = value => {
        const scaled = value / 255;

        return scaled <= 0.03928
            ? scaled / 12.92
            : ((scaled + 0.055) / 1.055) ** 2.4;
    };

    return (0.2126 * channel(r)) + (0.7152 * channel(g)) + (0.0722 * channel(b));
}

function contrast(rgbA, rgbB) {
    const light = Math.max(luminance(rgbA), luminance(rgbB));
    const dark = Math.min(luminance(rgbA), luminance(rgbB));

    return (light + 0.05) / (dark + 0.05);
}

async function collectComponent(page, selector) {
    return page.evaluate((selector) => {
        const node = document.querySelector(selector);
        const viewportWidth = window.innerWidth;
        const ignored = '.phpdebugbar, #admin_bar, .grimba-mobile-nav, .grimba-command-palette, .grimba-chips__row';
        const rect = node ? node.getBoundingClientRect() : null;
        const active = node ? node.querySelector('[aria-selected="true"]') : null;
        const activeStyle = active ? getComputedStyle(active) : null;

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

        return {
            exists: Boolean(node),
            selector,
            text: node ? node.textContent.replace(/\s+/g, ' ').trim().slice(0, 240) : '',
            viewportWidth,
            scrollWidth: document.documentElement.scrollWidth,
            rect: rect ? {
                left: Math.round(rect.left),
                right: Math.round(rect.right),
                width: Math.round(rect.width),
            } : null,
            display: node ? getComputedStyle(node).display : null,
            gridTemplateColumns: node ? getComputedStyle(node).gridTemplateColumns : null,
            childOffenders,
            documentOffenders,
            activeColor: activeStyle ? activeStyle.color : null,
            activeBackground: activeStyle ? activeStyle.backgroundColor : null,
        };
    }, selector);
}

async function collectModal(page) {
    return page.evaluate(() => {
        const viewportWidth = window.innerWidth;
        const panel = document.querySelector('#grimba-compare-modal .grimba-compare-modal__panel');
        const close = document.querySelector('#grimba-compare-modal .grimba-newsletter-modal__close');
        const title = document.querySelector('#grimba-compare-title');
        const panelRect = panel ? panel.getBoundingClientRect() : null;
        const closeRect = close ? close.getBoundingClientRect() : null;
        const titleRect = title ? title.getBoundingClientRect() : null;
        const cards = Array.from(document.querySelectorAll('#grimba-compare-modal .grimba-compare-modal__card'));

        return {
            viewportWidth,
            scrollWidth: document.documentElement.scrollWidth,
            panel: panelRect ? {
                left: Math.round(panelRect.left),
                right: Math.round(panelRect.right),
                top: Math.round(panelRect.top),
                bottom: Math.round(panelRect.bottom),
                width: Math.round(panelRect.width),
            } : null,
            close: closeRect ? {
                left: Math.round(closeRect.left),
                right: Math.round(closeRect.right),
                top: Math.round(closeRect.top),
                bottom: Math.round(closeRect.bottom),
            } : null,
            title: titleRect ? {
                left: Math.round(titleRect.left),
                right: Math.round(titleRect.right),
                top: Math.round(titleRect.top),
                bottom: Math.round(titleRect.bottom),
            } : null,
            cardCount: cards.length,
            cardWidths: cards.map(card => Math.round(card.getBoundingClientRect().width)),
            text: panel ? panel.textContent.replace(/\s+/g, ' ').trim().slice(0, 300) : '',
        };
    });
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

function assertModal(metrics, scenarioKey) {
    assert(metrics.panel, `${scenarioKey} compare modal panel exists`);
    assert(metrics.close, `${scenarioKey} compare modal close exists`);
    assert(metrics.title, `${scenarioKey} compare modal title exists`);
    assert(metrics.panel.left >= 0, `${scenarioKey} modal panel left ${metrics.panel.left}`);
    assert(metrics.panel.right <= metrics.viewportWidth + 1, `${scenarioKey} modal panel right ${metrics.panel.right}/${metrics.viewportWidth}`);
    assert(metrics.panel.top >= 0, `${scenarioKey} modal panel top ${metrics.panel.top}`);
    assert(metrics.scrollWidth <= metrics.viewportWidth + 1, `${scenarioKey} modal document overflow ${metrics.scrollWidth}/${metrics.viewportWidth}`);
    assert.equal(metrics.cardCount, 2, `${scenarioKey} selected compare cards`);
    assert(metrics.close.left > metrics.title.right || metrics.close.top > metrics.title.bottom || metrics.close.bottom < metrics.title.top, `${scenarioKey} close overlaps title`);
    assert(/Comparer le cadrage/.test(metrics.text), `${scenarioKey} modal title copy`);
}

(async () => {
    const { chromium } = loadPlaywright();
    const baseUrl = (process.env.GRIMBANEWS_BASE_URL || 'http://127.0.0.1:8007').replace(/\/$/, '');
    const storyPath = process.env.GRIMBANEWS_STORY_ARTICLE_PATH
        || '/article/en-direct-guerre-au-moyen-orient-le-chef-de-la-diplomatie-iranienne-abbas-araghtchi-est-attendu-au-pakistan-pour-des-pourparlers-le-hezbollah-appelle-le-liban-a-se-retirer-des-negociations-avec-israel';
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

        await page.locator('.grimba-story-page__actions').first().scrollIntoViewIfNeeded();
        const actions = await collectComponent(page, '.grimba-story-page__actions');
        const heroTabs = await collectComponent(page, '.grimba-story-page__tablist');
        assertComponent(actions, scenario.key);
        assertComponent(heroTabs, scenario.key);
        assert(/Tous/.test(heroTabs.text), `${scenario.key} hero tabs copy`);

        if (scenario.key.startsWith('mobile')) {
            assert.equal(heroTabs.display, 'grid', `${scenario.key} hero tabs grid`);
            assert(heroTabs.gridTemplateColumns.split(' ').length >= 3, `${scenario.key} hero tabs columns ${heroTabs.gridTemplateColumns}`);
        }

        await page.locator('.grimba-story-articles__tabs').first().scrollIntoViewIfNeeded();
        const articleTabs = await collectComponent(page, '.grimba-story-articles__tabs');
        assertComponent(articleTabs, scenario.key);
        assert(/Tous/.test(articleTabs.text), `${scenario.key} article tabs copy`);
        assert.equal(articleTabs.display, 'grid', `${scenario.key} article tabs grid`);

        const activeContrast = contrast(parseRgb(articleTabs.activeColor), parseRgb(articleTabs.activeBackground));
        assert(activeContrast >= 4.5, `${scenario.key} active tab contrast ${activeContrast.toFixed(2)}`);

        await page.locator('.grimba-story-article').first().scrollIntoViewIfNeeded();
        const articleCard = await collectComponent(page, '.grimba-story-article');
        const sourceRow = await collectComponent(page, '.grimba-story-article__source-row');
        const compareToggle = await collectComponent(page, '.grimba-compare-toggle');
        assertComponent(articleCard, scenario.key);
        assertComponent(sourceRow, scenario.key);
        assertComponent(compareToggle, scenario.key);
        assert(articleCard.text.length > 80, `${scenario.key} article card has readable text`);
        assert(compareToggle.rect.width >= 34, `${scenario.key} compare target width ${compareToggle.rect.width}`);

        await page.evaluate(() => {
            document.querySelectorAll('[data-grimba-compare-toggle]').forEach((input, index) => {
                if (index < 2) {
                    input.checked = true;
                    input.dispatchEvent(new Event('change', { bubbles: true }));
                }
            });
        });

        await page.locator('.grimba-compare-toolbar').scrollIntoViewIfNeeded();
        const toolbar = await collectComponent(page, '.grimba-compare-toolbar');
        assertComponent(toolbar, scenario.key);
        assert(/2/.test(toolbar.text), `${scenario.key} compare toolbar count`);

        await page.locator('[data-grimba-compare-open]').click();
        await page.waitForSelector('#grimba-compare-modal.is-open');
        const modal = await collectModal(page);
        assertModal(modal, scenario.key);

        results[scenario.key] = {
            heroTabs: heroTabs.rect,
            articleTabs: articleTabs.rect,
            articleCard: articleCard.rect,
            compareToggle: compareToggle.rect,
            toolbar: toolbar.rect,
            activeContrast: Number(activeContrast.toFixed(2)),
            modal: modal.panel,
        };

        await context.close();
    }

    await browser.close();
    console.log(JSON.stringify({ ok: true, baseUrl, results }, null, 2));
})().catch((error) => {
    console.error(error);
    process.exit(1);
});
