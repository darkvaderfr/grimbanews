/* GrimbaNews — mirror Botble admin theme mode onto <html>. */
(function () {
    function modeFromBody() {
        var bodyMode = document.body && document.body.getAttribute('data-bs-theme');

        return bodyMode === 'dark' ? 'dark' : 'light';
    }

    function applyMode() {
        try {
            var effective = modeFromBody();

            if (document.documentElement.getAttribute('data-bs-theme') !== effective) {
                document.documentElement.setAttribute('data-bs-theme', effective);
            }
            if (document.body && document.body.getAttribute('data-bs-theme') !== effective) {
                document.body.setAttribute('data-bs-theme', effective);
            }
            window.localStorage && window.localStorage.setItem('themeMode', effective);
        } catch (_) {}
    }

    applyMode();

    if (document.body && window.MutationObserver) {
        new MutationObserver(applyMode).observe(document.body, {
            attributes: true,
            attributeFilter: ['data-bs-theme'],
        });
    }
})();
