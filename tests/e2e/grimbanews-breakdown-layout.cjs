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

async function resolveComparisonPath(page, baseUrl) {
    if (process.env.GRIMBANEWS_BREAKDOWN_PATH) {
        return process.env.GRIMBANEWS_BREAKDOWN_PATH;
    }

    await page.goto('/comparatif', { waitUntil: 'networkidle' });
    const href = await page.locator('a[href*="/comparatif/"]').first().getAttribute('href');
    assert.ok(href, 'comparison index exposes at least one dossier link');

    return new URL(href, baseUrl).pathname;
}

async function inspectPanel(page, panel) {
    await page.locator(`.grimba-breakdown__tab[for$="-${panel}"]`).click();
    await page.waitForTimeout(90);

    return page.evaluate((panel) => {
        const bounds = element => {
            const box = element.getBoundingClientRect();

            return {
                left: Math.round(box.left),
                right: Math.round(box.right),
                top: Math.round(box.top),
                bottom: Math.round(box.bottom),
                width: Math.round(box.width),
                height: Math.round(box.height),
            };
        };
        const viewportWidth = window.innerWidth;
        const root = document.querySelector('.grimba-breakdown');
        const active = document.querySelector(`.grimba-breakdown__panel[data-panel="${panel}"]`);
        const tabs = document.querySelector('.grimba-breakdown__tabs');
        const ignored = '.phpdebugbar, #admin_bar, .grimba-mobile-nav, .grimba-command-palette, .grimba-chips__row';
        const visible = node => {
            const style = getComputedStyle(node);

            return style.display !== 'none'
                && style.visibility !== 'hidden'
                && style.position !== 'fixed'
                && Number(style.opacity) !== 0;
        };
        const visibleBox = node => {
            if (! visible(node)) {
                return false;
            }

            const box = node.getBoundingClientRect();

            return box.width > 1 && box.height > 1;
        };
        const offenders = Array.from(document.body.querySelectorAll('*'))
            .filter(node => {
                if (node.closest(ignored) || ! visible(node)) {
                    return false;
                }

                const box = node.getBoundingClientRect();

                return box.width > 1 && box.height > 1 && (box.left < -2 || box.right > viewportWidth + 2);
            })
            .slice(0, 10)
            .map(node => ({
                tag: node.tagName.toLowerCase(),
                className: String(node.className || '').slice(0, 120),
                rect: bounds(node),
            }));
        const ownerRows = Array.from(document.querySelectorAll('.grimba-breakdown__owner-row'))
            .filter(visibleBox)
            .map(node => ({
                rect: bounds(node),
                columns: getComputedStyle(node).gridTemplateColumns,
                text: node.textContent.replace(/\s+/g, ' ').trim().slice(0, 140),
            }));
        const ownerSummary = document.querySelector('.grimba-breakdown__owner-summary-card');
        const donut = document.querySelector('.grimba-breakdown__donut');
        const originCards = Array.from(document.querySelectorAll('.grimba-breakdown__origin-card'))
            .filter(visibleBox)
            .map(bounds);

        return {
            panel,
            viewportWidth,
            scrollWidth: document.documentElement.scrollWidth,
            root: root ? bounds(root) : null,
            active: active ? bounds(active) : null,
            tabs: tabs ? bounds(tabs) : null,
            tabsColumns: tabs ? getComputedStyle(tabs).gridTemplateColumns.split(' ').filter(Boolean).length : 0,
            ownerSummary: ownerSummary && visibleBox(ownerSummary) ? bounds(ownerSummary) : null,
            donut: donut && visibleBox(donut) ? bounds(donut) : null,
            ownerRows,
            originCards,
            offenders,
        };
    }, panel);
}

