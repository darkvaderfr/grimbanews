<?php

namespace App\Support;

class GrimbaSourceCountryBackfill
{
    private const DOMAIN_COUNTRIES = [
        'abc.net.au' => 'AU',
        'abcnews.com' => 'US',
        'abcnews.go.com' => 'US',
        '9to5google.com' => 'US',
        '9to5mac.com' => 'US',
        'a-z-animals.com' => 'US',
        'al.com' => 'US',
        'aljazeera.com' => 'QA',
        'apnews.com' => 'US',
        'atlantafalcons.com' => 'US',
        'audacy.com' => 'US',
        'axios.com' => 'US',
        'baltimoreravens.com' => 'US',
        'batterypower.com' => 'US',
        'bbc.com' => 'GB',
        'bbc.co.uk' => 'GB',
        'begeek.fr' => 'FR',
        'bengals.com' => 'US',
        'bigblueview.com' => 'US',
        'bleedinggreennation.com' => 'US',
        'bleepingcomputer.com' => 'US',
        'bloomberg.com' => 'US',
        'boredpanda.com' => 'LT',
        'boston.com' => 'US',
        'breitbart.com' => 'US',
        'broadwayworld.com' => 'US',
        'businessinsider.com' => 'US',
        'camer.be' => 'CM',
        'caradisiac.com' => 'FR',
        'cbc.ca' => 'CA',
        'cbsnews.com' => 'US',
        'chiefs.com' => 'US',
        'cnbc.com' => 'US',
        'cnn.com' => 'US',
        'coindesk.com' => 'US',
        'comicbook.com' => 'US',
        'cowboystatedaily.com' => 'US',
        'cyclingnews.com' => 'GB',
        'dailymail.co.uk' => 'GB',
        'deadline.com' => 'US',
        'democracydocket.com' => 'US',
        'digitalcameraworld.com' => 'GB',
        'digitalfoundry.net' => 'GB',
        'dualshockers.com' => 'US',
        'earth.com' => 'US',
        'eatingwell.com' => 'US',
        'eatthis.com' => 'US',
        'ekathimerini.com' => 'GR',
        'electrek.co' => 'US',
        'engadget.com' => 'US',
        'entertainmentweekly.com' => 'US',
        'espn.com' => 'US',
        'eurogamer.net' => 'GB',
        'ew.com' => 'US',
        'fox35orlando.com' => 'US',
        'foxnews.com' => 'US',
        'ft.com' => 'GB',
        'frandroid.com' => 'FR',
        'futurism.com' => 'US',
        'gamesindustry.biz' => 'GB',
        'gamingbible.com' => 'GB',
        'ghanaweb.com' => 'GH',
        'gizmodo.com' => 'US',
        'golfchannel.com' => 'US',
        'globalnews.ca' => 'CA',
        'goodhousekeeping.com' => 'US',
        'gq.com' => 'US',
        'grist.org' => 'US',
        'hackaday.com' => 'US',
        'harpersbazaar.com' => 'US',
        'healthline.com' => 'US',
        'hindustantimes.com' => 'IN',
        'histoirescrepues.fr' => 'FR',
        'hotnewhiphop.com' => 'US',
        'huffpost.com' => 'US',
        'ign.com' => 'US',
        'inc.com' => 'US',
        'indiandefencereview.com' => 'IN',
        'insider-gaming.com' => 'US',
        'instyle.com' => 'US',
        'investing.com' => 'IL',
        'iphoneaddict.fr' => 'FR',
        'iphon.fr' => 'FR',
        'jetsxfactor.com' => 'US',
        'kqed.org' => 'US',
        'lakingsinsider.com' => 'US',
        'leblogauto.com' => 'FR',
        'menshealth.com' => 'US',
        'marketwatch.com' => 'US',
        'mining.com' => 'CA',
        'motorsport.com' => 'US',
        'msnbc.com' => 'US',
        'mysanantonio.com' => 'US',
        'nbcsports.com' => 'US',
        'nationalgeographic.com' => 'US',
        'nationalreview.com' => 'US',
        'nbcnews.com' => 'US',
        'neurosciencenews.com' => 'US',
        'newsweek.com' => 'US',
        'nintendoeverything.com' => 'US',
        'npr.org' => 'US',
        'nytimes.com' => 'US',
        'oilprice.com' => 'US',
        'opinion-internationale.com' => 'FR',
        'openai.com' => 'US',
        'people.com' => 'US',
        'phototrend.fr' => 'FR',
        'politico.eu' => 'BE',
        'politico.com' => 'US',
        'presse-citron.net' => 'FR',
        'prideofdetroit.com' => 'US',
        'queerty.com' => 'US',
        'revue-farouest.fr' => 'FR',
        'riviantrackr.com' => 'US',
        'rte.ie' => 'IE',
        'rt.com' => 'RU',
        'sfstandard.com' => 'US',
        'slate.com' => 'US',
        'slate.fr' => 'FR',
        'space.com' => 'US',
        'suntimes.com' => 'US',
        'teamhcso.com' => 'US',
        'telerama.fr' => 'FR',
        'tennesseetitans.com' => 'US',
        'techcrunch.com' => 'US',
        'telegraph.co.uk' => 'GB',
        'theamericanconservative.com' => 'US',
        'thebrighterside.news' => 'US',
        'theregister.com' => 'GB',
        'theguardian.com' => 'GB',
        'thehill.com' => 'US',
        'theverge.com' => 'US',
        'thenewstack.io' => 'US',
        'tipranks.com' => 'IL',
        'time.com' => 'US',
        'timesofindia.indiatimes.com' => 'IN',
        'videocardz.com' => 'PL',
        'viewfromthewing.com' => 'US',
        'usatoday.com' => 'US',
        'vice.com' => 'US',
        'vox.com' => 'US',
        'washingtonpost.com' => 'US',
        'washingtontimes.com' => 'US',
        'wccftech.com' => 'PK',
        'wired.com' => 'US',
        'wsj.com' => 'US',
        'zdnet.fr' => 'FR',
    ];

