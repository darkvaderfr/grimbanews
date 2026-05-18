{{-- S-MODE-09 (Vader 2026-05-18) — FOUC guard.

     Before this partial, the dark-mode flow was:
       1. Page renders with NO `data-theme` attribute (default light)
       2. webpack `script.js` (deferred) runs after DOMContentLoaded
       3. It reads `localStorage.echo-theme` and flips the attribute

     Net: every dark-mode reader saw a white flash on every nav.

     This inline script runs as part of the document `<head>`, BEFORE
     the body parses, so the `<html>` element carries the correct
     `data-theme` and `data-bs-theme` from the very first paint.

     Precedence (highest first):
       1. `localStorage.echo-theme` — user's explicit choice via the
          `#rts-data-toggle` button
       2. `grimba_theme` cookie — server-side hint (also drives the
          PWA `<meta name="theme-color">` value in `pwa-head`)
       3. Default `light`. We deliberately DO NOT consult
          `prefers-color-scheme` here — Vader's policy (PwaShellTest
          covers it) is SSR-deterministic: stale/invalid hints fall
          back to light so cross-device review sessions stay
          predictable. A reader who wants dark mode picks it
          explicitly via the toggle. --}}
<script>
    (function () {
        try {
            var mode = null;
            try { mode = localStorage.getItem('echo-theme'); } catch (e) {}
            if (mode !== 'light' && mode !== 'dark') {
                var cookie = (document.cookie.match(/(?:^|;\s*)grimba_theme=([^;]+)/) || [])[1];
                if (cookie === 'light' || cookie === 'dark') {
                    mode = cookie;
                }
            }
            if (mode !== 'light' && mode !== 'dark') {
                mode = 'light';
            }
            var html = document.documentElement;
            html.setAttribute('data-theme', mode);
            html.setAttribute('data-bs-theme', mode);
            html.setAttribute('data-grimba-theme-pref', mode);
            // Tag body too once parsing reaches it — CSS uses both
            // `[data-bs-theme="dark"]` and `body[data-theme="dark"]`
            // selectors, so we mirror to body as soon as it exists.
            if (document.body) {
                document.body.setAttribute('data-theme', mode);
            } else {
                document.addEventListener('DOMContentLoaded', function () {
                    document.body.setAttribute('data-theme', mode);
                });
            }
            // Sync the user's localStorage choice to the cookie so
            // the SSR layer (pwa-head's `theme-color` meta, the home
            // layout's pre-head script) sees the right value on the
            // NEXT navigation. Cookie expires in 1 year.
            try {
                var cookieCurrent = (document.cookie.match(/(?:^|;\s*)grimba_theme=([^;]+)/) || [])[1];
                if (cookieCurrent !== mode) {
                    var oneYear = 60 * 60 * 24 * 365;
                    document.cookie = 'grimba_theme=' + mode + '; path=/; max-age=' + oneYear + '; samesite=lax';
                }
            } catch (e) {}
        } catch (e) {
            /* never break the page over a theme hint */
        }
    })();
</script>
