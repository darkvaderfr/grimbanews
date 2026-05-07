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
    const match = String(value).match(/rgba?\(([^)]+)\)/);
    if (! match) {
        throw new Error(`Cannot parse CSS color: ${value}`);
    }

    const [r, g, b, a = 1] = match[1].split(',').map(part => Number.parseFloat(part.trim()));
    return { r, g, b, a };
}

function parseHex(value) {
    const hex = String(value).trim().replace(/^#/, '');
    if (! /^[0-9a-f]{6}$/i.test(hex)) {
        throw new Error(`Cannot parse hex color: ${value}`);
    }

    return {
        r: Number.parseInt(hex.slice(0, 2), 16),
        g: Number.parseInt(hex.slice(2, 4), 16),
        b: Number.parseInt(hex.slice(4, 6), 16),
        a: 1,
    };
}

function blend(foreground, background) {
    const alpha = foreground.a ?? 1;

    return {
        r: (foreground.r * alpha) + (background.r * (1 - alpha)),
        g: (foreground.g * alpha) + (background.g * (1 - alpha)),
        b: (foreground.b * alpha) + (background.b * (1 - alpha)),
        a: 1,
    };
}

function luminance(color) {
    const channel = value => {
        const normalized = value / 255;
        return normalized <= 0.03928
            ? normalized / 12.92
            : ((normalized + 0.055) / 1.055) ** 2.4;
    };

    return (0.2126 * channel(color.r)) + (0.7152 * channel(color.g)) + (0.0722 * channel(color.b));
}

function contrast(foreground, background) {
    const light = Math.max(luminance(foreground), luminance(background));
    const dark = Math.min(luminance(foreground), luminance(background));

    return (light + 0.05) / (dark + 0.05);
}

async function inspectHome(page, width, theme) {
    await page.setViewportSize({ width, height: 844 });
    await page.goto('/', { waitUntil: 'networkidle' });

    return page.evaluate(() => {
        const rect = element => {
            const bounds = element.getBoundingClientRect();

            return {
                x: bounds.x,
                y: bounds.y,
                width: bounds.width,
                height: bounds.height,
                right: bounds.right,
                bottom: bounds.bottom,
            };
        };

        const nav = document.querySelector('.grimba-mobile-nav');
        const navItems = [...document.querySelectorAll('.grimba-mobile-nav__item')].map(item => {
            const label = item.querySelector('span:last-child');
            return {
                text: label?.textContent?.trim() || '',
                itemRect: rect(item),
                labelClientWidth: label?.clientWidth || 0,
                labelScrollWidth: label?.scrollWidth || 0,
            };
        });

        const wordmark = document.querySelector('.grimba-wordmark');
        const search = document.querySelector('.grimba-search');
        const selectionChip = document.querySelector('.grimba-similar__chip');
        const selectionChipStyle = selectionChip ? getComputedStyle(selectionChip) : null;
        const rootStyle = getComputedStyle(document.documentElement);

        return {
            theme: document.documentElement.getAttribute('data-bs-theme'),
            width: window.innerWidth,
            navRect: nav ? rect(nav) : null,
            navDisplay: nav ? getComputedStyle(nav).display : null,
            navItems,
            wordmarkRect: wordmark ? rect(wordmark) : null,
            searchRect: search ? rect(search) : null,
            selectionChip: selectionChip ? {
                text: selectionChip.textContent.trim().replace(/\s+/g, ' '),
                color: selectionChipStyle.color,
                backgroundColor: selectionChipStyle.backgroundColor,
                borderColor: selectionChipStyle.borderColor,
                paper: rootStyle.getPropertyValue('--gn-paper').trim(),
            } : null,
        };
    });
}

(async () => {
    const { chromium } = loadPlaywright();
    const baseUrl = (process.env.GRIMBANEWS_BASE_URL || 'http://127.0.0.1:8003').replace(/\/$/, '');
    const browser = await chromium.launch({ headless: process.env.PLAYWRIGHT_HEADLESS !== '0' });
    const context = await browser.newContext({
        baseURL: baseUrl,
        isMobile: true,
        deviceScaleFactor: 2,
    });

    await context.addCookies([
        { name: 'grimba_lang', value: 'en', url: baseUrl },
        { name: 'grimba_onboarded', value: '1', url: baseUrl },
        { name: 'grimba_cookie_consent', value: 'necessary', url: baseUrl },
        { name: 'grimba_theme', value: 'dark', url: baseUrl },
    ]);

    const page = await context.newPage();

    try {
        const snapshots = [];

        for (const width of [320, 390]) {
            const snapshot = await inspectHome(page, width, 'dark');
            snapshots.push(snapshot);

            assert.equal(snapshot.theme, 'dark', `dark theme is active at ${width}px`);
            assert.equal(snapshot.navDisplay, 'grid', `mobile nav is visible at ${width}px`);
            assert.ok(snapshot.navRect, `mobile nav rect exists at ${width}px`);
            assert.ok(snapshot.navRect.bottom <= 844, `mobile nav stays inside viewport at ${width}px`);
            assert.ok(snapshot.navRect.height >= 44, `mobile nav is tappable at ${width}px`);

            for (const item of snapshot.navItems) {
                assert.ok(item.itemRect.height >= 44, `${item.text} keeps a 44px tap target at ${width}px`);
                assert.ok(item.labelScrollWidth <= item.labelClientWidth + 1, `${item.text} label does not overflow at ${width}px`);
            }

            assert.ok(snapshot.wordmarkRect.right < snapshot.searchRect.x, `wordmark and search do not collide at ${width}px`);
            assert.ok(snapshot.selectionChip, `topic selection chip exists at ${width}px`);

            const textColor = parseRgb(snapshot.selectionChip.color);
            const chipBackground = blend(parseRgb(snapshot.selectionChip.backgroundColor), parseHex(snapshot.selectionChip.paper));
            assert.ok(
                contrast(textColor, chipBackground) >= 7,
                `dark topic selection chip contrast is AAA-sized at ${width}px`
            );
        }

        console.log(JSON.stringify({ ok: true, baseUrl, snapshots: snapshots.map(({ width, selectionChip }) => ({ width, selectionChip })) }));
    } finally {
        await browser.close();
    }
})().catch(error => {
    console.error(error);
    process.exit(1);
});