    private const NAME_COUNTRIES = [
        '9to5mac' => 'US',
        'abc news' => 'US',
        'abc news au' => 'AU',
        'acme packing company' => 'US',
        'allphly com' => 'US',
        'al jazeera english' => 'QA',
        'alternet' => 'US',
        'anchorage daily news' => 'US',
        'android police' => 'US',
        'barron s' => 'US',
        'bear goggles on' => 'US',
        'bgr' => 'US',
        'blazer s edge' => 'US',
        'block club chicago' => 'US',
        'boston herald' => 'US',
        'bring me the news' => 'US',
        'bbc' => 'GB',
        'bbc news' => 'GB',
        'bbc sport' => 'GB',
        'cageside seats' => 'US',
        'cinemablend' => 'US',
        'democracydocket com' => 'US',
        'dexerto' => 'GB',
        'dw english' => 'DE',
        'est republicain' => 'FR',
        'fda gov' => 'US',
        'field gulls' => 'US',
        'forbes' => 'US',
        'gear patrol' => 'US',
        'geeky gadgets' => 'GB',
        'golf channel' => 'US',
        'heat com' => 'US',
        'hogs haven' => 'US',
        'houston chronicle' => 'US',
        'interesting engineering' => 'US',
        'jalopnik' => 'US',
        'jdn' => 'FR',
        'just jared' => 'US',
        'kabc tv' => 'US',
        'kotaku' => 'US',
        'le progres' => 'FR',
        'live science' => 'US',
        'macrumors' => 'US',
        'martha stewart' => 'US',
        'medical xpress' => 'GB',
        'medium' => 'US',
        'miami herald' => 'US',
        'mlb trade rumors' => 'US',
        'motley fool' => 'US',
        'my nintendo news' => 'GB',
        'neowin' => 'GB',
        'netflix life' => 'US',
        'new york post' => 'US',
        'newser' => 'US',
        'news medical net' => 'GB',
        'nfl news' => 'US',
        'nintendo life' => 'GB',
        'notus org' => 'US',
        'numerama' => 'FR',
        'cbc news' => 'CA',
        'cnbc' => 'US',
        'cnn' => 'US',
        'daily mail' => 'GB',
        'one mile at a time' => 'US',
        'oregonlive' => 'US',
        'page six' => 'US',
        'parade' => 'US',
        'pats pulpit' => 'US',
        'pbs' => 'US',
        'peterattiamd com' => 'US',
        'pff com' => 'US',
        'phonearena' => 'US',
        'phoronix' => 'US',
        'phys org' => 'GB',
        'pitchfork' => 'US',
        'pittsburgh hockey now' => 'US',
        'pittsburgh post gazette' => 'US',
        'psypost' => 'US',
        'push square' => 'GB',
        'real simple' => 'US',
        'ringside news' => 'US',
        'rock paper shotgun' => 'GB',
        'rolling stone' => 'US',
        'runner s world uk' => 'GB',
        'san francisco chronicle' => 'US',
        'scarlet and game' => 'US',
        'sciencedaily' => 'US',
        'science daily' => 'US',
        'sciencealert' => 'AU',
        'scitechdaily' => 'US',
        'screen rant' => 'US',
        'seeking alpha' => 'US',
        'semafor com' => 'US',
        'silver and black pride' => 'US',
        'silver screen and roll' => 'US',
        'slashgear' => 'US',
        'southernliving com' => 'US',
        'space daily' => 'US',
        'spacenews' => 'US',
        'sports illustrated' => 'US',
        'stampede blue' => 'US',
        'stat' => 'US',
        'tv series finale' => 'US',
        'tvline' => 'US',
        'the action network' => 'US',
        'the blast' => 'US',
        'the boston globe' => 'US',
        'the christian post' => 'US',
        'the colorado sun' => 'US',
        'the conversation africa' => 'ZA',
        'the daily galaxy great discoveries channel' => 'US',
        'the detroit news' => 'US',
        'the drive' => 'US',
        'the hindu' => 'IN',
        'the information' => 'US',
        'the ringer' => 'US',
        'the seattle times' => 'US',
        'thestreet' => 'US',
        'tom s guide' => 'US',
        'trekmovie' => 'US',
        'variety' => 'US',
        'vox' => 'US',
        'vsin com' => 'US',
        'vulture' => 'US',
        'wtop' => 'US',
        'windows central' => 'US',
        'windowslatest' => 'US',
        'women s health' => 'US',
        'wrestling news' => 'US',
        'yahoo entertainment' => 'US',
        'fox news' => 'US',
        'global news' => 'CA',
        'msnbc' => 'US',
        'nbc news' => 'US',
        'npr' => 'US',
        'rt' => 'RU',
        'soompi' => 'KR',
        'the guardian' => 'GB',
        'the telegraph' => 'GB',
        'the wall street journal' => 'US',
        'the washington post' => 'US',
        'woman home' => 'GB',
    ];

