@php
    /**
     * S167 — Local news view.
     * @var string $city
     * @var string $country
     * @var string $cc
     * @var bool   $detected     // true when location was IP-resolved this request
     * @var \Illuminate\Database\Eloquent\Collection $posts
     */
    Theme::layout('grimba-chrome');
    Theme::set('pageTitle', __('Local'));

    $countryNames = app()->getLocale() === 'en'
        ? [
            'FR' => 'France', 'BE' => 'Belgium', 'CH' => 'Switzerland', 'CA' => 'Canada',
            'US' => 'United States', 'GB' => 'United Kingdom', 'UK' => 'United Kingdom',
            'DE' => 'Germany', 'ES' => 'Spain', 'IT' => 'Italy', 'PT' => 'Portugal',
            'MA' => 'Morocco', 'DZ' => 'Algeria', 'TN' => 'Tunisia', 'SN' => 'Senegal',
            'CI' => 'Ivory Coast', 'CM' => 'Cameroon', 'NG' => 'Nigeria',
            'ZA' => 'South Africa', 'KE' => 'Kenya', 'EG' => 'Egypt',
            'JP' => 'Japan', 'CN' => 'China', 'IN' => 'India', 'BR' => 'Brazil',
            'MX' => 'Mexico', 'AU' => 'Australia', 'NZ' => 'New Zealand',
        ]
        : [
            'FR' => 'France', 'BE' => 'Belgique', 'CH' => 'Suisse', 'CA' => 'Canada',
            'US' => 'États-Unis', 'GB' => 'Royaume-Uni', 'UK' => 'Royaume-Uni',
            'DE' => 'Allemagne', 'ES' => 'Espagne', 'IT' => 'Italie', 'PT' => 'Portugal',
            'MA' => 'Maroc', 'DZ' => 'Algérie', 'TN' => 'Tunisie', 'SN' => 'Sénégal',
            'CI' => "Côte d'Ivoire", 'CM' => 'Cameroun', 'NG' => 'Nigeria',
            'ZA' => 'Afrique du Sud', 'KE' => 'Kenya', 'EG' => 'Égypte',
            'JP' => 'Japon', 'CN' => 'Chine', 'IN' => 'Inde', 'BR' => 'Brésil',
            'MX' => 'Mexique', 'AU' => 'Australie', 'NZ' => 'Nouvelle-Zélande',
        ];
    $countryCode = mb_strtoupper($cc);
    $displayCountry = $countryCode !== '' && isset($countryNames[$countryCode])
        ? $countryNames[$countryCode]
        : $country;

    $hasLocation = $city !== '' || $displayCountry !== '';
@endphp

<section class="grimba-local py-4 py-md-5">
    <div class="container">

        <header class="glass-panel p-4 p-md-5 mb-4">
            <span class="grimba-methodology__kicker">{{ __('Local') }}</span>

            @if($hasLocation)
                <h1 class="grimba-methodology__title mt-2 mb-2" style="font-size: clamp(28px, 3.6vw, 42px); letter-spacing:-0.4px;">
                    @if($city)
                        {{ $city }}@if($displayCountry), <span class="opacity-65">{{ $displayCountry }}</span>@endif
                    @else
                        {{ $displayCountry }}
                    @endif
                </h1>
                <p class="opacity-85 mb-3" style="font-size:16px; line-height:1.5;">
                    {{ $posts->count() }} {{ $posts->count() === 1 ? __('histoire récente') : __('histoires récentes') }}
                    @if($city)
                        {{ __('couvrant') }} {{ $city }}
                    @elseif($displayCountry)
                        {{ __('provenant de') }} {{ $displayCountry }}
                    @endif
                    — {{ __('sources croisées') }}.
                </p>
                @if($detected)
                    <p class="small opacity-65 mb-3">
                        🌍 {{ __('Localisation détectée automatiquement à partir de votre IP.') }}
                        {{ __('Pas la bonne ? Changez-la ci-dessous.') }}
                    </p>
                @endif
            @else
                <h1 class="grimba-methodology__title mt-2 mb-2" style="font-size: clamp(26px, 3.2vw, 36px); letter-spacing:-0.3px;">
                    {{ __('Choisissez votre ville') }}
                </h1>
                <p class="opacity-85 mb-3" style="font-size:16px; line-height:1.5; max-width: 60ch;">
                    {{ __("Indiquez où vous êtes pour obtenir l'actualité locale et les sources françaises ou internationales pertinentes pour vous.") }}
                </p>
            @endif

            <form method="POST" action="{{ route('public.local.set') }}" class="grimba-local__form" id="grimba-local-form"
                  style="display:flex; gap:8px; flex-wrap:wrap; align-items:flex-end; max-width: 640px;">
                @csrf
                <div class="grimba-local__field">
                    <label for="grimba-local-city" class="small fw-semibold d-block mb-1" style="opacity:0.85;">
                        {{ __('Ville') }}
                    </label>
                    <input type="text" id="grimba-local-city" name="city" value="{{ $city }}" placeholder="Paris"
                           autocomplete="address-level2"
                           class="grimba-form-pill">
                </div>
                <div class="grimba-local__field grimba-local__field--country">
                    <label for="grimba-local-cc" class="small fw-semibold d-block mb-1" style="opacity:0.85;">
                        {{ __('Pays (ISO)') }}
                    </label>
                    <input type="text" id="grimba-local-cc" name="cc" value="{{ $cc }}" maxlength="2" placeholder="FR"
                           autocomplete="country"
                           class="grimba-form-pill grimba-local__input--country">
                </div>
                <input type="hidden" name="country" id="grimba-local-country" value="{{ $displayCountry }}">
                <button type="submit" class="btn-grimba btn-grimba--solid"
                        style="padding:10px 22px; border-radius:9999px; background:var(--gn-ink,#1a1713); color:var(--gn-paper,#f6f1e8); font-family:'Public Sans',system-ui,sans-serif; font-weight:700; letter-spacing:0.4px; font-size:13px; border:none; cursor:pointer;">
                    {{ __('Mettre à jour') }}
                </button>
            </form>
        </header>

        @if($hasLocation && $posts->isEmpty())
            <div class="glass-panel p-4 text-center">
                <p class="mb-1">
                    {{ __('Aucune histoire récente pour') }}
                    <strong>{{ $city ?: $displayCountry }}</strong>
                    {{ __('dans notre archive.') }}
                </p>
                <p class="small opacity-75 mb-0">
                    {{ __('Essayez une ville plus grande, ou ajustez le pays. La couverture locale varie avec les flux RSS et NewsAPI suivis.') }}
                </p>
            </div>
        @elseif($posts->isNotEmpty())
            <div class="row g-4">
                @foreach($posts as $post)
                    <div class="col-lg-4 col-md-6 col-12">
                        @include(Theme::getThemeNamespace('partials.blog.post.partials.items.grid'), ['post' => $post])
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>

<script>
    // Auto-fill the hidden country name from the country-code input —
    // for the small set we display, mapping on the client is fine.
    (function () {
        const ccInput = document.getElementById('grimba-local-cc');
        const cnInput = document.getElementById('grimba-local-country');
        if (! ccInput || ! cnInput) return;
        const map = @json($countryNames);
        ccInput.addEventListener('change', () => {
            const cc = ccInput.value.trim().toUpperCase();
            ccInput.value = cc;
            if (map[cc]) cnInput.value = map[cc];
        });
    })();
</script>
