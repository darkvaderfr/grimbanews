<script>
    (function () {
        if (!('serviceWorker' in navigator)) {
            return;
        }

        window.addEventListener('load', function () {
            navigator.serviceWorker.register(@json(asset('grimba-sw.js')), { scope: '/' }).catch(function () {});
        });
    })();
</script>
