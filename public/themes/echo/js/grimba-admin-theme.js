/* GrimbaNews — keep Botble admin theme mode in sync with our CSS hooks. */
(function () {
    var applying = false;
    var preferDomUntil = 0;

    function disablePublicWorkerOnAdmin() {
        if (!('serviceWorker' in navigator) || ! window.location.pathname.match(/^\/admin(?:\/|$)/)) {
            return;
        }

        navigator.serviceWorker.getRegistrations()
            .then(function (registrations) {
                registrations.forEach(function (registration) {
                    registration.unregister().catch(function () {});
                });
            })
            .catch(function () {});
    }

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

    function domMode() {
        return normalize(document.documentElement.getAttribute('data-bs-theme'))
            || (document.body ? normalize(document.body.getAttribute('data-bs-theme')) : null);
    }

    function persistMode(mode) {
        if (! window.localStorage) {
            return;
        }

        window.localStorage.setItem('tablerTheme', mode);
        window.localStorage.setItem('grimba_theme', mode);
        window.localStorage.setItem('echo-theme', mode);
        window.localStorage.setItem('themeMode', mode);
    }

    function currentMode(preferDom) {
        var dom = domMode();

        if (preferDom && dom) {
            return dom;
        }

        var stored = storedMode(false);

        if (stored) {
            return stored;
        }

        if (dom) {
            return dom;
        }

        return storedMode(true)
            || cookieMode()
            || 'light';
    }

    function applyMode(preferDom) {
        if (applying) {
            return;
        }

        try {
            applying = true;
            var effective = currentMode(preferDom || Date.now() < preferDomUntil);

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

            persistMode(effective);
        } catch (_) {}
        finally {
            applying = false;
        }
    }

    disablePublicWorkerOnAdmin();
    applyMode();

    window.addEventListener('storage', applyMode);
    document.addEventListener('click', function () {
        preferDomUntil = Date.now() + 700;
        window.setTimeout(function () { applyMode(true); }, 0);
        window.setTimeout(function () { applyMode(true); }, 80);
        window.setTimeout(function () { applyMode(true); }, 250);
    }, true);

    if (window.MutationObserver) {
        var observer = new MutationObserver(function () {
            applyMode(true);
        });

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
