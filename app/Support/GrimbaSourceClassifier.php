<?php

namespace App\Support;

use Illuminate\Support\Str;

class GrimbaSourceClassifier
{
    private const VERSION = 'source-map-v1';

    /**
     * Public-record source baselines. Bias values are source-level editorial
     * orientation, not per-article opinion. Unknowns stay unknown unless a
     * high-confidence map entry exists.
     *
     * @var array<string, array<string, mixed>>
     */
    private const DOMAIN_PROFILES = [
        'abcnews.go.com' => ['name' => 'ABC News', 'bias_rating' => 'center', 'ownership_type' => 'conglomerate', 'owner_name' => 'The Walt Disney Company', 'credibility_score' => 82, 'country' => 'US', 'language' => 'en'],
        'abcnews.com' => ['name' => 'ABC News', 'bias_rating' => 'center', 'ownership_type' => 'conglomerate', 'owner_name' => 'The Walt Disney Company', 'credibility_score' => 82, 'country' => 'US', 'language' => 'en'],
        'abc.net.au' => ['name' => 'ABC News (AU)', 'bias_rating' => 'center', 'ownership_type' => 'government', 'owner_name' => 'Australian Broadcasting Corporation (public)', 'credibility_score' => 84, 'country' => 'AU', 'language' => 'en'],
        'afp.com' => ['name' => 'AFP', 'bias_rating' => 'center', 'ownership_type' => 'government', 'owner_name' => 'Agence France-Presse (public-private)', 'credibility_score' => 90, 'country' => 'FR', 'language' => 'fr'],
        'aljazeera.com' => ['name' => 'Al Jazeera English', 'bias_rating' => 'center', 'ownership_type' => 'government', 'owner_name' => 'Qatar Media Corporation (state-funded)', 'credibility_score' => 74, 'country' => 'QA', 'language' => 'en'],
        'androidpolice.com' => ['name' => 'Android Police', 'bias_rating' => 'center', 'ownership_type' => 'corporation', 'owner_name' => 'Valnet Inc.', 'credibility_score' => 70, 'country' => 'US', 'language' => 'en'],
        'apnews.com' => ['name' => 'Associated Press', 'bias_rating' => 'center', 'ownership_type' => 'independent', 'owner_name' => 'AP cooperative (member-owned)', 'credibility_score' => 92, 'country' => 'US', 'language' => 'en'],
        'axios.com' => ['name' => 'Axios', 'bias_rating' => 'center', 'ownership_type' => 'corporation', 'owner_name' => 'Cox Enterprises', 'credibility_score' => 80, 'country' => 'US', 'language' => 'en'],
        '9to5google.com' => ['name' => '9to5Google', 'bias_rating' => 'center', 'ownership_type' => 'corporation', 'owner_name' => '9to5 LLC', 'credibility_score' => 72, 'country' => 'US', 'language' => 'en'],
        '9to5mac.com' => ['name' => '9to5Mac', 'bias_rating' => 'center', 'ownership_type' => 'corporation', 'owner_name' => '9to5 LLC', 'credibility_score' => 72, 'country' => 'US', 'language' => 'en'],
        'bbc.com' => ['name' => 'BBC News', 'bias_rating' => 'center', 'ownership_type' => 'government', 'owner_name' => 'BBC (British public broadcaster)', 'credibility_score' => 86, 'country' => 'GB', 'language' => 'en'],
        'bbc.co.uk' => ['name' => 'BBC News', 'bias_rating' => 'center', 'ownership_type' => 'government', 'owner_name' => 'BBC (British public broadcaster)', 'credibility_score' => 86, 'country' => 'GB', 'language' => 'en'],
        'bgr.com' => ['name' => 'BGR', 'bias_rating' => 'center', 'ownership_type' => 'corporation', 'owner_name' => 'Valnet Inc.', 'credibility_score' => 66, 'country' => 'US', 'language' => 'en'],
        'bleepingcomputer.com' => ['name' => 'BleepingComputer', 'bias_rating' => 'center', 'ownership_type' => 'independent', 'owner_name' => 'BleepingComputer LLC', 'credibility_score' => 74, 'country' => 'US', 'language' => 'en'],
        'bloomberg.com' => ['name' => 'Bloomberg', 'bias_rating' => 'center', 'ownership_type' => 'individual', 'owner_name' => 'Bloomberg L.P. (Michael Bloomberg)', 'credibility_score' => 86, 'country' => 'US', 'language' => 'en'],
        'bloodyelbow.com' => ['name' => 'Bloody Elbow', 'bias_rating' => 'center', 'ownership_type' => 'independent', 'owner_name' => 'Bloody Elbow', 'credibility_score' => 60, 'country' => 'US', 'language' => 'en'],
        'defector.com' => ['name' => 'Defector', 'bias_rating' => 'left', 'ownership_type' => 'independent', 'owner_name' => 'Defector Media LLC (employee-owned)', 'credibility_score' => 72, 'country' => 'US', 'language' => 'en'],
        'dw.com' => ['name' => 'DW (English)', 'bias_rating' => 'center', 'ownership_type' => 'government', 'owner_name' => 'Deutsche Welle (German public broadcaster)', 'credibility_score' => 82, 'country' => 'DE', 'language' => 'en'],
        'boursorama.com' => ['name' => 'Boursorama', 'bias_rating' => 'center', 'ownership_type' => 'corporation', 'owner_name' => 'Societe Generale', 'credibility_score' => 70, 'country' => 'FR', 'language' => 'fr'],
        'breitbart.com' => ['name' => 'Breitbart News', 'bias_rating' => 'right', 'ownership_type' => 'corporation', 'owner_name' => 'Breitbart News Network LLC', 'credibility_score' => 48, 'country' => 'US', 'language' => 'en'],
        'businessinsider.com' => ['name' => 'Business Insider', 'bias_rating' => 'center', 'ownership_type' => 'conglomerate', 'owner_name' => 'Axel Springer SE', 'credibility_score' => 76, 'country' => 'US', 'language' => 'en'],
        'cbc.ca' => ['name' => 'CBC News', 'bias_rating' => 'center', 'ownership_type' => 'government', 'owner_name' => 'CBC/Radio-Canada (Canadian public broadcaster)', 'credibility_score' => 84, 'country' => 'CA', 'language' => 'en'],
        'cbsnews.com' => ['name' => 'CBS News', 'bias_rating' => 'center', 'ownership_type' => 'conglomerate', 'owner_name' => 'Paramount Global', 'credibility_score' => 80, 'country' => 'US', 'language' => 'en'],
        'cleveland.com' => ['name' => 'cleveland.com', 'bias_rating' => 'center', 'ownership_type' => 'conglomerate', 'owner_name' => 'Advance Local', 'credibility_score' => 70, 'country' => 'US', 'language' => 'en'],
        'cnbc.com' => ['name' => 'CNBC', 'bias_rating' => 'center', 'ownership_type' => 'telecom', 'owner_name' => 'NBCUniversal (Comcast)', 'credibility_score' => 80, 'country' => 'US', 'language' => 'en'],
        'cnn.com' => ['name' => 'CNN', 'bias_rating' => 'left', 'ownership_type' => 'conglomerate', 'owner_name' => 'Warner Bros. Discovery', 'credibility_score' => 74, 'country' => 'US', 'language' => 'en'],
        'cagesideseats.com' => ['name' => 'Cageside Seats', 'bias_rating' => 'center', 'ownership_type' => 'corporation', 'owner_name' => 'Vox Media', 'credibility_score' => 62, 'country' => 'US', 'language' => 'en'],
        'dailymail.co.uk' => ['name' => 'Daily Mail', 'bias_rating' => 'right', 'ownership_type' => 'conglomerate', 'owner_name' => 'Daily Mail and General Trust', 'credibility_score' => 54, 'country' => 'GB', 'language' => 'en'],
        'dailygalaxy.com' => ['name' => 'The Daily Galaxy', 'bias_rating' => 'center', 'ownership_type' => 'independent', 'owner_name' => 'The Daily Galaxy', 'credibility_score' => 62, 'country' => 'US', 'language' => 'en'],
        'dailybeast.com' => ['name' => 'The Daily Beast', 'bias_rating' => 'left', 'ownership_type' => 'corporation', 'owner_name' => 'IAC', 'credibility_score' => 66, 'country' => 'US', 'language' => 'en'],
        'dexerto.com' => ['name' => 'Dexerto', 'bias_rating' => 'center', 'ownership_type' => 'corporation', 'owner_name' => 'Dexerto Ltd.', 'credibility_score' => 62, 'country' => 'GB', 'language' => 'en'],
        'espn.com' => ['name' => 'ESPN', 'bias_rating' => 'center', 'ownership_type' => 'conglomerate', 'owner_name' => 'The Walt Disney Company / Hearst Communications', 'credibility_score' => 76, 'country' => 'US', 'language' => 'en'],
        'ew.com' => ['name' => 'Entertainment Weekly', 'bias_rating' => 'center', 'ownership_type' => 'corporation', 'owner_name' => 'Dotdash Meredith (IAC)', 'credibility_score' => 70, 'country' => 'US', 'language' => 'en'],
        'earth.com' => ['name' => 'Earth.com', 'bias_rating' => 'center', 'ownership_type' => 'corporation', 'owner_name' => 'Earth.com Inc.', 'credibility_score' => 70, 'country' => 'US', 'language' => 'en'],
        'eurogamer.net' => ['name' => 'Eurogamer', 'bias_rating' => 'center', 'ownership_type' => 'corporation', 'owner_name' => 'IGN Entertainment (Ziff Davis)', 'credibility_score' => 70, 'country' => 'GB', 'language' => 'en'],
        'financialafrik.com' => ['name' => 'Financial Afrik', 'bias_rating' => 'center', 'ownership_type' => 'independent', 'owner_name' => 'Financial Afrik', 'credibility_score' => 70, 'country' => 'SN', 'language' => 'fr'],
        'forbes.com' => ['name' => 'Forbes', 'bias_rating' => 'center', 'ownership_type' => 'conglomerate', 'owner_name' => 'Integrated Whale Media Investments', 'credibility_score' => 72, 'country' => 'US', 'language' => 'en'],
        'foxnews.com' => ['name' => 'Fox News', 'bias_rating' => 'right', 'ownership_type' => 'conglomerate', 'owner_name' => 'Fox Corporation (Murdoch family)', 'credibility_score' => 62, 'country' => 'US', 'language' => 'en'],
        'france24.com' => ['name' => 'France 24', 'bias_rating' => 'center', 'ownership_type' => 'government', 'owner_name' => 'France Medias Monde (state)', 'credibility_score' => 80, 'country' => 'FR', 'language' => 'fr'],
        'ft.com' => ['name' => 'Financial Times', 'bias_rating' => 'center', 'ownership_type' => 'conglomerate', 'owner_name' => 'Nikkei Inc.', 'credibility_score' => 88, 'country' => 'GB', 'language' => 'en'],
        'futureplc.com' => ['name' => 'Future plc', 'bias_rating' => 'center', 'ownership_type' => 'corporation', 'owner_name' => 'Future plc', 'credibility_score' => 70, 'country' => 'GB', 'language' => 'en'],
        'gematsu.com' => ['name' => 'Gematsu', 'bias_rating' => 'center', 'ownership_type' => 'independent', 'owner_name' => 'Gematsu', 'credibility_score' => 68, 'country' => 'US', 'language' => 'en'],
        'geeky-gadgets.com' => ['name' => 'Geeky Gadgets', 'bias_rating' => 'center', 'ownership_type' => 'independent', 'owner_name' => 'Geeky Gadgets', 'credibility_score' => 60, 'country' => 'GB', 'language' => 'en'],
        'globalnews.ca' => ['name' => 'Global News', 'bias_rating' => 'center', 'ownership_type' => 'conglomerate', 'owner_name' => 'Corus Entertainment', 'credibility_score' => 78, 'country' => 'CA', 'language' => 'en'],
        'grv.media' => ['name' => 'GRV Media', 'bias_rating' => 'center', 'ownership_type' => 'corporation', 'owner_name' => 'GRV Media', 'credibility_score' => 62, 'country' => 'GB', 'language' => 'en'],
        'hackaday.com' => ['name' => 'Hackaday', 'bias_rating' => 'center', 'ownership_type' => 'corporation', 'owner_name' => 'Supplyframe', 'credibility_score' => 68, 'country' => 'US', 'language' => 'en'],
        'hearst.com' => ['name' => 'Hearst', 'bias_rating' => 'center', 'ownership_type' => 'conglomerate', 'owner_name' => 'Hearst Communications', 'credibility_score' => 72, 'country' => 'US', 'language' => 'en'],
        'heatworld.com' => ['name' => 'Heat', 'bias_rating' => 'center', 'ownership_type' => 'corporation', 'owner_name' => 'Bauer Media Group', 'credibility_score' => 58, 'country' => 'GB', 'language' => 'en'],
        'huffpost.com' => ['name' => 'HuffPost', 'bias_rating' => 'left', 'ownership_type' => 'corporation', 'owner_name' => 'BuzzFeed Inc.', 'credibility_score' => 66, 'country' => 'US', 'language' => 'en'],
        'instyle.com' => ['name' => 'InStyle', 'bias_rating' => 'center', 'ownership_type' => 'corporation', 'owner_name' => 'Dotdash Meredith (IAC)', 'credibility_score' => 68, 'country' => 'US', 'language' => 'en'],
        'ici.fr' => ['name' => 'ici', 'bias_rating' => 'center', 'ownership_type' => 'government', 'owner_name' => 'Radio France / France Televisions (public)', 'credibility_score' => 78, 'country' => 'FR', 'language' => 'fr'],
        'indiandefencereview.com' => ['name' => 'Indian Defence Review', 'bias_rating' => 'center', 'ownership_type' => 'independent', 'owner_name' => 'Lancer Publishers', 'credibility_score' => 60, 'country' => 'IN', 'language' => 'en'],
        'insider-gaming.com' => ['name' => 'Insider Gaming', 'bias_rating' => 'center', 'ownership_type' => 'independent', 'owner_name' => 'Insider Gaming', 'credibility_score' => 62, 'country' => 'GB', 'language' => 'en'],
        'jalopnik.com' => ['name' => 'Jalopnik', 'bias_rating' => 'center', 'ownership_type' => 'corporation', 'owner_name' => 'Static Media', 'credibility_score' => 62, 'country' => 'US', 'language' => 'en'],
        'kotaku.com' => ['name' => 'Kotaku', 'bias_rating' => 'center', 'ownership_type' => 'corporation', 'owner_name' => 'Keleops Media', 'credibility_score' => 66, 'country' => 'US', 'language' => 'en'],
        'ladepeche.fr' => ['name' => 'La Depeche', 'bias_rating' => 'center', 'ownership_type' => 'individual', 'owner_name' => 'Groupe La Depeche / famille Baylet', 'credibility_score' => 76, 'country' => 'FR', 'language' => 'fr'],
        'lefigaro.fr' => ['name' => 'Le Figaro', 'bias_rating' => 'right', 'ownership_type' => 'conglomerate', 'owner_name' => 'Groupe Dassault', 'credibility_score' => 76, 'country' => 'FR', 'language' => 'fr'],
        'lemonde.fr' => ['name' => 'Le Monde', 'bias_rating' => 'left', 'ownership_type' => 'individual', 'owner_name' => 'Xavier Niel + Daniel Kretinsky', 'credibility_score' => 82, 'country' => 'FR', 'language' => 'fr'],
        'liberation.fr' => ['name' => 'Liberation', 'bias_rating' => 'left', 'ownership_type' => 'telecom', 'owner_name' => 'Patrick Drahi (Altice)', 'credibility_score' => 74, 'country' => 'FR', 'language' => 'fr'],
        'livescience.com' => ['name' => 'Live Science', 'bias_rating' => 'center', 'ownership_type' => 'corporation', 'owner_name' => 'Future plc', 'credibility_score' => 74, 'country' => 'US', 'language' => 'en'],
        'macrumors.com' => ['name' => 'MacRumors', 'bias_rating' => 'center', 'ownership_type' => 'independent', 'owner_name' => 'MacRumors.com LLC', 'credibility_score' => 70, 'country' => 'US', 'language' => 'en'],
        'mashable.com' => ['name' => 'Mashable', 'bias_rating' => 'center', 'ownership_type' => 'corporation', 'owner_name' => 'Ziff Davis', 'credibility_score' => 66, 'country' => 'US', 'language' => 'en'],
        'mediapart.fr' => ['name' => 'Mediapart', 'bias_rating' => 'left', 'ownership_type' => 'independent', 'owner_name' => 'Societe des Amis de Mediapart (employee-owned)', 'credibility_score' => 80, 'country' => 'FR', 'language' => 'fr'],
        'msnbc.com' => ['name' => 'MSNBC', 'bias_rating' => 'left', 'ownership_type' => 'telecom', 'owner_name' => 'NBCUniversal (Comcast)', 'credibility_score' => 66, 'country' => 'US', 'language' => 'en'],
        'mlbtraderumors.com' => ['name' => 'MLB Trade Rumors', 'bias_rating' => 'center', 'ownership_type' => 'independent', 'owner_name' => 'Trade Rumors', 'credibility_score' => 72, 'country' => 'US', 'language' => 'en'],
        'motorsport.com' => ['name' => 'Motorsport.com', 'bias_rating' => 'center', 'ownership_type' => 'corporation', 'owner_name' => 'Motorsport Network', 'credibility_score' => 70, 'country' => 'US', 'language' => 'en'],
        'mynintendonews.com' => ['name' => 'My Nintendo News', 'bias_rating' => 'center', 'ownership_type' => 'independent', 'owner_name' => 'My Nintendo News', 'credibility_score' => 62, 'country' => 'GB', 'language' => 'en'],
        'nationalreview.com' => ['name' => 'National Review', 'bias_rating' => 'right', 'ownership_type' => 'independent', 'owner_name' => 'National Review Inc.', 'credibility_score' => 66, 'country' => 'US', 'language' => 'en'],
        'nbcnews.com' => ['name' => 'NBC News', 'bias_rating' => 'center', 'ownership_type' => 'telecom', 'owner_name' => 'NBCUniversal (Comcast)', 'credibility_score' => 80, 'country' => 'US', 'language' => 'en'],
        'nbcsports.com' => ['name' => 'NBC Sports', 'bias_rating' => 'center', 'ownership_type' => 'telecom', 'owner_name' => 'NBCUniversal (Comcast)', 'credibility_score' => 78, 'country' => 'US', 'language' => 'en'],
        'newsweek.com' => ['name' => 'Newsweek', 'bias_rating' => 'center', 'ownership_type' => 'corporation', 'owner_name' => 'IBT Media', 'credibility_score' => 64, 'country' => 'US', 'language' => 'en'],
        'newsnationnow.com' => ['name' => 'NewsNation', 'bias_rating' => 'center', 'ownership_type' => 'conglomerate', 'owner_name' => 'Nexstar Media Group', 'credibility_score' => 66, 'country' => 'US', 'language' => 'en'],
        'nhl.com' => ['name' => 'NHL News', 'bias_rating' => 'center', 'ownership_type' => 'corporation', 'owner_name' => 'National Hockey League', 'credibility_score' => 70, 'country' => 'US', 'language' => 'en'],
        'nintendolife.com' => ['name' => 'Nintendo Life', 'bias_rating' => 'center', 'ownership_type' => 'corporation', 'owner_name' => 'Hookshot Media', 'credibility_score' => 68, 'country' => 'GB', 'language' => 'en'],
        'npr.org' => ['name' => 'NPR', 'bias_rating' => 'left', 'ownership_type' => 'independent', 'owner_name' => 'National Public Radio', 'credibility_score' => 84, 'country' => 'US', 'language' => 'en'],
        'nypost.com' => ['name' => 'New York Post', 'bias_rating' => 'right', 'ownership_type' => 'conglomerate', 'owner_name' => 'News Corp (Murdoch family)', 'credibility_score' => 56, 'country' => 'US', 'language' => 'en'],
        'nytimes.com' => ['name' => 'The New York Times', 'bias_rating' => 'left', 'ownership_type' => 'corporation', 'owner_name' => 'The New York Times Company', 'credibility_score' => 84, 'country' => 'US', 'language' => 'en'],
        'ouest-france.fr' => ['name' => 'Ouest-France', 'bias_rating' => 'center', 'ownership_type' => 'independent', 'owner_name' => 'SIPA Ouest-France / Association pour le soutien des principes de la democratie humaniste', 'credibility_score' => 80, 'country' => 'FR', 'language' => 'fr'],
        'pbs.org' => ['name' => 'PBS', 'bias_rating' => 'center', 'ownership_type' => 'government', 'owner_name' => 'Public Broadcasting Service', 'credibility_score' => 84, 'country' => 'US', 'language' => 'en'],
        'phonearena.com' => ['name' => 'PhoneArena', 'bias_rating' => 'center', 'ownership_type' => 'corporation', 'owner_name' => 'PhoneArena', 'credibility_score' => 64, 'country' => 'US', 'language' => 'en'],
        'phoronix.com' => ['name' => 'Phoronix', 'bias_rating' => 'center', 'ownership_type' => 'independent', 'owner_name' => 'Phoronix Media', 'credibility_score' => 72, 'country' => 'US', 'language' => 'en'],
        'phys.org' => ['name' => 'Phys.Org', 'bias_rating' => 'center', 'ownership_type' => 'corporation', 'owner_name' => 'Science X Network', 'credibility_score' => 78, 'country' => 'IM', 'language' => 'en'],
        'politico.com' => ['name' => 'Politico', 'bias_rating' => 'center', 'ownership_type' => 'conglomerate', 'owner_name' => 'Axel Springer SE', 'credibility_score' => 80, 'country' => 'US', 'language' => 'en'],
        'politico.eu' => ['name' => 'Politico Europe', 'bias_rating' => 'center', 'ownership_type' => 'conglomerate', 'owner_name' => 'Axel Springer SE', 'credibility_score' => 80, 'country' => 'BE', 'language' => 'en'],
        'psypost.org' => ['name' => 'PsyPost', 'bias_rating' => 'center', 'ownership_type' => 'independent', 'owner_name' => 'PsyPost', 'credibility_score' => 66, 'country' => 'US', 'language' => 'en'],
        'reuters.com' => ['name' => 'Reuters', 'bias_rating' => 'center', 'ownership_type' => 'corporation', 'owner_name' => 'Thomson Reuters', 'credibility_score' => 92, 'country' => 'GB', 'language' => 'en'],
        'ringsidenews.com' => ['name' => 'Ringside News', 'bias_rating' => 'center', 'ownership_type' => 'independent', 'owner_name' => 'Ringside News', 'credibility_score' => 54, 'country' => 'US', 'language' => 'en'],
        'rollingstone.com' => ['name' => 'Rolling Stone', 'bias_rating' => 'left', 'ownership_type' => 'corporation', 'owner_name' => 'Penske Media Corporation', 'credibility_score' => 68, 'country' => 'US', 'language' => 'en'],
        'rt.com' => ['name' => 'RT', 'bias_rating' => 'right', 'ownership_type' => 'government', 'owner_name' => 'TV-Novosti (Russian government-funded)', 'credibility_score' => 35, 'country' => 'RU', 'language' => 'en'],
        'sciencealert.com' => ['name' => 'ScienceAlert', 'bias_rating' => 'center', 'ownership_type' => 'independent', 'owner_name' => 'ScienceAlert Pty Ltd', 'credibility_score' => 76, 'country' => 'AU', 'language' => 'en'],
        'sciencedaily.com' => ['name' => 'Science Daily', 'bias_rating' => 'center', 'ownership_type' => 'corporation', 'owner_name' => 'ScienceDaily LLC', 'credibility_score' => 78, 'country' => 'US', 'language' => 'en'],
        'scitechdaily.com' => ['name' => 'SciTechDaily', 'bias_rating' => 'center', 'ownership_type' => 'corporation', 'owner_name' => 'DailyeDeals Inc.', 'credibility_score' => 72, 'country' => 'US', 'language' => 'en'],
        'screenrant.com' => ['name' => 'Screen Rant', 'bias_rating' => 'center', 'ownership_type' => 'corporation', 'owner_name' => 'Valnet Inc.', 'credibility_score' => 66, 'country' => 'US', 'language' => 'en'],
        'slate.com' => ['name' => 'Slate', 'bias_rating' => 'left', 'ownership_type' => 'conglomerate', 'owner_name' => 'Graham Holdings Company', 'credibility_score' => 68, 'country' => 'US', 'language' => 'en'],
        'slate.fr' => ['name' => 'Slate France', 'bias_rating' => 'left', 'ownership_type' => 'independent', 'owner_name' => 'Slate France', 'credibility_score' => 68, 'country' => 'FR', 'language' => 'fr'],
        'space.com' => ['name' => 'Space.com', 'bias_rating' => 'center', 'ownership_type' => 'corporation', 'owner_name' => 'Future plc', 'credibility_score' => 74, 'country' => 'US', 'language' => 'en'],
        'si.com' => ['name' => 'Sports Illustrated', 'bias_rating' => 'center', 'ownership_type' => 'corporation', 'owner_name' => 'Minute Media / Authentic Brands Group', 'credibility_score' => 70, 'country' => 'US', 'language' => 'en'],
        'sfchronicle.com' => ['name' => 'San Francisco Chronicle', 'bias_rating' => 'center', 'ownership_type' => 'conglomerate', 'owner_name' => 'Hearst Communications', 'credibility_score' => 76, 'country' => 'US', 'language' => 'en'],
        'sudouest.fr' => ['name' => 'Sud Ouest', 'bias_rating' => 'center', 'ownership_type' => 'corporation', 'owner_name' => 'Groupe Sud Ouest', 'credibility_score' => 76, 'country' => 'FR', 'language' => 'fr'],
        'techcrunch.com' => ['name' => 'TechCrunch', 'bias_rating' => 'center', 'ownership_type' => 'corporation', 'owner_name' => 'Yahoo Inc. (Apollo Global Management)', 'credibility_score' => 74, 'country' => 'US', 'language' => 'en'],
        'telegraph.co.uk' => ['name' => 'The Telegraph', 'bias_rating' => 'right', 'ownership_type' => 'conglomerate', 'owner_name' => 'RedBird IMI', 'credibility_score' => 70, 'country' => 'GB', 'language' => 'en'],
        'thebrighterside.news' => ['name' => 'The Brighter Side of News', 'bias_rating' => 'center', 'ownership_type' => 'independent', 'owner_name' => 'The Brighter Side of News', 'credibility_score' => 60, 'country' => 'US', 'language' => 'en'],
        'theregister.com' => ['name' => 'The Register', 'bias_rating' => 'center', 'ownership_type' => 'corporation', 'owner_name' => 'Situation Publishing', 'credibility_score' => 76, 'country' => 'GB', 'language' => 'en'],
        'theamericanconservative.com' => ['name' => 'The American Conservative', 'bias_rating' => 'right', 'ownership_type' => 'independent', 'owner_name' => 'The American Ideas Institute', 'credibility_score' => 62, 'country' => 'US', 'language' => 'en'],
        'theactionnetwork.com' => ['name' => 'The Action Network', 'bias_rating' => 'center', 'ownership_type' => 'corporation', 'owner_name' => 'Better Collective', 'credibility_score' => 66, 'country' => 'US', 'language' => 'en'],
        'theguardian.com' => ['name' => 'The Guardian', 'bias_rating' => 'left', 'ownership_type' => 'independent', 'owner_name' => 'Scott Trust Limited', 'credibility_score' => 82, 'country' => 'GB', 'language' => 'en'],
        'thehill.com' => ['name' => 'The Hill', 'bias_rating' => 'center', 'ownership_type' => 'conglomerate', 'owner_name' => 'Nexstar Media Group', 'credibility_score' => 76, 'country' => 'US', 'language' => 'en'],
        'theconversation.com' => ['name' => 'The Conversation', 'bias_rating' => 'center', 'ownership_type' => 'independent', 'owner_name' => 'The Conversation Trust', 'credibility_score' => 84, 'country' => 'AU', 'language' => 'en'],
        'theconversation.com/africa' => ['name' => 'The Conversation Africa', 'bias_rating' => 'center', 'ownership_type' => 'independent', 'owner_name' => 'The Conversation Africa', 'credibility_score' => 84, 'country' => 'ZA', 'language' => 'en'],
        'fool.com' => ['name' => 'Motley Fool', 'bias_rating' => 'center', 'ownership_type' => 'corporation', 'owner_name' => 'The Motley Fool Holdings Inc.', 'credibility_score' => 66, 'country' => 'US', 'language' => 'en'],
        'futurism.com' => ['name' => 'Futurism', 'bias_rating' => 'center', 'ownership_type' => 'corporation', 'owner_name' => 'Recurrent Ventures', 'credibility_score' => 62, 'country' => 'US', 'language' => 'en'],
        'pushsquare.com' => ['name' => 'Push Square', 'bias_rating' => 'center', 'ownership_type' => 'corporation', 'owner_name' => 'Hookshot Media', 'credibility_score' => 68, 'country' => 'GB', 'language' => 'en'],
        'thestreet.com' => ['name' => 'TheStreet', 'bias_rating' => 'center', 'ownership_type' => 'corporation', 'owner_name' => 'The Arena Group', 'credibility_score' => 70, 'country' => 'US', 'language' => 'en'],
        'theverge.com' => ['name' => 'The Verge', 'bias_rating' => 'center', 'ownership_type' => 'corporation', 'owner_name' => 'Vox Media', 'credibility_score' => 74, 'country' => 'US', 'language' => 'en'],
        'time.com' => ['name' => 'Time', 'bias_rating' => 'left', 'ownership_type' => 'individual', 'owner_name' => 'Marc Benioff', 'credibility_score' => 76, 'country' => 'US', 'language' => 'en'],
        'tipranks.com' => ['name' => 'TipRanks', 'bias_rating' => 'center', 'ownership_type' => 'corporation', 'owner_name' => 'TipRanks Ltd.', 'credibility_score' => 70, 'country' => 'IL', 'language' => 'en'],
        'tmz.com' => ['name' => 'TMZ', 'bias_rating' => 'center', 'ownership_type' => 'corporation', 'owner_name' => 'Fox Corporation', 'credibility_score' => 52, 'country' => 'US', 'language' => 'en'],
        'tomsguide.com' => ['name' => "Tom's Guide", 'bias_rating' => 'center', 'ownership_type' => 'corporation', 'owner_name' => 'Future plc', 'credibility_score' => 72, 'country' => 'US', 'language' => 'en'],
        'telerama.fr' => ['name' => 'Telerama', 'bias_rating' => 'center', 'ownership_type' => 'conglomerate', 'owner_name' => 'Groupe Le Monde', 'credibility_score' => 74, 'country' => 'FR', 'language' => 'fr'],
        'tvline.com' => ['name' => 'TVLine', 'bias_rating' => 'center', 'ownership_type' => 'corporation', 'owner_name' => 'Penske Media Corporation', 'credibility_score' => 68, 'country' => 'US', 'language' => 'en'],
        'tv5monde.com' => ['name' => 'TV5MONDE', 'bias_rating' => 'center', 'ownership_type' => 'government', 'owner_name' => 'TV5MONDE public media consortium', 'credibility_score' => 78, 'country' => 'FR', 'language' => 'fr'],
        'usatoday.com' => ['name' => 'USA Today', 'bias_rating' => 'center', 'ownership_type' => 'private_equity', 'owner_name' => 'Gannett Co.', 'credibility_score' => 76, 'country' => 'US', 'language' => 'en'],
        'vox.com' => ['name' => 'Vox', 'bias_rating' => 'left', 'ownership_type' => 'corporation', 'owner_name' => 'Vox Media', 'credibility_score' => 72, 'country' => 'US', 'language' => 'en'],
        'variety.com' => ['name' => 'Variety', 'bias_rating' => 'center', 'ownership_type' => 'corporation', 'owner_name' => 'Penske Media Corporation', 'credibility_score' => 76, 'country' => 'US', 'language' => 'en'],
        'warhammer-community.com' => ['name' => 'Warhammer Community', 'bias_rating' => 'center', 'ownership_type' => 'corporation', 'owner_name' => 'Games Workshop', 'credibility_score' => 62, 'country' => 'GB', 'language' => 'en'],
        'videocardz.com' => ['name' => 'VideoCardz', 'bias_rating' => 'center', 'ownership_type' => 'independent', 'owner_name' => 'VideoCardz', 'credibility_score' => 64, 'country' => 'PL', 'language' => 'en'],
        'washingtonpost.com' => ['name' => 'The Washington Post', 'bias_rating' => 'left', 'ownership_type' => 'individual', 'owner_name' => 'Nash Holdings (Jeff Bezos)', 'credibility_score' => 82, 'country' => 'US', 'language' => 'en'],
        'washingtontimes.com' => ['name' => 'The Washington Times', 'bias_rating' => 'right', 'ownership_type' => 'corporation', 'owner_name' => 'Operations Holdings (Unification Church)', 'credibility_score' => 56, 'country' => 'US', 'language' => 'en'],
        'wccftech.com' => ['name' => 'Wccftech', 'bias_rating' => 'center', 'ownership_type' => 'independent', 'owner_name' => 'Wccftech', 'credibility_score' => 58, 'country' => 'PK', 'language' => 'en'],
        'wired.com' => ['name' => 'Wired', 'bias_rating' => 'center', 'ownership_type' => 'corporation', 'owner_name' => 'Conde Nast (Advance Publications)', 'credibility_score' => 76, 'country' => 'US', 'language' => 'en'],
        'wsj.com' => ['name' => 'The Wall Street Journal', 'bias_rating' => 'right', 'ownership_type' => 'conglomerate', 'owner_name' => 'News Corp (Murdoch family)', 'credibility_score' => 84, 'country' => 'US', 'language' => 'en'],
        'yahoo.com' => ['name' => 'Yahoo', 'bias_rating' => 'center', 'ownership_type' => 'private_equity', 'owner_name' => 'Yahoo Inc. (Apollo Global Management)', 'credibility_score' => 68, 'country' => 'US', 'language' => 'en'],
    ];

