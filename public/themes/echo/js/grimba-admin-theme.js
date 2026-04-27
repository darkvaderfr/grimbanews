/* GrimbaNews — keep Botble admin theme mode in sync with our CSS hooks. */
(function () {
    var applying = false;

    function normalize(value) {
        return value === 'dark' ? 'dark' : value === 'light' ? 'light' : null;
    }

    function cookieMode() {
        var match = document.cookie.match(/(?:^|;\s*)grimba_theme=(dark|light)/);

        return match ? match[1] : null;
    }

    function storedMode(includeLegacy) {
        try {
            return normalize(window.localStorage.getItem('tablerTheme'))
                || normalize(window.localStorage.getItem('grimba_theme'))
                || normalize(window.localStorage.getItem('echo-theme'))
                || (includeLegacy ? normalize(window.localStorage.getItem('themeMode')) : null);
        } catch (_) {
            return null;
        }
    }

    function currentMode() {
        var stored = storedMode(false);

        if (stored) {
            return stored;
        }

        if (document.body) {
            return normalize(document.body.getAttribute('data-bs-theme')) || 'light';
        }

        return normalize(document.documentElement.getAttribute('data-bs-theme'))
            || storedMode(true)
            || cookieMode()
            || 'light';
    }

    function applyMode() {
        if (applying) {
            return;
        }

        try {
            applying = true;
            var effective = currentMode();

            if (document.documentElement.getAttribute('data-bs-theme') !== effective) {
                document.documentElement.setAttribute('data-bs-theme', effective);
            }

            if (! document.body) {
                return;
            }

            if (effective === 'dark') {
                document.body.setAttribute('data-bs-theme', effective);
            } else if (document.body.hasAttribute('data-bs-theme')) {
                document.body.removeAttribute('data-bs-theme');
            }

            window.localStorage && window.localStorage.setItem('themeMode', effective);
        } catch (_) {}
        finally {
            applying = false;
        }
    }

    applyMode();

    window.addEventListener('storage', applyMode);
    document.addEventListener('click', function () {
        window.setTimeout(applyMode, 0);
        window.setTimeout(applyMode, 80);
    }, true);

    if (window.MutationObserver) {
        var observer = new MutationObserver(applyMode);

        observer.observe(document.documentElement, {
            attributes: true,
            attributeFilter: ['data-bs-theme'],
        });

        if (document.body) {
            observer.observe(document.body, {
                attributes: true,
                attributeFilter: ['data-bs-theme'],
            });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', applyMode);
    } else {
        window.setTimeout(applyMode, 0);
    }

    window.setInterval(applyMode, 1000);
})();
