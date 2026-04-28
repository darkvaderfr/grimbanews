@php
    /**
     * S161 — real publisher logo.
     *
     * Renders an <img> through /img-proxy, backed by Clearbit's free
     * logo API, with two cascading fallbacks via JS onerror:
     *   1. Proxied Clearbit → preferred (clean PNG with alpha)
     *   2. Proxied Google s2 → favicon at requested size
     *   3. Initials  → 2-letter monogram in a colored circle (the
     *                   previous S148 design, kept as last resort)
     *
     * Caller passes either a $source DB row (with name + website
     * fields) or just a $name string. When website is absent we
     * skip the network calls and render initials directly.
     *
     * Required props:
     *   $name    — string display name
     * Optional props:
     *   $source_id — news_sources.id; enables provider status caching
     *   $website — host or full URL; without this we go straight
     *              to initials
     *   $logo_url / $logo_status / $logo_checked_at — cached admin
     *              logo diagnostics from news_sources
     *   $size    — px (default 36) — sets width + height
     *   $color   — hex; ring / initials accent (default ink)
     */

    $size    = $size ?? 36;
    $name    = (string) ($name ?? '');
    $website = (string) ($website ?? '');
    $sourceId = (int) ($source_id ?? 0);
    $logoUrl = trim((string) ($logo_url ?? ''));
    $logoStatus = (string) ($logo_status ?? 'unknown');
    $logoCheckedAt = (string) ($logo_checked_at ?? '');
    $color   = $color ?? '#1a1713';

    // Extract host from a website-like string. Accepts "lemonde.fr",
    // "https://www.lemonde.fr/", "www.lemonde.fr/news?x=1".
    $host = '';
    if ($website !== '') {
        $w = $website;
        if (! str_starts_with($w, 'http://') && ! str_starts_with($w, 'https://')) {
            $w = 'https://' . $w;
        }
        $parts = parse_url($w);
        $host  = $parts['host'] ?? '';
        if (str_starts_with($host, 'www.')) $host = substr($host, 4);
    }

    // Initials fallback (always computed — used if all else fails).
    $words = preg_split('/\s+/u', preg_replace('/[\(\)\.\,]/u', '', $name)) ?: [];
    $initials = count($words) === 1
        ? mb_strtoupper(mb_substr($words[0], 0, 2))
        : mb_strtoupper(mb_substr($words[0], 0, 1) . mb_substr($words[1] ?? '', 0, 1));

    $recentMiss = false;
    if ($logoStatus === 'missing' && $logoCheckedAt !== '') {
        try {
            $recentMiss = \Illuminate\Support\Carbon::parse($logoCheckedAt)->gt(now()->subDays(30));
        } catch (\Throwable) {
            $recentMiss = false;
        }
    }

    $proxy = function (string $remote, string $provider) use ($sourceId): string {
        $qs = ['u' => $remote, 'provider' => $provider];
        if ($sourceId > 0) $qs['sid'] = $sourceId;
        return url('/img-proxy?' . http_build_query($qs));
    };

    $clearbitRemote = $host ? "https://logo.clearbit.com/{$host}?size={$size}" : '';
    $faviconRemote = $host ? "https://www.google.com/s2/favicons?domain={$host}&sz=64" : '';

    $manualLogo = $logoUrl !== '' && $logoStatus === 'manual' ? $logoUrl : null;
    $cachedLogo = $logoUrl !== '' && in_array($logoStatus, ['clearbit', 'favicon'], true)
        ? $proxy($logoUrl, $logoStatus === 'clearbit' ? 'clearbit' : 'favicon')
        : null;
    $clearbit = (! $manualLogo && ! $cachedLogo && ! $recentMiss && $clearbitRemote !== '')
        ? $proxy($clearbitRemote, 'clearbit')
        : null;
    $googleS2 = (! $manualLogo && $faviconRemote !== '')
        ? $proxy($faviconRemote, 'favicon')
        : null;
    $primaryLogo = $manualLogo ?: ($cachedLogo ?: $clearbit);

    $uid = 'sl-' . bin2hex(random_bytes(4));
@endphp

<span class="grimba-source-logo" id="{{ $uid }}"
      style="
          display:inline-flex; align-items:center; justify-content:center;
          width:{{ $size }}px; height:{{ $size }}px;
          border-radius:50%;
          flex-shrink:0;
          background:{{ $color }}1a;
          color:{{ $color }};
          border:1.5px solid {{ $color }}33;
          overflow:hidden;
          position:relative;
      "
      title="{{ $name }}">
    @if($primaryLogo)
        <img src="{{ $primaryLogo }}"
             alt="{{ $name }}"
             loading="lazy"
             decoding="async"
             width="{{ $size }}"
             height="{{ $size }}"
             style="width:100%; height:100%; object-fit:contain; background:#fff;"
             data-grimba-logo-fallback="{{ $googleS2 }}"
             onerror="(function(el){
                 const fb = el.dataset.grimbaLogoFallback;
                 if (fb && el.src !== fb) { el.src = fb; el.removeAttribute('data-grimba-logo-fallback'); return; }
                 el.style.display='none';
                 const span = document.getElementById('{{ $uid }}');
                 if (span) {
                     const ini = document.createElement('span');
                     ini.style.cssText='font-family:Public Sans,system-ui,sans-serif; font-weight:700; font-size:{{ max(10, intval($size * 0.36)) }}px; letter-spacing:0.5px;';
                     ini.textContent={{ json_encode($initials) }};
                     span.appendChild(ini);
                 }
             })(this)">
    @else
        <span style="font-family:'Public Sans',system-ui,sans-serif; font-weight:700; font-size:{{ max(10, intval($size * 0.36)) }}px; letter-spacing:0.5px;">
            {{ $initials }}
        </span>
    @endif
</span>
