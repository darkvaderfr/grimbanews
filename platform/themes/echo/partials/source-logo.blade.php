@php
    /**
     * S161 — real publisher logo.
     *
     * Renders an <img> from Clearbit's free logo API
     * (https://logo.clearbit.com/<host>), with two cascading
     * fallbacks via JS onerror:
     *   1. Clearbit  → preferred (returns clean PNG with alpha)
     *   2. Google s2 → favicon at requested size (always works for
     *                   any reachable domain)
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
     *   $website — host or full URL; without this we go straight
     *              to initials
     *   $size    — px (default 36) — sets width + height
     *   $color   — hex; ring / initials accent (default ink)
     */

    $size    = $size ?? 36;
    $name    = (string) ($name ?? '');
    $website = (string) ($website ?? '');
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

    $clearbit = $host ? "https://logo.clearbit.com/{$host}?size={$size}" : null;
    $googleS2 = $host ? "https://www.google.com/s2/favicons?domain={$host}&sz=64" : null;

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
    @if($clearbit)
        <img src="{{ $clearbit }}"
             alt="{{ $name }}"
             loading="lazy"
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
