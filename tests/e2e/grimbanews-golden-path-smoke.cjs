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

async function firstVisible(locator, label) {
    const item = await maybeFirstVisible(locator);
    if (item) {
        return item;
    }

    throw new Error(`No visible ${label} found.`);
}

async function maybeFirstVisible(locator) {
    const count = await locator.count();
    for (let index = 0; index < count; index += 1) {
        const item = locator.nth(index);
        if (await item.isVisible()) {
            return item;
        }
    }

    return null;
}

function storyLinks(page) {
    return page.locator([
        '.grimba-briefing__headline',
        '.grimba-hero__media',
        '.grimba-topnews__headline',
        '.grimba-latest__headline',
        '.grimba-most-read__headline',
        'a[href*="/article/"]',
    ].join(', ')).filter({ hasText: /.+/ });
}

async function waitForVaultCookie(page, postId, shouldContain) {
    await page.waitForFunction(
        ([expectedPostId, expectedState]) => {
            const match = document.cookie.match(/(?:^|; )grimba_vault=([^;]*)/);
            const value = match ? decodeURIComponent(match[1]) : '';
            const ids = value.split(',').filter(Boolean);
            return expectedState ? ids.includes(expectedPostId) : ! ids.includes(expectedPostId);
        },
        [postId, shouldContain],
        { timeout: 5000 }
    );
}

(async () => {
    const { chromium } = loadPlaywright();
    const baseUrl = (process.env.GRIMBANEWS_BASE_URL || 'http://127.0.0.1:8003').replace(/\/$/, '');
    const launchOptions = { headless: process.env.PLAYWRIGHT_HEADLESS !== '0' };
    if (process.env.PLAYWRIGHT_CHROMIUM_EXECUTABLE) {
        launchOptions.executablePath = process.env.PLAYWRIGHT_CHROMIUM_EXECUTABLE;
    }
    const browser = await chromium.launch(launchOptions);
    const context = await browser.newContext({
        viewport: { width: 390, height: 844 },
        colorScheme: 'dark',
        baseURL: baseUrl,
    });
    const page = await context.newPage();

    try {
        await context.addCookies([
            { name: 'grimba_lang', value: 'en', url: baseUrl },
            { name: 'grimba_onboarded', value: '1', url: baseUrl },
            { name: 'grimba_theme', value: 'dark', url: baseUrl },
        ]);

        await page.goto('/', { waitUntil: 'domcontentloaded' });
        await page.waitForSelector('.grimba-chip__label', { timeout: 10000 });

        const topicChip = await firstVisible(page.locator('.grimba-chip__label'), 'topic chip');
        await Promise.all([
            page.waitForLoadState('domcontentloaded'),
            topicChip.click(),
        ]);

        let storyLink = await maybeFirstVisible(storyLinks(page));
        if (! storyLink) {
            await page.goto('/', { waitUntil: 'domcontentloaded' });
            storyLink = await firstVisible(storyLinks(page), 'story link');
        }

        await Promise.all([
            page.waitForLoadState('domcontentloaded'),
            storyLink.click(),
        ]);

        await page.waitForSelector('[data-grimba-save]', { timeout: 10000 });
        const saveButton = await firstVisible(page.locator('[data-grimba-save]'), 'save button');
        const postId = await saveButton.getAttribute('data-grimba-save');
        assert.match(postId || '', /^[1-9][0-9]*$/, 'save button exposes a post id');

        await saveButton.click();
        await waitForVaultCookie(page, postId, true);

        await page.goto('/coffre', { waitUntil: 'domcontentloaded' });
        await page.waitForSelector(`[data-post-id="${postId}"]`, { timeout: 10000 });
        assert.ok(await page.locator(`[data-post-id="${postId}"]`).first().isVisible(), 'saved article appears in vault');

        const removeButton = page.locator(`[data-grimba-vault-remove="${postId}"]`).first();
        if (await removeButton.count()) {
            await removeButton.click();
        } else {
            await page.locator(`[data-grimba-save="${postId}"]`).first().click();
        }

        await waitForVaultCookie(page, postId, false);

        await page.goto('/coffre', { waitUntil: 'domcontentloaded' });
        await page.waitForLoadState('networkidle').catch(() => {});
        const remainingSavedArticle = await page.locator(`[data-post-id="${postId}"]`).count();
        assert.equal(remainingSavedArticle, 0, 'unsaved article is removed from vault');

        console.log(JSON.stringify({ ok: true, baseUrl, postId }));
    } finally {
        await browser.close();
    }
})().catch(error => {
    console.error(error);
    process.exit(1);
});