    /**
     * @var array<string, string>
     */
    private const NAME_TO_DOMAIN = [
        'abc news' => 'abcnews.go.com',
        'abc news au' => 'abc.net.au',
        'afp' => 'afp.com',
        'al jazeera english' => 'aljazeera.com',
        'android police' => 'androidpolice.com',
        'associated press' => 'apnews.com',
        'ap' => 'apnews.com',
        'axios' => 'axios.com',
        '9to5google com' => '9to5google.com',
        '9to5google' => '9to5google.com',
        '9to5mac' => '9to5mac.com',
        'bbc' => 'bbc.com',
        'bbc news' => 'bbc.com',
        'bgr' => 'bgr.com',
        'bleepingcomputer' => 'bleepingcomputer.com',
        'bleeping computer' => 'bleepingcomputer.com',
        'bloody elbow' => 'bloodyelbow.com',
        'bloomberg' => 'bloomberg.com',
        'boursorama' => 'boursorama.com',
        'breitbart' => 'breitbart.com',
        'breitbart news' => 'breitbart.com',
        'business insider' => 'businessinsider.com',
        'cbc news' => 'cbc.ca',
        'cbs news' => 'cbsnews.com',
        'cleveland com' => 'cleveland.com',
        'cnbc' => 'cnbc.com',
        'cnn' => 'cnn.com',
        'cageside seats' => 'cagesideseats.com',
        'daily mail' => 'dailymail.co.uk',
        'daily galaxy' => 'dailygalaxy.com',
        'daily beast' => 'dailybeast.com',
        'the daily beast' => 'dailybeast.com',
        'defector com' => 'defector.com',
        'defector' => 'defector.com',
        'dexerto' => 'dexerto.com',
        'dw english' => 'dw.com',
        'dw' => 'dw.com',
        'deutsche welle' => 'dw.com',
        'entertainment weekly' => 'ew.com',
        'espn' => 'espn.com',
        'earth com' => 'earth.com',
        'eurogamer net' => 'eurogamer.net',
        'eurogamer' => 'eurogamer.net',
        'financial afrik' => 'financialafrik.com',
        'financial times' => 'ft.com',
        'forbes' => 'forbes.com',
        'fox news' => 'foxnews.com',
        'france 24' => 'france24.com',
        'gematsu' => 'gematsu.com',
        'geeky gadgets' => 'geeky-gadgets.com',
        'global news' => 'globalnews.ca',
        'heat com' => 'heatworld.com',
        'heat' => 'heatworld.com',
        'hackaday' => 'hackaday.com',
        'hitc football gaming movies tv music' => 'grv.media',
        'hitc' => 'grv.media',
        'huffpost' => 'huffpost.com',
        'ici' => 'ici.fr',
        'ici fr' => 'ici.fr',
        'instyle' => 'instyle.com',
        'indian defence review' => 'indiandefencereview.com',
        'indiandefencereview com' => 'indiandefencereview.com',
        'insider gaming' => 'insider-gaming.com',
        'insider gaming com' => 'insider-gaming.com',
        'jalopnik' => 'jalopnik.com',
        'kotaku' => 'kotaku.com',
        'ladepeche fr' => 'ladepeche.fr',
        'la depeche' => 'ladepeche.fr',
        'le figaro' => 'lefigaro.fr',
        'le monde' => 'lemonde.fr',
        'live science' => 'livescience.com',
        'liberation' => 'liberation.fr',
        'macrumors' => 'macrumors.com',
        'mashable' => 'mashable.com',
        'mediapart' => 'mediapart.fr',
        'msnbc' => 'msnbc.com',
        'mlb trade rumors' => 'mlbtraderumors.com',
        'motorsport com' => 'motorsport.com',
        'motorsport' => 'motorsport.com',
        'my nintendo news' => 'mynintendonews.com',
        'national review' => 'nationalreview.com',
        'nbc news' => 'nbcnews.com',
        'nbcsports com' => 'nbcsports.com',
        'nbc sports' => 'nbcsports.com',
        'newsweek' => 'newsweek.com',
        'new york post' => 'nypost.com',
        'newsnationnow com' => 'newsnationnow.com',
        'newsnation' => 'newsnationnow.com',
        'nhl news' => 'nhl.com',
        'nhl' => 'nhl.com',
        'nintendo life' => 'nintendolife.com',
        'npr' => 'npr.org',
        'ouest france' => 'ouest-france.fr',
        'pbs' => 'pbs.org',
        'phonearena' => 'phonearena.com',
        'phonearena com' => 'phonearena.com',
        'phoronix' => 'phoronix.com',
        'phys org' => 'phys.org',
        'politico' => 'politico.com',
        'politico europe' => 'politico.eu',
        'psypost' => 'psypost.org',
        'ringside news' => 'ringsidenews.com',
        'rolling stone' => 'rollingstone.com',
        'reuters' => 'reuters.com',
        'rt' => 'rt.com',
        'sciencealert' => 'sciencealert.com',
        'science daily' => 'sciencedaily.com',
        'sciencedaily' => 'sciencedaily.com',
        'scitechdaily' => 'scitechdaily.com',
        'screen rant' => 'screenrant.com',
        'slate' => 'slate.com',
        'slate france' => 'slate.fr',
        'space com' => 'space.com',
        'sports illustrated' => 'si.com',
        'san francisco chronicle' => 'sfchronicle.com',
        'sud ouest' => 'sudouest.fr',
        'techcrunch' => 'techcrunch.com',
        'thebrighterside news' => 'thebrighterside.news',
        'the brighter side of news' => 'thebrighterside.news',
        'theregister com' => 'theregister.com',
        'the register' => 'theregister.com',
        'the action network' => 'theactionnetwork.com',
        'the american conservative' => 'theamericanconservative.com',
        'the conversation africa' => 'theconversation.com/africa',
        'the conversation' => 'theconversation.com',
        'the daily galaxy great discoveries channel' => 'dailygalaxy.com',
        'futurism' => 'futurism.com',
        'the guardian' => 'theguardian.com',
        'the hill' => 'thehill.com',
        'motley fool' => 'fool.com',
        'push square' => 'pushsquare.com',
        'thestreet' => 'thestreet.com',
        'the new york times' => 'nytimes.com',
        'the telegraph' => 'telegraph.co.uk',
        'the verge' => 'theverge.com',
        'the wall street journal' => 'wsj.com',
        'the washington post' => 'washingtonpost.com',
        'the washington times' => 'washingtontimes.com',
        'time' => 'time.com',
        'tipranks com' => 'tipranks.com',
        'tipranks' => 'tipranks.com',
        'tmz' => 'tmz.com',
        'tom s guide' => 'tomsguide.com',
        'toms guide' => 'tomsguide.com',
        'telerama fr' => 'telerama.fr',
        'telerama' => 'telerama.fr',
        'tvline' => 'tvline.com',
        'tv5monde' => 'tv5monde.com',
        'usa today' => 'usatoday.com',
        'variety' => 'variety.com',
        'warhammer community com' => 'warhammer-community.com',
        'warhammer community' => 'warhammer-community.com',
        'videocardz com' => 'videocardz.com',
        'videocardz' => 'videocardz.com',
        'vox' => 'vox.com',
        'wccftech' => 'wccftech.com',
        'wired' => 'wired.com',
        'yahoo entertainment' => 'yahoo.com',
    ];