    private const GLOBAL_NAMES = [
        'afp',
        'associated press',
        'google news',
        'reuters',
        'yahoo news',
    ];

    private const COUNTRY_TLDS = [
        'ad' => 'AD', 'ae' => 'AE', 'af' => 'AF', 'ag' => 'AG', 'al' => 'AL',
        'am' => 'AM', 'ao' => 'AO', 'ar' => 'AR', 'at' => 'AT', 'au' => 'AU',
        'az' => 'AZ', 'ba' => 'BA', 'bb' => 'BB', 'bd' => 'BD', 'be' => 'BE',
        'bf' => 'BF', 'bg' => 'BG', 'bh' => 'BH', 'bi' => 'BI', 'bj' => 'BJ',
        'bo' => 'BO', 'br' => 'BR', 'bs' => 'BS', 'bw' => 'BW', 'by' => 'BY',
        'bz' => 'BZ', 'ca' => 'CA', 'cd' => 'CD', 'cf' => 'CF', 'cg' => 'CG',
        'ch' => 'CH', 'ci' => 'CI', 'cl' => 'CL', 'cm' => 'CM', 'cn' => 'CN',
        'co' => 'CO', 'cr' => 'CR', 'cu' => 'CU', 'cv' => 'CV', 'cy' => 'CY',
        'cz' => 'CZ', 'de' => 'DE', 'dj' => 'DJ', 'dk' => 'DK', 'do' => 'DO',
        'dz' => 'DZ', 'ec' => 'EC', 'ee' => 'EE', 'eg' => 'EG', 'er' => 'ER',
        'es' => 'ES', 'et' => 'ET', 'fi' => 'FI', 'fr' => 'FR', 'ga' => 'GA',
        'gb' => 'GB', 'gh' => 'GH', 'gm' => 'GM', 'gn' => 'GN', 'gq' => 'GQ',
        'gr' => 'GR', 'gt' => 'GT', 'gw' => 'GW', 'hk' => 'HK', 'hn' => 'HN',
        'hr' => 'HR', 'ht' => 'HT', 'hu' => 'HU', 'id' => 'ID', 'ie' => 'IE',
        'il' => 'IL', 'in' => 'IN', 'is' => 'IS', 'it' => 'IT', 'jm' => 'JM',
        'jo' => 'JO', 'jp' => 'JP', 'ke' => 'KE', 'kr' => 'KR', 'lb' => 'LB',
        'lk' => 'LK', 'lr' => 'LR', 'ls' => 'LS', 'lt' => 'LT', 'lu' => 'LU',
        'lv' => 'LV', 'ma' => 'MA', 'mg' => 'MG', 'mk' => 'MK', 'ml' => 'ML',
        'mr' => 'MR', 'mu' => 'MU', 'mw' => 'MW', 'mx' => 'MX', 'mz' => 'MZ',
        'na' => 'NA', 'ne' => 'NE', 'ng' => 'NG', 'ni' => 'NI', 'nl' => 'NL',
        'no' => 'NO', 'nz' => 'NZ', 'pa' => 'PA', 'pe' => 'PE', 'ph' => 'PH',
        'pk' => 'PK', 'pl' => 'PL', 'pt' => 'PT', 'py' => 'PY', 'qa' => 'QA',
        'ro' => 'RO', 'rs' => 'RS', 'ru' => 'RU', 'rw' => 'RW', 'sa' => 'SA',
        'sc' => 'SC', 'sd' => 'SD', 'se' => 'SE', 'sg' => 'SG', 'si' => 'SI',
        'sk' => 'SK', 'sl' => 'SL', 'sn' => 'SN', 'so' => 'SO', 'st' => 'ST',
        'sv' => 'SV', 'sz' => 'SZ', 'td' => 'TD', 'tg' => 'TG', 'tn' => 'TN',
        'tr' => 'TR', 'tt' => 'TT', 'tz' => 'TZ', 'ua' => 'UA', 'ug' => 'UG',
        'uk' => 'GB', 'us' => 'US', 'uy' => 'UY', 'va' => 'VA', 've' => 'VE',
        'za' => 'ZA', 'zm' => 'ZM', 'zw' => 'ZW',
    ];