function assertContained(metrics, scenario) {
    assert.ok(metrics.root, `${scenario} breakdown root exists`);
    assert.ok(metrics.active, `${scenario} ${metrics.panel} panel exists`);
    assert.ok(metrics.tabs, `${scenario} tabs exist`);
    assert.ok(metrics.scrollWidth <= metrics.viewportWidth + 1, `${scenario} ${metrics.panel} document overflow ${metrics.scrollWidth}/${metrics.viewportWidth}`);
    assert.ok(metrics.root.left >= -1, `${scenario} root left ${metrics.root.left}`);
    assert.ok(metrics.root.right <= metrics.viewportWidth + 1, `${scenario} root right ${metrics.root.right}/${metrics.viewportWidth}`);
    assert.ok(metrics.active.right <= metrics.root.right + 1, `${scenario} ${metrics.panel} panel overflows root`);
    assert.ok(metrics.tabs.right <= metrics.root.right + 1, `${scenario} tabs overflow root`);
    assert.equal(metrics.offenders.length, 0, `${scenario} ${metrics.panel} viewport offenders: ${JSON.stringify(metrics.offenders)}`);

    if (metrics.panel === 'owner') {
        assert.ok(metrics.ownerRows.length > 0, `${scenario} owner rows render`);
        for (const row of metrics.ownerRows) {
            assert.ok(row.rect.left >= metrics.root.left - 1, `${scenario} owner row left ${row.rect.left}`);
            assert.ok(row.rect.right <= metrics.root.right + 1, `${scenario} owner row right ${row.rect.right}/${metrics.root.right}`);
        }
        assert.ok(metrics.ownerSummary, `${scenario} owner summary card renders`);
        assert.ok(metrics.donut, `${scenario} owner donut renders`);
        assert.ok(metrics.donut.right <= metrics.ownerSummary.right + 1, `${scenario} donut stays inside summary`);
    }

    if (metrics.panel === 'origin') {
        assert.ok(metrics.originCards.length > 0, `${scenario} origin cards render`);
        for (const card of metrics.originCards) {
            assert.ok(card.right <= metrics.root.right + 1, `${scenario} origin card right ${card.right}/${metrics.root.right}`);
        }
    }
}

(async () => {
    const { chromium } = loadPlaywright();
    const baseUrl = (process.env.GRIMBANEWS_BASE_URL || 'http://127.0.0.1:8007').replace(/\/$/, '');
    const launchOptions = { headless: process.env.PLAYWRIGHT_HEADLESS !== '0' };

    if (process.env.PLAYWRIGHT_CHROMIUM_EXECUTABLE) {
        launchOptions.executablePath = process.env.PLAYWRIGHT_CHROMIUM_EXECUTABLE;
    }

    const browser = await chromium.launch(launchOptions);
    const results = {};
    let comparisonPath;

    try {
        for (const scenario of [
            { key: 'desktopLight', viewport: { width: 1440, height: 1100 }, theme: 'light', expectedTabColumns: 4 },
            { key: 'mobileDark', viewport: { width: 390, height: 844 }, theme: 'dark', expectedTabColumns: 4 },
            { key: 'narrowDark', viewport: { width: 320, height: 844 }, theme: 'dark', expectedTabColumns: 4 },
        ]) {
            const context = await browser.newContext({
                baseURL: baseUrl,
                viewport: scenario.viewport,
                deviceScaleFactor: scenario.key === 'desktopLight' ? 1 : 2,
            });

            await context.addCookies([
                { name: 'grimba_lang', value: 'fr', url: baseUrl },
                { name: 'grimba_onboarded', value: '1', url: baseUrl },
                { name: 'grimba_cookie_consent', value: 'necessary', url: baseUrl },
                { name: 'grimba_theme', value: scenario.theme, url: baseUrl },
            ]);

            const page = await context.newPage();
            comparisonPath ??= await resolveComparisonPath(page, baseUrl);
            const response = await page.goto(comparisonPath, { waitUntil: 'networkidle' });
            assert.ok(response && response.ok(), `${scenario.key} comparison response ${response && response.status()}`);
            await page.locator('.grimba-breakdown').scrollIntoViewIfNeeded();

            results[scenario.key] = {};
            for (const panel of ['bias', 'origin', 'fact', 'owner']) {
                const metrics = await inspectPanel(page, panel);
                assertContained(metrics, scenario.key);
                assert.equal(metrics.tabsColumns, scenario.expectedTabColumns, `${scenario.key} tabs keep four stable columns`);
                results[scenario.key][panel] = {
                    scrollWidth: metrics.scrollWidth,
                    root: metrics.root,
                    active: metrics.active,
                    ownerRows: metrics.ownerRows.map(row => ({ rect: row.rect, columns: row.columns })),
                    originCards: metrics.originCards,
                };
            }

            await context.close();
        }
    } finally {
        await browser.close();
    }

    console.log(JSON.stringify({ ok: true, baseUrl, comparisonPath, results }, null, 2));
})().catch(error => {
    console.error(error);
    process.exit(1);
});
