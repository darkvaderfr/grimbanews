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

async function firstVisible(locator, label) {
    const count = await locator.count();

    for (let index = 0; index < count; index += 1) {
        const item = locator.nth(index);
        if (await item.isVisible()) {
            return item;
        }
    }

    throw new Error(`No visible ${label} found.`);
}

async function inspectHome(page, width) {
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
        const translationNote = document.querySelector('.grimba-translation-note');
        const translationNoteShort = translationNote?.querySelector('.grimba-translation-note__copy--short') || null;
        const translationNoteFull = translationNote?.querySelector('.grimba-translation-note__copy--full') || null;
        const rootStyle = getComputedStyle(document.documentElement);

        return {
            theme: document.documentElement.getAttribute('data-bs-theme'),
            width: window.innerWidth,
            scrollWidth: document.documentElement.scrollWidth,
            bodyScrollWidth: document.body.scrollWidth,
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
            translationNote: translationNote ? {
                clientWidth: translationNote.clientWidth,
                scrollWidth: translationNote.scrollWidth,
                shortText: translationNoteShort?.textContent?.trim() || '',
                shortDisplay: translationNoteShort ? getComputedStyle(translationNoteShort).display : null,
                fullDisplay: translationNoteFull ? getComputedStyle(translationNoteFull).display : null,
            } : null,
        };
    });
}

async function inspectPageWidth(page, path, width) {
    await page.setViewportSize({ width, height: 844 });
    await page.goto(path, { waitUntil: 'networkidle' });

    return page.evaluate(() => ({
        path: window.location.pathname + window.location.search,
        width: window.innerWidth,
        scrollWidth: document.documentElement.scrollWidth,
        bodyScrollWidth: document.body.scrollWidth,
    }));
}

async function inspectFormControl(page, path, selector, label) {
    await page.setViewportSize({ width: 390, height: 844 });
    await page.goto(path, { waitUntil: 'networkidle' });

    const control = await firstVisible(page.locator(selector), label);
    const data = await control.evaluate(element => {
        const style = getComputedStyle(element);
        const rootStyle = getComputedStyle(document.documentElement);
        const bounds = element.getBoundingClientRect();

        return {
            color: style.color,
            backgroundColor: style.backgroundColor,
            borderColor: style.borderColor,
            paper: rootStyle.getPropertyValue('--gn-paper').trim(),
            theme: document.documentElement.getAttribute('data-bs-theme'),
            rect: {
                width: bounds.width,
                height: bounds.height,
            },
        };
    });
    const ratio = contrast(
        parseRgb(data.color),
        blend(parseRgb(data.backgroundColor), parseHex(data.paper))
    );

    assert.equal(data.theme, 'dark', `${label} page keeps dark theme`);
    assert.ok(data.rect.height >= 40, `${label} keeps a comfortable mobile input height`);
    assert.ok(ratio >= 7, `${label} contrast is AAA-sized in dark mode`);

    return { path, label, contrast: Number(ratio.toFixed(2)), color: data.color, backgroundColor: data.backgroundColor };
}

async function inspectSubpagePolish(page) {
    await page.setViewportSize({ width: 390, height: 844 });
    await page.goto('/search?q=afrique', { waitUntil: 'networkidle' });

    const search = await page.evaluate(() => {
        const title = document.querySelector('.grimba-search-page .grimba-methodology__title');
        const query = document.querySelector('.grimba-search-page__query');
        const titleStyle = title ? getComputedStyle(title) : null;
        const queryStyle = query ? getComputedStyle(query) : null;

        return {
            titleFontSize: titleStyle ? Number.parseFloat(titleStyle.fontSize) : 0,
            queryFontSize: queryStyle ? Number.parseFloat(queryStyle.fontSize) : 0,
            queryDisplay: queryStyle?.display || null,
            queryText: query?.textContent?.trim() || '',
        };
    });

    await page.goto('/local', { waitUntil: 'networkidle' });
    const local = await page.evaluate(() => {
        const lede = document.querySelector('.grimba-local__lede');
        const input = document.querySelector('#grimba-local-city');
        const title = document.querySelector('.grimba-local__title');
        const rootStyle = getComputedStyle(document.documentElement);
        const ledeStyle = lede ? getComputedStyle(lede) : null;
        const inputStyle = input ? getComputedStyle(input) : null;
        const titleStyle = title ? getComputedStyle(title) : null;

        return {
            ledeColor: ledeStyle?.color || '',
            ledeOpacity: ledeStyle?.opacity || '',
            paper: rootStyle.getPropertyValue('--gn-paper').trim(),
            inputBorderRadius: inputStyle ? Number.parseFloat(inputStyle.borderRadius) : 0,
            titleFontSize: titleStyle ? Number.parseFloat(titleStyle.fontSize) : 0,
        };
    });

    const localLedeContrast = contrast(blend(parseRgb(local.ledeColor), parseHex(local.paper)), parseHex(local.paper));

    return { search, local: { ...local, ledeContrast: Number(localLedeContrast.toFixed(2)) } };
}