    /**
     * @return array{country:string, confidence:int, method:string, basis:string}|null
     */
    public static function infer(?string $name, ?string $website, ?string $apiId = null): ?array
    {
        $normalizedName = self::normalizeName($name);

        if ($normalizedName !== '' && in_array($normalizedName, self::GLOBAL_NAMES, true)) {
            return null;
        }

        $host = self::hostFromWebsite($website);
        if ($host === null && str_contains((string) $name, '.')) {
            $host = self::hostFromWebsite($name);
        }
        if ($host === null && str_contains((string) $apiId, '.')) {
            $host = self::hostFromWebsite($apiId);
        }

        if ($host !== null) {
            foreach (self::DOMAIN_COUNTRIES as $domain => $country) {
                if ($host === $domain || str_ends_with($host, '.' . $domain)) {
                    return self::result($country, 95, 'domain-map', $domain);
                }
            }

            if (str_ends_with($host, '.co.uk') || str_ends_with($host, '.org.uk') || str_ends_with($host, '.ac.uk')) {
                return self::result('GB', 88, 'country-tld', $host);
            }

            $lastDot = strrpos($host, '.');
            $tld = $lastDot === false ? '' : substr($host, $lastDot + 1);
            if (in_array($tld, ['gov', 'mil'], true)) {
                return self::result('US', 90, 'us-public-tld', $host);
            }
            if (isset(self::COUNTRY_TLDS[$tld])) {
                return self::result(self::COUNTRY_TLDS[$tld], 86, 'country-tld', $host);
            }
        }

        if ($normalizedName !== '' && isset(self::NAME_COUNTRIES[$normalizedName])) {
            return self::result(self::NAME_COUNTRIES[$normalizedName], 82, 'name-map', (string) $name);
        }

        $normalizedApi = self::normalizeName(str_replace('-', ' ', (string) $apiId));
        if ($normalizedApi !== '' && isset(self::NAME_COUNTRIES[$normalizedApi])) {
            return self::result(self::NAME_COUNTRIES[$normalizedApi], 82, 'api-id-map', (string) $apiId);
        }

        return null;
    }

    public static function normalizeCountry(?string $country): ?string
    {
        $value = strtoupper(trim((string) $country));

        return preg_match('/^[A-Z]{2}$/', $value) ? $value : null;
    }

    private static function hostFromWebsite(?string $website): ?string
    {
        $raw = trim((string) $website);
        if ($raw === '') {
            return null;
        }

        $url = preg_match('#^https?://#i', $raw) ? $raw : 'https://' . ltrim($raw, '/');
        $host = parse_url($url, PHP_URL_HOST);
        if (! is_string($host) || $host === '') {
            return null;
        }

        $host = strtolower($host);
        $host = preg_replace('/^(www|m|amp)\./', '', $host) ?: $host;

        return trim($host, '.');
    }

    private static function normalizeName(?string $name): string
    {
        $value = strtolower(trim((string) $name));
        $value = preg_replace('/[^\pL\pN]+/u', ' ', $value) ?: '';

        return trim($value);
    }

    /**
     * @return array{country:string, confidence:int, method:string, basis:string}
     */
    private static function result(string $country, int $confidence, string $method, string $basis): array
    {
        return [
            'country' => $country,
            'confidence' => $confidence,
            'method' => $method,
            'basis' => $basis,
        ];
    }
}
