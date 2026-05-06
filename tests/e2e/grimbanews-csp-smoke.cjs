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

function isCspMessage(message) {
    return /content security policy|violates the following Content Security Policy directive|Refused to/i.test(message);
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

    await context.addCookies([
        { name: 'grimba_lang', value: 'en', url: baseUrl },
        { name: 'grimba_onboarded', value: '1', url: baseUrl },
        { name: 'grimba_theme', value: 'dark', url: baseUrl },
        { name: 'grimba_local_city', value: 'Paris', url: baseUrl },
        { name: 'grimba_local_country', value: 'France', url: baseUrl },
        { name: 'grimba_local_cc', value: 'FR', url: baseUrl },
    ]);

    const violations = [];
    const routes = ['/', '/coffre', '/pour-vous', '/local', '/command-palette.json'];

    try {
        for (const route of routes) {
            const page = await context.newPage();
            page.on('console', (message) => {
                const text = message.text();
                if (isCspMessage(text)) {
                    violations.push({ route, type: message.type(), text });
                }
            });
            page.on('pageerror', (error) => {
                if (isCspMessage(error.message)) {
                    violations.push({ route, type: 'pageerror', text: error.message });
                }
            });

            const response = await page.goto(route, { waitUntil: 'domcontentloaded' });
            assert.ok(response, `No response for ${route}`);
            assert.ok(response.ok(), `${route} returned ${response.status()}`);

            const headers = response.headers();
            assert.ok(headers['content-security-policy'], `${route} is missing Content-Security-Policy`);
            assert.equal(headers['content-security-policy-report-only'], undefined, `${route} still uses report-only CSP`);
            assert.match(headers['content-security-policy'], /default-src 'self'/, `${route} CSP lacks default-src`);
            assert.match(headers['content-security-policy'], /frame-ancestors 'self'/, `${route} CSP lacks frame-ancestors`);

            await page.waitForLoadState('networkidle', { timeout: 5000 }).catch(() => {});
            await page.close();
        }

        assert.deepEqual(violations, []);
        console.log(JSON.stringify({ ok: true, baseUrl, routes }));
    } finally {
        await browser.close();
    }
})().catch(error => {
    console.error(error);
    process.exit(1);
});