    /**
     * @return array<string, mixed>|null
     */
    public static function classify(?string $name, ?string $website, ?string $apiId = null): ?array
    {
        $basis = null;
        $profile = null;
        $host = self::hostFromWebsite($website);

        if ($host !== null) {
            foreach (self::DOMAIN_PROFILES as $domain => $candidate) {
                if ($host === $domain || str_ends_with($host, '.' . $domain)) {
                    $profile = $candidate;
                    $basis = $domain;
                    break;
                }
            }
        }

        $normalizedName = self::normalizeName($name);
        $normalizedApi = self::normalizeName(str_replace('-', ' ', (string) $apiId));
        if ($profile === null) {
            foreach ([$normalizedName, $normalizedApi] as $normalized) {
                if ($normalized !== '' && isset(self::NAME_TO_DOMAIN[$normalized])) {
                    $basis = self::NAME_TO_DOMAIN[$normalized];
                    $profile = self::DOMAIN_PROFILES[$basis] ?? null;
                    break;
                }
            }
        }

        $country = GrimbaSourceCountryBackfill::infer($name, $website, $apiId);

        if ($profile === null && $country === null) {
            return null;
        }

        $result = $profile ? [
            ...$profile,
            'bias_score' => self::biasScore($profile['bias_rating'] ?? null),
            'confidence' => 92,
            'method' => self::VERSION,
            'basis' => $basis ?: (string) ($website ?: $name ?: $apiId),
        ] : [
            'confidence' => $country['confidence'],
            'method' => 'country-only:' . $country['method'],
            'basis' => $country['basis'],
        ];

        if (! isset($result['country']) && $country !== null) {
            $result['country'] = $country['country'];
        }

        if (! isset($result['language'])) {
            $result['language'] = self::languageForCountry($result['country'] ?? null);
        }

        return $result;
    }

    public static function hasManualLock(?string $notes): bool
    {
        $notes = strtolower((string) $notes);

        return str_contains($notes, 'source-classifier:manual-lock')
            || str_contains($notes, 'manual-lock');
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

        $host = Str::lower($host);
        $host = preg_replace('/^(www|m|amp)\./', '', $host) ?: $host;

        return trim($host, '.');
    }

    private static function normalizeName(?string $name): string
    {
        $value = Str::lower(trim((string) $name));
        $value = preg_replace('/[^\pL\pN]+/u', ' ', $value) ?: '';

        return trim($value);
    }

    private static function biasScore(mixed $bias): ?float
    {
        return match ((string) $bias) {
            'left' => -1.0,
            'center' => 0.0,
            'right' => 1.0,
            default => null,
        };
    }

    private static function languageForCountry(mixed $country): ?string
    {
        return match (strtoupper((string) $country)) {
            'FR', 'BE', 'SN', 'CI', 'CM', 'MA', 'DZ' => 'fr',
            default => null,
        };
    }
}
