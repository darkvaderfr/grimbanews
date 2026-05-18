@if(\App\Support\GrimbaAds::shouldLoadAdSenseScript())
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client={{ \App\Support\GrimbaAds::adsenseClientId() }}"
            crossorigin="anonymous"></script>
@endif