async function inspectDesktopHeaderSearch(page) {
    await page.setViewportSize({ width: 1440, height: 900 });
    await page.goto('/', { waitUntil: 'networkidle' });

    return page.evaluate(() => {
        const search = document.querySelector('.grimba-search');
        const input = search?.querySelector('input[type="search"]') || null;
        const style = input ? getComputedStyle(input) : null;
        const bounds = search?.getBoundingClientRect();

        return {
            display: search ? getComputedStyle(search).display : null,
            placeholder: input?.getAttribute('placeholder') || '',
            inputFontSize: style ? Number.parseFloat(style.fontSize) : 0,
            width: bounds ? Math.round(bounds.width) : 0,
        };
    });
}

(async () => {
    const { chromium } = loadPlaywright();
    const baseUrl = (process.env.GRIMBANEWS_BASE_URL || 'http://127.0.0.1:8003').replace(/\/$/, '');
    const launchOptions = {
        headless: process.env.PLAYWRIGHT_HEADLESS !== '0',
    };

    if (process.env.PLAYWRIGHT_CHROMIUM_EXECUTABLE) {
        launchOptions.executablePath = process.env.PLAYWRIGHT_CHROMIUM_EXECUTABLE;
    }

    const browser = await chromium.launch(launchOptions);
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
            const snapshot = await inspectHome(page, width);
            snapshots.push(snapshot);

            assert.equal(snapshot.theme, 'dark', `dark theme is active at ${width}px`);
            assert.ok(snapshot.scrollWidth <= width + 1, `home document width stays contained at ${width}px`);
            assert.ok(snapshot.bodyScrollWidth <= width + 1, `home body width stays contained at ${width}px`);
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
            assert.ok(snapshot.translationNote, `translation note exists at ${width}px`);
            assert.ok(snapshot.translationNote.scrollWidth <= snapshot.translationNote.clientWidth + 1, `translation note stays contained at ${width}px`);
            assert.notEqual(snapshot.translationNote.shortDisplay, 'none', `mobile translation note uses compact copy at ${width}px`);
            assert.equal(snapshot.translationNote.fullDisplay, 'none', `mobile translation note hides long copy at ${width}px`);
            assert.match(snapshot.translationNote.shortText, /available|disponible/i, `translation note compact copy remains meaningful at ${width}px`);

            const textColor = parseRgb(snapshot.selectionChip.color);
            const chipBackground = blend(parseRgb(snapshot.selectionChip.backgroundColor), parseHex(snapshot.selectionChip.paper));
            assert.ok(
                contrast(textColor, chipBackground) >= 7,
                `dark topic selection chip contrast is AAA-sized at ${width}px`
            );
        }

        const formControls = [
            await inspectFormControl(page, '/login', '#grimba-login-email', 'login email input'),
            await inspectFormControl(page, '/local', '#grimba-local-city', 'local city input'),
        ];

        const subpagePolish = await inspectSubpagePolish(page);
        assert.equal(subpagePolish.search.queryDisplay, 'block', 'mobile search query wraps onto its own line');
        assert.match(subpagePolish.search.queryText, /afrique/i, 'mobile search query remains visible');
        assert.ok(subpagePolish.search.queryFontSize < subpagePolish.search.titleFontSize, 'mobile search query is subordinate to result count');
        assert.ok(subpagePolish.local.ledeContrast >= 7, 'mobile local helper copy keeps AAA-sized dark contrast');
        assert.equal(subpagePolish.local.ledeOpacity, '1', 'mobile local helper copy avoids opacity stacking');
        assert.ok(subpagePolish.local.inputBorderRadius >= 18, 'mobile local inputs keep softened corners');
        assert.ok(subpagePolish.local.titleFontSize <= 32, 'mobile local title uses contained type scale');

        const desktopHeaderSearch = await inspectDesktopHeaderSearch(page);
        assert.notEqual(desktopHeaderSearch.display, 'none', 'desktop header search remains visible');
        assert.ok(desktopHeaderSearch.width >= 320, 'desktop header search keeps its expected width');
        assert.ok(desktopHeaderSearch.placeholder.length <= 24, 'desktop header search placeholder is concise enough to fit');
        assert.match(desktopHeaderSearch.placeholder, /source/i, 'desktop header search placeholder still names sources');

        for (const width of [320, 390]) {
            const searchWidth = await inspectPageWidth(page, '/search?q=afrique', width);
            assert.ok(searchWidth.scrollWidth <= width + 1, `search document width stays contained at ${width}px`);
            assert.ok(searchWidth.bodyScrollWidth <= width + 1, `search body width stays contained at ${width}px`);
        }

        await page.goto('/', { waitUntil: 'networkidle' });
        const storyLink = await firstVisible(page.locator([
            '.grimba-briefing__headline',
            '.grimba-hero__media',
            '.grimba-topnews__headline',
            '.grimba-latest__headline',
            '.grimba-most-read__headline',
            'a[href*="/article/"]',
        ].join(', ')).filter({ hasText: /.+/ }), 'story link');
        await Promise.all([
            page.waitForLoadState('domcontentloaded'),
            storyLink.click(),
        ]);

        const storyTitle = await firstVisible(page.locator('.grimba-story-page__title'), 'article story title');
        const storyTitleMetrics = await storyTitle.evaluate(element => {
            const style = getComputedStyle(element);
            const bounds = element.getBoundingClientRect();

            return {
                fontSize: Number.parseFloat(style.fontSize),
                lineHeight: Number.parseFloat(style.lineHeight),
                right: bounds.right,
                viewportWidth: window.innerWidth,
            };
        });
        assert.ok(storyTitleMetrics.fontSize <= 31.5, 'mobile article title uses contained type scale');
        assert.ok(storyTitleMetrics.lineHeight <= storyTitleMetrics.fontSize * 1.12, 'mobile article title keeps a tight readable line-height');
        assert.ok(storyTitleMetrics.right <= storyTitleMetrics.viewportWidth + 1, 'mobile article title stays inside the viewport');

        const saveButton = await firstVisible(page.locator('.grimba-save-btn'), 'save button');
        const saveButtonStyle = await saveButton.evaluate(button => {
            const style = getComputedStyle(button);
            const rootStyle = getComputedStyle(document.documentElement);

            return {
                color: style.color,
                backgroundColor: style.backgroundColor,
                borderColor: style.borderColor,
                paper: rootStyle.getPropertyValue('--gn-paper').trim(),
            };
        });
        const saveButtonContrast = contrast(
            parseRgb(saveButtonStyle.color),
            blend(parseRgb(saveButtonStyle.backgroundColor), parseHex(saveButtonStyle.paper))
        );
        assert.ok(saveButtonContrast >= 7, 'dark save button contrast is AAA-sized before interaction');

        await saveButton.click();
        await page.waitForTimeout(200);
        const pressedButtonStyle = await saveButton.evaluate(button => {
            const style = getComputedStyle(button);
            return {
                pressed: button.getAttribute('aria-pressed'),
                color: style.color,
                backgroundColor: style.backgroundColor,
            };
        });
        assert.equal(pressedButtonStyle.pressed, 'true', 'save button becomes pressed');
        assert.match(pressedButtonStyle.backgroundColor, /rgb\(255, 250, 240\)/, 'pressed save button uses explicit cream background');

        console.log(JSON.stringify({
            ok: true,
            baseUrl,
            snapshots: snapshots.map(({ width, selectionChip }) => ({ width, selectionChip })),
            formControls,
            subpagePolish,
            desktopHeaderSearch,
            saveButton: { contrast: Number(saveButtonContrast.toFixed(2)), pressedButtonStyle },
        }));
    } finally {
        await browser.close();
    }
})().catch(error => {
    console.error(error);
    process.exit(1);
});
