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

async function activeInside(page, selector) {
    return page.evaluate((targetSelector) => {
        const target = document.querySelector(targetSelector);
        return Boolean(target && target.contains(document.activeElement));
    }, selector);
}

async function modalIsOpen(page, selector) {
    return page.evaluate((targetSelector) => {
        const target = document.querySelector(targetSelector);
        return Boolean(target && target.classList.contains('is-open') && target.getAttribute('aria-hidden') === 'false');
    }, selector);
}

async function visibleNewsletterOpener(page) {
    const openers = page.locator('[data-grimba-newsletter-open]');
    const count = await openers.count();

    for (let index = 0; index < count; index += 1) {
        const opener = openers.nth(index);
        if (await opener.isVisible()) {
            return opener;
        }
    }

    throw new Error('No visible newsletter opener found.');
}

(async () => {
    const { chromium } = loadPlaywright();
    const baseUrl = (process.env.GRIMBANEWS_BASE_URL || 'http://127.0.0.1:8003').replace(/\/$/, '');
    const browser = await chromium.launch({ headless: process.env.PLAYWRIGHT_HEADLESS !== '0' });
    const context = await browser.newContext({
        viewport: { width: 390, height: 844 },
        colorScheme: 'dark',
        baseURL: baseUrl,
    });
    const page = await context.newPage();

    try {
        await context.addCookies([
            { name: 'grimba_theme', value: 'dark', url: baseUrl },
            { name: 'grimba_cookie_consent', value: 'necessary', url: baseUrl },
        ]);

        await page.goto('/?onboarding=1', { waitUntil: 'domcontentloaded' });
        await page.waitForSelector('#grimba-onboard-modal.is-open', { timeout: 10000 });
        assert.equal(await activeInside(page, '#grimba-onboard-modal'), true, 'onboarding opens with focus inside');

        await page.focus('#grimba-onboard-modal [data-grimba-onboard-close]');
        await page.keyboard.down('Shift');
        await page.keyboard.press('Tab');
        await page.keyboard.up('Shift');
        assert.equal(await activeInside(page, '#grimba-onboard-modal'), true, 'shift-tab remains inside onboarding');

        await page.keyboard.press('Escape');
        await page.waitForFunction(() => {
            const modal = document.querySelector('#grimba-onboard-modal');
            return !modal || !modal.classList.contains('is-open');
        }, null, { timeout: 10000 });

        await page.goto('/', { waitUntil: 'domcontentloaded' });
        const newsletterOpener = await visibleNewsletterOpener(page);
        await newsletterOpener.click();
        await page.waitForSelector('#grimba-newsletter-modal.is-open', { timeout: 10000 });
        assert.equal(await activeInside(page, '#grimba-newsletter-modal'), true, 'newsletter opens with focus inside');

        await page.focus('#grimba-newsletter-modal [data-grimba-newsletter-close]');
        await page.keyboard.press('Tab');
        assert.equal(await activeInside(page, '#grimba-newsletter-modal'), true, 'tab wraps inside newsletter');

        await page.keyboard.press('Escape');
        await page.waitForFunction(() => !document.querySelector('#grimba-newsletter-modal')?.classList.contains('is-open'), null, { timeout: 10000 });
        assert.equal(await modalIsOpen(page, '#grimba-newsletter-modal'), false, 'newsletter closes on escape');

        await page.keyboard.press('Control+K');
        await page.waitForSelector('#grimba-command-palette.is-open', { timeout: 10000 });
        assert.equal(await page.locator('#grimba-command-input').evaluate((node) => node === document.activeElement), true, 'command palette focuses search input');

        await page.evaluate(() => {
            const palette = document.querySelector('#grimba-command-palette');
            const nodes = window.GrimbaFocus.focusables(palette);
            nodes[nodes.length - 1].focus();
        });
        await page.keyboard.press('Tab');
        assert.equal(await page.locator('#grimba-command-input').evaluate((node) => node === document.activeElement), true, 'command palette tab wraps to search input');

        await page.keyboard.press('Escape');
        await page.waitForFunction(() => !document.querySelector('#grimba-command-palette')?.classList.contains('is-open'), null, { timeout: 10000 });

        console.log(JSON.stringify({ ok: true, baseUrl }));
    } finally {
        await browser.close();
    }
})().catch(error => {
    console.error(error);
    process.exit(1);
});
