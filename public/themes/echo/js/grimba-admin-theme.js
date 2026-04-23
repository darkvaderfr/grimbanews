/* GrimbaNews — mirror grimba_theme cookie onto admin <html>. */
(function () {
    try {
        var pref = (document.cookie.match(/(?:^|; )grimba_theme=([^;]+)/) || [])[1] || 'auto';
        var prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
        var effective = pref === 'auto' ? (prefersDark ? 'dark' : 'light') : pref;
        document.documentElement.setAttribute('data-bs-theme', effective);
    } catch (_) {}
})();
