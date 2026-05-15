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
        'aa.com.tr' => ['name' => 'Anadolu Agency', 'bias_rating' => 'right', 'ownership_type' => 'government', 'owner_name' => 'Turkish state news agency', 'credibility_score' => 56, 'country' => 'TR', 'language' => 'fr'],
        'actualite.cd' => ['name' => 'Actualite.cd', 'bias_rating' => 'center', 'ownership_type' => 'independent', 'owner_name' => 'Actualite.cd', 'credibility_score' => 68, 'country' => 'CD', 'language' => 'fr'],
        'actu17.fr' => ['name' => 'Actu17', 'bias_rating' => 'right', 'ownership_type' => 'independent', 'owner_name' => 'Actu17', 'credibility_score' => 60, 'country' => 'FR', 'language' => 'fr'],
        'afp.com' => ['name' => 'AFP', 'bias_rating' => 'center', 'ownership_type' => 'government', 'owner_name' => 'Agence France-Presse (public-private)', 'credibility_score' => 90, 'country' => 'FR', 'language' => 'fr'],
        'afd.fr' => ['name' => 'Agence Francaise de Developpement', 'bias_rating' => 'center', 'ownership_type' => 'government', 'owner_name' => 'French public development agency', 'credibility_score' => 80, 'country' => 'FR', 'language' => 'fr'],
        'agencemediapalestine.fr' => ['name' => 'Agence Media Palestine', 'bias_rating' => 'left', 'ownership_type' => 'independent', 'owner_name' => 'Agence Media Palestine', 'credibility_score' => 56, 'country' => 'FR', 'language' => 'fr'],
        'alwihdainfo.com' => ['name' => 'Alwihda Info', 'bias_rating' => 'center', 'ownership_type' => 'independent', 'owner_name' => 'Alwihda Info', 'credibility_score' => 58, 'country' => 'TD', 'language' => 'fr'],
        'aljazeera.com' => ['name' => 'Al Jazeera English', 'bias_rating' => 'center', 'ownership_type' => 'government', 'owner_name' => 'Qatar Media Corporation (state-funded)', 'credibility_score' => 74, 'country' => 'QA', 'language' => 'en'],
        'amnesty.org' => ['name' => 'Amnesty International', 'bias_rating' => 'center', 'ownership_type' => 'independent', 'owner_name' => 'Amnesty International', 'credibility_score' => 76, 'country' => 'GB', 'language' => 'en'],
        'androidpolice.com' => ['name' => 'Android Police', 'bias_rating' => 'center', 'ownership_type' => 'corporation', 'owner_name' => 'Valnet Inc.', 'credibility_score' => 70, 'country' => 'US', 'language' => 'en'],
        'apnews.com' => ['name' => 'Associated Press', 'bias_rating' => 'center', 'ownership_type' => 'independent', 'owner_name' => 'AP cooperative (member-owned)', 'credibility_score' => 92, 'country' => 'US', 'language' => 'en'],
        'actu.fr' => ['name' => 'Actu.fr', 'bias_rating' => 'center', 'ownership_type' => 'corporation', 'owner_name' => 'Publihebdos / Groupe SIPA Ouest-France', 'credibility_score' => 70, 'country' => 'FR', 'language' => 'fr'],
        'axios.com' => ['name' => 'Axios', 'bias_rating' => 'center', 'ownership_type' => 'corporation', 'owner_name' => 'Cox Enterprises', 'credibility_score' => 80, 'country' => 'US', 'language' => 'en'],
        'avoir-alire.com' => ['name' => 'aVoir-aLire.com', 'bias_rating' => 'center', 'ownership_type' => 'independent', 'owner_name' => 'aVoir-aLire', 'credibility_score' => 62, 'country' => 'FR', 'language' => 'fr'],
        '9to5google.com' => ['name' => '9to5Google', 'bias_rating' => 'center', 'ownership_type' => 'corporation', 'owner_name' => '9to5 LLC', 'credibility_score' => 72, 'country' => 'US', 'language' => 'en'],
        '9to5mac.com' => ['name' => '9to5Mac', 'bias_rating' => 'center', 'ownership_type' => 'corporation', 'owner_name' => '9to5 LLC', 'credibility_score' => 72, 'country' => 'US', 'language' => 'en'],
        'bbc.com' => ['name' => 'BBC News', 'bias_rating' => 'center', 'ownership_type' => 'government', 'owner_name' => 'BBC (British public broadcaster)', 'credibility_score' => 86, 'country' => 'GB', 'language' => 'en'],
        'bbc.co.uk' => ['name' => 'BBC News', 'bias_rating' => 'center', 'ownership_type' => 'government', 'owner_name' => 'BBC (British public broadcaster)', 'credibility_score' => 86, 'country' => 'GB', 'language' => 'en'],
        'bgr.com' => ['name' => 'BGR', 'bias_rating' => 'center', 'ownership_type' => 'corporation', 'owner_name' => 'Valnet Inc.', 'credibility_score' => 66, 'country' => 'US', 'language' => 'en'],
        'blast-info.fr' => ['name' => 'Blast', 'bias_rating' => 'left', 'ownership_type' => 'independent', 'owner_name' => 'Blast', 'credibility_score' => 68, 'country' => 'FR', 'language' => 'fr'],
        'bleepingcomputer.com' => ['name' => 'BleepingComputer', 'bias_rating' => 'center', 'ownership_type' => 'independent', 'owner_name' => 'BleepingComputer LLC', 'credibility_score' => 74, 'country' => 'US', 'language' => 'en'],
        'bloomberg.com' => ['name' => 'Bloomberg', 'bias_rating' => 'center', 'ownership_type' => 'individual', 'owner_name' => 'Bloomberg L.P. (Michael Bloomberg)', 'credibility_score' => 86, 'country' => 'US', 'language' => 'en'],
        'bloodyelbow.com' => ['name' => 'Bloody Elbow', 'bias_rating' => 'center', 'ownership_type' => 'independent', 'owner_name' => 'Bloody Elbow', 'credibility_score' => 60, 'country' => 'US', 'language' => 'en'],
        'boursier.com' => ['name' => 'Boursier.com', 'bias_rating' => 'center', 'ownership_type' => 'conglomerate', 'owner_name' => 'Les Echos-Le Parisien / LVMH', 'credibility_score' => 68, 'country' => 'FR', 'language' => 'fr'],
        'defector.com' => ['name' => 'Defector', 'bias_rating' => 'left', 'ownership_type' => 'independent', 'owner_name' => 'Defector Media LLC (employee-owned)', 'credibility_score' => 72, 'country' => 'US', 'language' => 'en'],
        'dw.com' => ['name' => 'DW (English)', 'bias_rating' => 'center', 'ownership_type' => 'government', 'owner_name' => 'Deutsche Welle (German public broadcaster)', 'credibility_score' => 82, 'country' => 'DE', 'language' => 'en'],
        'boursorama.com' => ['name' => 'Boursorama', 'bias_rating' => 'center', 'ownership_type' => 'corporation', 'owner_name' => 'Societe Generale', 'credibility_score' => 70, 'country' => 'FR', 'language' => 'fr'],
        'breitbart.com' => ['name' => 'Breitbart News', 'bias_rating' => 'right', 'ownership_type' => 'corporation', 'owner_name' => 'Breitbart News Network LLC', 'credibility_score' => 48, 'country' => 'US', 'language' => 'en'],
        'businessinsider.com' => ['name' => 'Business Insider', 'bias_rating' => 'center', 'ownership_type' => 'conglomerate', 'owner_name' => 'Axel Springer SE', 'credibility_score' => 76, 'country' => 'US', 'language' => 'en'],
        'cbc.ca' => ['name' => 'CBC News', 'bias_rating' => 'center', 'ownership_type' => 'government', 'owner_name' => 'CBC/Radio-Canada (Canadian public broadcaster)', 'credibility_score' => 84, 'country' => 'CA', 'language' => 'en'],
        'cbsnews.com' => ['name' => 'CBS News', 'bias_rating' => 'center', 'ownership_type' => 'conglomerate', 'owner_name' => 'Paramount Global', 'credibility_score' => 80, 'country' => 'US', 'language' => 'en'],
        'centrepresseaveyron.fr' => ['name' => 'Centre Presse Aveyron', 'bias_rating' => 'center', 'ownership_type' => 'individual', 'owner_name' => 'Groupe La Depeche / famille Baylet', 'credibility_score' => 70, 'country' => 'FR', 'language' => 'fr'],
        'charentelibre.fr' => ['name' => 'Charente Libre', 'bias_rating' => 'center', 'ownership_type' => 'corporation', 'owner_name' => 'Groupe Sud Ouest', 'credibility_score' => 74, 'country' => 'FR', 'language' => 'fr'],
        'cinemateaser.com' => ['name' => 'Cinemateaser', 'bias_rating' => 'center', 'ownership_type' => 'independent', 'owner_name' => 'Cinemateaser', 'credibility_score' => 60, 'country' => 'FR', 'language' => 'fr'],
        'circusdaily.com' => ['name' => 'Circus Daily', 'bias_rating' => 'center', 'ownership_type' => 'independent', 'owner_name' => 'Circus Daily', 'credibility_score' => 58, 'country' => 'US', 'language' => 'en'],
        'cleveland.com' => ['name' => 'cleveland.com', 'bias_rating' => 'center', 'ownership_type' => 'conglomerate', 'owner_name' => 'Advance Local', 'credibility_score' => 70, 'country' => 'US', 'language' => 'en'],
        'cnews.fr' => ['name' => 'CNews', 'bias_rating' => 'right', 'ownership_type' => 'conglomerate', 'owner_name' => 'Groupe Canal+ / Bollore family', 'credibility_score' => 50, 'country' => 'FR', 'language' => 'fr'],
        'cnbc.com' => ['name' => 'CNBC', 'bias_rating' => 'center', 'ownership_type' => 'telecom', 'owner_name' => 'NBCUniversal (Comcast)', 'credibility_score' => 80, 'country' => 'US', 'language' => 'en'],
        'cnn.com' => ['name' => 'CNN', 'bias_rating' => 'left', 'ownership_type' => 'conglomerate', 'owner_name' => 'Warner Bros. Discovery', 'credibility_score' => 74, 'country' => 'US', 'language' => 'en'],
        'cath.ch' => ['name' => 'cath.ch', 'bias_rating' => 'center', 'ownership_type' => 'independent', 'owner_name' => 'Centre catholique des medias cath.ch', 'credibility_score' => 66, 'country' => 'CH', 'language' => 'fr'],
        'cagesideseats.com' => ['name' => 'Cageside Seats', 'bias_rating' => 'center', 'ownership_type' => 'corporation', 'owner_name' => 'Vox Media', 'credibility_score' => 62, 'country' => 'US', 'language' => 'en'],
        'corsenetinfos.corsica' => ['name' => 'Corse Net Infos', 'bias_rating' => 'center', 'ownership_type' => 'independent', 'owner_name' => 'Corse Net Infos', 'credibility_score' => 64, 'country' => 'FR', 'language' => 'fr'],
        'corsematin.com' => ['name' => 'Corse Matin', 'bias_rating' => 'center', 'ownership_type' => 'individual', 'owner_name' => 'CMA Media / Rodolphe Saade', 'credibility_score' => 72, 'country' => 'FR', 'language' => 'fr'],
        'cult.news' => ['name' => 'Cult News', 'bias_rating' => 'center', 'ownership_type' => 'independent', 'owner_name' => 'Cult News', 'credibility_score' => 58, 'country' => 'FR', 'language' => 'fr'],
        'dailymail.co.uk' => ['name' => 'Daily Mail', 'bias_rating' => 'right', 'ownership_type' => 'conglomerate', 'owner_name' => 'Daily Mail and General Trust', 'credibility_score' => 54, 'country' => 'GB', 'language' => 'en'],
        'dailygalaxy.com' => ['name' => 'The Daily Galaxy', 'bias_rating' => 'center', 'ownership_type' => 'independent', 'owner_name' => 'The Daily Galaxy', 'credibility_score' => 62, 'country' => 'US', 'language' => 'en'],
        'dailybeast.com' => ['name' => 'The Daily Beast', 'bias_rating' => 'left', 'ownership_type' => 'corporation', 'owner_name' => 'IAC', 'credibility_score' => 66, 'country' => 'US', 'language' => 'en'],
        'dexerto.com' => ['name' => 'Dexerto', 'bias_rating' => 'center', 'ownership_type' => 'corporation', 'owner_name' => 'Dexerto Ltd.', 'credibility_score' => 62, 'country' => 'GB', 'language' => 'en'],
        'elmoudjahid.dz' => ['name' => 'El Moudjahid', 'bias_rating' => 'center', 'ownership_type' => 'government', 'owner_name' => 'Algerian public press', 'credibility_score' => 64, 'country' => 'DZ', 'language' => 'fr'],
        'epochtimes.fr' => ['name' => 'Epoch Times France', 'bias_rating' => 'right', 'ownership_type' => 'independent', 'owner_name' => 'Epoch Media Group', 'credibility_score' => 42, 'country' => 'FR', 'language' => 'fr'],
        'espn.com' => ['name' => 'ESPN', 'bias_rating' => 'center', 'ownership_type' => 'conglomerate', 'owner_name' => 'The Walt Disney Company / Hearst Communications', 'credibility_score' => 76, 'country' => 'US', 'language' => 'en'],
        'europe1.fr' => ['name' => 'Europe 1', 'bias_rating' => 'right', 'ownership_type' => 'conglomerate', 'owner_name' => 'Lagardere Radio / Vivendi', 'credibility_score' => 62, 'country' => 'FR', 'language' => 'fr'],
        'ew.com' => ['name' => 'Entertainment Weekly', 'bias_rating' => 'center', 'ownership_type' => 'corporation', 'owner_name' => 'Dotdash Meredith (IAC)', 'credibility_score' => 70, 'country' => 'US', 'language' => 'en'],
        'earth.com' => ['name' => 'Earth.com', 'bias_rating' => 'center', 'ownership_type' => 'corporation', 'owner_name' => 'Earth.com Inc.', 'credibility_score' => 70, 'country' => 'US', 'language' => 'en'],
        'energynews.pro' => ['name' => 'Energynews.pro', 'bias_rating' => 'center', 'ownership_type' => 'corporation', 'owner_name' => 'Energynews.pro', 'credibility_score' => 62, 'country' => 'FR', 'language' => 'fr'],
        'e-sante.fr' => ['name' => 'E-Sante.fr', 'bias_rating' => 'center', 'ownership_type' => 'corporation', 'owner_name' => 'E-Sante.fr', 'credibility_score' => 64, 'country' => 'FR', 'language' => 'fr'],
        'eurogamer.net' => ['name' => 'Eurogamer', 'bias_rating' => 'center', 'ownership_type' => 'corporation', 'owner_name' => 'IGN Entertainment (Ziff Davis)', 'credibility_score' => 70, 'country' => 'GB', 'language' => 'en'],
        'euronews.com' => ['name' => 'Euronews', 'bias_rating' => 'center', 'ownership_type' => 'corporation', 'owner_name' => 'Euronews SA / Alpac Capital', 'credibility_score' => 74, 'country' => 'FR', 'language' => 'fr'],
        'fabula.org' => ['name' => 'Fabula', 'bias_rating' => 'center', 'ownership_type' => 'independent', 'owner_name' => 'Association Fabula', 'credibility_score' => 68, 'country' => 'FR', 'language' => 'fr'],
        'financialafrik.com' => ['name' => 'Financial Afrik', 'bias_rating' => 'center', 'ownership_type' => 'independent', 'owner_name' => 'Financial Afrik', 'credibility_score' => 70, 'country' => 'SN', 'language' => 'fr'],
        'forbes.com' => ['name' => 'Forbes', 'bias_rating' => 'center', 'ownership_type' => 'conglomerate', 'owner_name' => 'Integrated Whale Media Investments', 'credibility_score' => 72, 'country' => 'US', 'language' => 'en'],
        'forbesafrique.com' => ['name' => 'Forbes Afrique', 'bias_rating' => 'center', 'ownership_type' => 'corporation', 'owner_name' => 'Forbes licensed local edition', 'credibility_score' => 66, 'country' => 'CG', 'language' => 'fr'],
        'foxnews.com' => ['name' => 'Fox News', 'bias_rating' => 'right', 'ownership_type' => 'conglomerate', 'owner_name' => 'Fox Corporation (Murdoch family)', 'credibility_score' => 62, 'country' => 'US', 'language' => 'en'],
        'france-palestine.org' => ['name' => 'France Palestine Solidarite', 'bias_rating' => 'left', 'ownership_type' => 'independent', 'owner_name' => 'Association France Palestine Solidarite', 'credibility_score' => 56, 'country' => 'FR', 'language' => 'fr'],
        'franceguyane.fr' => ['name' => 'France-Guyane', 'bias_rating' => 'center', 'ownership_type' => 'corporation', 'owner_name' => 'France-Guyane', 'credibility_score' => 66, 'country' => 'FR', 'language' => 'fr'],
        'france.tv' => ['name' => 'France TV', 'bias_rating' => 'center', 'ownership_type' => 'government', 'owner_name' => 'France Televisions (public broadcaster)', 'credibility_score' => 80, 'country' => 'FR', 'language' => 'fr'],
        'france24.com' => ['name' => 'France 24', 'bias_rating' => 'center', 'ownership_type' => 'government', 'owner_name' => 'France Medias Monde (state)', 'credibility_score' => 80, 'country' => 'FR', 'language' => 'fr'],
        'france3-regions.franceinfo.fr' => ['name' => 'France 3 Regions', 'bias_rating' => 'center', 'ownership_type' => 'government', 'owner_name' => 'France Televisions (public broadcaster)', 'credibility_score' => 80, 'country' => 'FR', 'language' => 'fr'],
        'franceantilles.fr' => ['name' => 'France-Antilles', 'bias_rating' => 'center', 'ownership_type' => 'corporation', 'owner_name' => 'France-Antilles', 'credibility_score' => 68, 'country' => 'FR', 'language' => 'fr'],
        'francetvinfo.fr' => ['name' => 'franceinfo', 'bias_rating' => 'center', 'ownership_type' => 'government', 'owner_name' => 'France Televisions / Radio France', 'credibility_score' => 82, 'country' => 'FR', 'language' => 'fr'],
        'frequenceprotestante.com' => ['name' => 'Frequence Protestante', 'bias_rating' => 'center', 'ownership_type' => 'independent', 'owner_name' => 'Frequence Protestante', 'credibility_score' => 62, 'country' => 'FR', 'language' => 'fr'],
        'ft.com' => ['name' => 'Financial Times', 'bias_rating' => 'center', 'ownership_type' => 'conglomerate', 'owner_name' => 'Nikkei Inc.', 'credibility_score' => 88, 'country' => 'GB', 'language' => 'en'],
        'futureplc.com' => ['name' => 'Future plc', 'bias_rating' => 'center', 'ownership_type' => 'corporation', 'owner_name' => 'Future plc', 'credibility_score' => 70, 'country' => 'GB', 'language' => 'en'],
        'game-focus.com' => ['name' => 'Game-Focus', 'bias_rating' => 'center', 'ownership_type' => 'independent', 'owner_name' => 'Game-Focus', 'credibility_score' => 58, 'country' => 'CA', 'language' => 'fr'],
        'gematsu.com' => ['name' => 'Gematsu', 'bias_rating' => 'center', 'ownership_type' => 'independent', 'owner_name' => 'Gematsu', 'credibility_score' => 68, 'country' => 'US', 'language' => 'en'],
        'geeky-gadgets.com' => ['name' => 'Geeky Gadgets', 'bias_rating' => 'center', 'ownership_type' => 'independent', 'owner_name' => 'Geeky Gadgets', 'credibility_score' => 60, 'country' => 'GB', 'language' => 'en'],
        'globalnews.ca' => ['name' => 'Global News', 'bias_rating' => 'center', 'ownership_type' => 'conglomerate', 'owner_name' => 'Corus Entertainment', 'credibility_score' => 78, 'country' => 'CA', 'language' => 'en'],
        'grv.media' => ['name' => 'GRV Media', 'bias_rating' => 'center', 'ownership_type' => 'corporation', 'owner_name' => 'GRV Media', 'credibility_score' => 62, 'country' => 'GB', 'language' => 'en'],
        'hackaday.com' => ['name' => 'Hackaday', 'bias_rating' => 'center', 'ownership_type' => 'corporation', 'owner_name' => 'Supplyframe', 'credibility_score' => 68, 'country' => 'US', 'language' => 'en'],
        'hearst.com' => ['name' => 'Hearst', 'bias_rating' => 'center', 'ownership_type' => 'conglomerate', 'owner_name' => 'Hearst Communications', 'credibility_score' => 72, 'country' => 'US', 'language' => 'en'],
        'heatworld.com' => ['name' => 'Heat', 'bias_rating' => 'center', 'ownership_type' => 'corporation', 'owner_name' => 'Bauer Media Group', 'credibility_score' => 58, 'country' => 'GB', 'language' => 'en'],
        'huffpost.com' => ['name' => 'HuffPost', 'bias_rating' => 'left', 'ownership_type' => 'corporation', 'owner_name' => 'BuzzFeed Inc.', 'credibility_score' => 66, 'country' => 'US', 'language' => 'en'],
        'humanite.fr' => ['name' => "L'Humanite", 'bias_rating' => 'left', 'ownership_type' => 'independent', 'owner_name' => "Societe Nouvelle du Journal l'Humanite", 'credibility_score' => 72, 'country' => 'FR', 'language' => 'fr'],
        'instyle.com' => ['name' => 'InStyle', 'bias_rating' => 'center', 'ownership_type' => 'corporation', 'owner_name' => 'Dotdash Meredith (IAC)', 'credibility_score' => 68, 'country' => 'US', 'language' => 'en'],
        'ici.fr' => ['name' => 'ici', 'bias_rating' => 'center', 'ownership_type' => 'government', 'owner_name' => 'Radio France / France Televisions (public)', 'credibility_score' => 78, 'country' => 'FR', 'language' => 'fr'],
        'icibeyrouth.com' => ['name' => 'Ici Beyrouth', 'bias_rating' => 'center', 'ownership_type' => 'independent', 'owner_name' => 'Ici Beyrouth', 'credibility_score' => 62, 'country' => 'LB', 'language' => 'fr'],
        'imdb.com' => ['name' => 'IMDb', 'bias_rating' => 'center', 'ownership_type' => 'corporation', 'owner_name' => 'Amazon', 'credibility_score' => 70, 'country' => 'US', 'language' => 'en'],
        'if-saint-etienne.fr' => ['name' => 'IF Saint-Etienne', 'bias_rating' => 'center', 'ownership_type' => 'independent', 'owner_name' => 'IF Saint-Etienne', 'credibility_score' => 58, 'country' => 'FR', 'language' => 'fr'],
        'indiandefencereview.com' => ['name' => 'Indian Defence Review', 'bias_rating' => 'center', 'ownership_type' => 'independent', 'owner_name' => 'Lancer Publishers', 'credibility_score' => 60, 'country' => 'IN', 'language' => 'en'],
        'insider-gaming.com' => ['name' => 'Insider Gaming', 'bias_rating' => 'center', 'ownership_type' => 'independent', 'owner_name' => 'Insider Gaming', 'credibility_score' => 62, 'country' => 'GB', 'language' => 'en'],
        'investing.com' => ['name' => 'Investing.com', 'bias_rating' => 'center', 'ownership_type' => 'corporation', 'owner_name' => 'Fusion Media', 'credibility_score' => 70, 'country' => 'IL', 'language' => 'en'],
        'jalopnik.com' => ['name' => 'Jalopnik', 'bias_rating' => 'center', 'ownership_type' => 'corporation', 'owner_name' => 'Static Media', 'credibility_score' => 62, 'country' => 'US', 'language' => 'en'],
        'kotaku.com' => ['name' => 'Kotaku', 'bias_rating' => 'center', 'ownership_type' => 'corporation', 'owner_name' => 'Keleops Media', 'credibility_score' => 66, 'country' => 'US', 'language' => 'en'],
        'journaldekinshasa.com' => ['name' => 'Journal de Kinshasa', 'bias_rating' => 'center', 'ownership_type' => 'independent', 'owner_name' => 'Journal de Kinshasa', 'credibility_score' => 62, 'country' => 'CD', 'language' => 'fr'],
        'la-croix.com' => ['name' => 'La Croix', 'bias_rating' => 'center', 'ownership_type' => 'independent', 'owner_name' => 'Bayard Presse / Assumptionists', 'credibility_score' => 78, 'country' => 'FR', 'language' => 'fr'],
        'la1ere.franceinfo.fr' => ['name' => 'Outre-mer La 1ere', 'bias_rating' => 'center', 'ownership_type' => 'government', 'owner_name' => 'France Televisions (public broadcaster)', 'credibility_score' => 78, 'country' => 'FR', 'language' => 'fr'],
        'ladepeche.fr' => ['name' => 'La Depeche', 'bias_rating' => 'center', 'ownership_type' => 'individual', 'owner_name' => 'Groupe La Depeche / famille Baylet', 'credibility_score' => 76, 'country' => 'FR', 'language' => 'fr'],
        'laradioplus.com' => ['name' => 'La Radio Plus', 'bias_rating' => 'center', 'ownership_type' => 'corporation', 'owner_name' => 'Espace Group', 'credibility_score' => 60, 'country' => 'FR', 'language' => 'fr'],
        'lamontagne.fr' => ['name' => 'La Montagne', 'bias_rating' => 'center', 'ownership_type' => 'independent', 'owner_name' => 'Groupe Centre France / Fondation Varenne', 'credibility_score' => 74, 'country' => 'FR', 'language' => 'fr'],
        'laprovence.com' => ['name' => 'La Provence', 'bias_rating' => 'center', 'ownership_type' => 'individual', 'owner_name' => 'CMA Media / Rodolphe Saade', 'credibility_score' => 72, 'country' => 'FR', 'language' => 'fr'],
        'laviesenegalaise.com' => ['name' => 'La Vie Senegalaise', 'bias_rating' => 'center', 'ownership_type' => 'independent', 'owner_name' => 'La Vie Senegalaise', 'credibility_score' => 60, 'country' => 'SN', 'language' => 'fr'],
        'latribune.fr' => ['name' => 'La Tribune', 'bias_rating' => 'center', 'ownership_type' => 'individual', 'owner_name' => 'CMA Media / Rodolphe Saade', 'credibility_score' => 74, 'country' => 'FR', 'language' => 'fr'],
        'lactualite.com' => ['name' => "L'actualite", 'bias_rating' => 'center', 'ownership_type' => 'corporation', 'owner_name' => 'Mishmash Media', 'credibility_score' => 74, 'country' => 'CA', 'language' => 'fr'],
        'lefigaro.fr' => ['name' => 'Le Figaro', 'bias_rating' => 'right', 'ownership_type' => 'conglomerate', 'owner_name' => 'Groupe Dassault', 'credibility_score' => 76, 'country' => 'FR', 'language' => 'fr'],
        'lejdd.fr' => ['name' => 'Le Journal du Dimanche', 'bias_rating' => 'right', 'ownership_type' => 'conglomerate', 'owner_name' => 'Lagardere News / Vivendi', 'credibility_score' => 58, 'country' => 'FR', 'language' => 'fr'],
        'lemonde.fr' => ['name' => 'Le Monde', 'bias_rating' => 'left', 'ownership_type' => 'individual', 'owner_name' => 'Xavier Niel + Daniel Kretinsky', 'credibility_score' => 82, 'country' => 'FR', 'language' => 'fr'],
        'letemps.ch' => ['name' => 'Le Temps', 'bias_rating' => 'center', 'ownership_type' => 'independent', 'owner_name' => 'Fondation Aventinus', 'credibility_score' => 78, 'country' => 'CH', 'language' => 'fr'],
        'levif.be' => ['name' => 'Le Vif', 'bias_rating' => 'center', 'ownership_type' => 'corporation', 'owner_name' => 'Roularta Media Group', 'credibility_score' => 74, 'country' => 'BE', 'language' => 'fr'],
        'liberation.fr' => ['name' => 'Liberation', 'bias_rating' => 'left', 'ownership_type' => 'telecom', 'owner_name' => 'Patrick Drahi (Altice)', 'credibility_score' => 74, 'country' => 'FR', 'language' => 'fr'],
        'librejournal.fr' => ['name' => 'Libre Journal', 'bias_rating' => 'right', 'ownership_type' => 'independent', 'owner_name' => 'Libre Journal', 'credibility_score' => 54, 'country' => 'FR', 'language' => 'fr'],
        'livescience.com' => ['name' => 'Live Science', 'bias_rating' => 'center', 'ownership_type' => 'corporation', 'owner_name' => 'Future plc', 'credibility_score' => 74, 'country' => 'US', 'language' => 'en'],
        'lorientlejour.com' => ['name' => "L'Orient-Le Jour", 'bias_rating' => 'center', 'ownership_type' => 'independent', 'owner_name' => "L'Orient-Le Jour SAL", 'credibility_score' => 76, 'country' => 'LB', 'language' => 'fr'],
        'macrumors.com' => ['name' => 'MacRumors', 'bias_rating' => 'center', 'ownership_type' => 'independent', 'owner_name' => 'MacRumors.com LLC', 'credibility_score' => 70, 'country' => 'US', 'language' => 'en'],
        'marianne.net' => ['name' => 'Marianne', 'bias_rating' => 'center', 'ownership_type' => 'individual', 'owner_name' => 'CMI France / Daniel Kretinsky', 'credibility_score' => 68, 'country' => 'FR', 'language' => 'fr'],
        'marqueur.com' => ['name' => 'Marqueur.com', 'bias_rating' => 'center', 'ownership_type' => 'corporation', 'owner_name' => 'Marqueur.com', 'credibility_score' => 58, 'country' => 'CA', 'language' => 'fr'],
        'mashable.com' => ['name' => 'Mashable', 'bias_rating' => 'center', 'ownership_type' => 'corporation', 'owner_name' => 'Ziff Davis', 'credibility_score' => 66, 'country' => 'US', 'language' => 'en'],
        'mediapart.fr' => ['name' => 'Mediapart', 'bias_rating' => 'left', 'ownership_type' => 'independent', 'owner_name' => 'Societe des Amis de Mediapart (employee-owned)', 'credibility_score' => 80, 'country' => 'FR', 'language' => 'fr'],
        'meteocity.com' => ['name' => 'Meteocity', 'bias_rating' => 'center', 'ownership_type' => 'conglomerate', 'owner_name' => 'Digital Prisma Players / Prisma Media', 'credibility_score' => 68, 'country' => 'FR', 'language' => 'fr'],
        'msn.com' => ['name' => 'MSN', 'bias_rating' => 'center', 'ownership_type' => 'corporation', 'owner_name' => 'Microsoft', 'credibility_score' => 60, 'country' => 'US', 'language' => 'en'],
        'msnbc.com' => ['name' => 'MSNBC', 'bias_rating' => 'left', 'ownership_type' => 'telecom', 'owner_name' => 'NBCUniversal (Comcast)', 'credibility_score' => 66, 'country' => 'US', 'language' => 'en'],
        'mlbtraderumors.com' => ['name' => 'MLB Trade Rumors', 'bias_rating' => 'center', 'ownership_type' => 'independent', 'owner_name' => 'Trade Rumors', 'credibility_score' => 72, 'country' => 'US', 'language' => 'en'],
        'motorsport.com' => ['name' => 'Motorsport.com', 'bias_rating' => 'center', 'ownership_type' => 'corporation', 'owner_name' => 'Motorsport Network', 'credibility_score' => 70, 'country' => 'US', 'language' => 'en'],
        'mynintendonews.com' => ['name' => 'My Nintendo News', 'bias_rating' => 'center', 'ownership_type' => 'independent', 'owner_name' => 'My Nintendo News', 'credibility_score' => 62, 'country' => 'GB', 'language' => 'en'],
        'nationalreview.com' => ['name' => 'National Review', 'bias_rating' => 'right', 'ownership_type' => 'independent', 'owner_name' => 'National Review Inc.', 'credibility_score' => 66, 'country' => 'US', 'language' => 'en'],
        'nantes-infos.fr' => ['name' => 'nantes-infos.fr', 'bias_rating' => 'center', 'ownership_type' => 'independent', 'owner_name' => 'nantes-infos.fr', 'credibility_score' => 56, 'country' => 'FR', 'language' => 'fr'],
        'nbcnews.com' => ['name' => 'NBC News', 'bias_rating' => 'center', 'ownership_type' => 'telecom', 'owner_name' => 'NBCUniversal (Comcast)', 'credibility_score' => 80, 'country' => 'US', 'language' => 'en'],
        'nbcsports.com' => ['name' => 'NBC Sports', 'bias_rating' => 'center', 'ownership_type' => 'telecom', 'owner_name' => 'NBCUniversal (Comcast)', 'credibility_score' => 78, 'country' => 'US', 'language' => 'en'],
        'newsweek.com' => ['name' => 'Newsweek', 'bias_rating' => 'center', 'ownership_type' => 'corporation', 'owner_name' => 'IBT Media', 'credibility_score' => 64, 'country' => 'US', 'language' => 'en'],
        'newsnationnow.com' => ['name' => 'NewsNation', 'bias_rating' => 'center', 'ownership_type' => 'conglomerate', 'owner_name' => 'Nexstar Media Group', 'credibility_score' => 66, 'country' => 'US', 'language' => 'en'],
        'news-eco.com' => ['name' => 'news-eco.com', 'bias_rating' => 'center', 'ownership_type' => 'corporation', 'owner_name' => 'news-eco.com', 'credibility_score' => 55, 'country' => 'FR', 'language' => 'fr'],
        'nhl.com' => ['name' => 'NHL News', 'bias_rating' => 'center', 'ownership_type' => 'corporation', 'owner_name' => 'National Hockey League', 'credibility_score' => 70, 'country' => 'US', 'language' => 'en'],
        'nintendolife.com' => ['name' => 'Nintendo Life', 'bias_rating' => 'center', 'ownership_type' => 'corporation', 'owner_name' => 'Hookshot Media', 'credibility_score' => 68, 'country' => 'GB', 'language' => 'en'],
        'npr.org' => ['name' => 'NPR', 'bias_rating' => 'left', 'ownership_type' => 'independent', 'owner_name' => 'National Public Radio', 'credibility_score' => 84, 'country' => 'US', 'language' => 'en'],
        'nypost.com' => ['name' => 'New York Post', 'bias_rating' => 'right', 'ownership_type' => 'conglomerate', 'owner_name' => 'News Corp (Murdoch family)', 'credibility_score' => 56, 'country' => 'US', 'language' => 'en'],
        'nytimes.com' => ['name' => 'The New York Times', 'bias_rating' => 'left', 'ownership_type' => 'corporation', 'owner_name' => 'The New York Times Company', 'credibility_score' => 84, 'country' => 'US', 'language' => 'en'],
        'opinion-internationale.com' => ['name' => "L'Opinion Independante", 'bias_rating' => 'center', 'ownership_type' => 'independent', 'owner_name' => "L'Opinion Independante", 'credibility_score' => 62, 'country' => 'FR', 'language' => 'fr'],
        'opex360.com' => ['name' => 'Zone Militaire', 'bias_rating' => 'center', 'ownership_type' => 'independent', 'owner_name' => 'Zone Militaire / Opex360', 'credibility_score' => 70, 'country' => 'FR', 'language' => 'fr'],
        'orange.fr' => ['name' => 'Orange Actualites', 'bias_rating' => 'center', 'ownership_type' => 'telecom', 'owner_name' => 'Orange S.A.', 'credibility_score' => 64, 'country' => 'FR', 'language' => 'fr'],
        'orthodoxie.com' => ['name' => 'Orthodoxie.com', 'bias_rating' => 'center', 'ownership_type' => 'independent', 'owner_name' => 'Orthodoxie.com', 'credibility_score' => 60, 'country' => 'FR', 'language' => 'fr'],
        'ouest-france.fr' => ['name' => 'Ouest-France', 'bias_rating' => 'center', 'ownership_type' => 'independent', 'owner_name' => 'SIPA Ouest-France / Association pour le soutien des principes de la democratie humaniste', 'credibility_score' => 80, 'country' => 'FR', 'language' => 'fr'],
        'pbs.org' => ['name' => 'PBS', 'bias_rating' => 'center', 'ownership_type' => 'government', 'owner_name' => 'Public Broadcasting Service', 'credibility_score' => 84, 'country' => 'US', 'language' => 'en'],
        'phonearena.com' => ['name' => 'PhoneArena', 'bias_rating' => 'center', 'ownership_type' => 'corporation', 'owner_name' => 'PhoneArena', 'credibility_score' => 64, 'country' => 'US', 'language' => 'en'],
        'phoronix.com' => ['name' => 'Phoronix', 'bias_rating' => 'center', 'ownership_type' => 'independent', 'owner_name' => 'Phoronix Media', 'credibility_score' => 72, 'country' => 'US', 'language' => 'en'],
        'phys.org' => ['name' => 'Phys.Org', 'bias_rating' => 'center', 'ownership_type' => 'corporation', 'owner_name' => 'Science X Network', 'credibility_score' => 78, 'country' => 'IM', 'language' => 'en'],
        'planet.fr' => ['name' => 'Planet.fr', 'bias_rating' => 'center', 'ownership_type' => 'conglomerate', 'owner_name' => 'Prisma Media', 'credibility_score' => 60, 'country' => 'FR', 'language' => 'fr'],
        'pleinevie.fr' => ['name' => 'Pleine Vie', 'bias_rating' => 'center', 'ownership_type' => 'corporation', 'owner_name' => 'Reworld Media', 'credibility_score' => 58, 'country' => 'FR', 'language' => 'fr'],
        'politico.com' => ['name' => 'Politico', 'bias_rating' => 'center', 'ownership_type' => 'conglomerate', 'owner_name' => 'Axel Springer SE', 'credibility_score' => 80, 'country' => 'US', 'language' => 'en'],
        'politico.eu' => ['name' => 'Politico Europe', 'bias_rating' => 'center', 'ownership_type' => 'conglomerate', 'owner_name' => 'Axel Springer SE', 'credibility_score' => 80, 'country' => 'BE', 'language' => 'en'],
        'psypost.org' => ['name' => 'PsyPost', 'bias_rating' => 'center', 'ownership_type' => 'independent', 'owner_name' => 'PsyPost', 'credibility_score' => 66, 'country' => 'US', 'language' => 'en'],
        'radio-canada.ca' => ['name' => 'Radio-Canada', 'bias_rating' => 'center', 'ownership_type' => 'government', 'owner_name' => 'CBC/Radio-Canada (Canadian public broadcaster)', 'credibility_score' => 84, 'country' => 'CA', 'language' => 'fr'],
        'reuters.com' => ['name' => 'Reuters', 'bias_rating' => 'center', 'ownership_type' => 'corporation', 'owner_name' => 'Thomson Reuters', 'credibility_score' => 92, 'country' => 'GB', 'language' => 'en'],
        'ringsidenews.com' => ['name' => 'Ringside News', 'bias_rating' => 'center', 'ownership_type' => 'independent', 'owner_name' => 'Ringside News', 'credibility_score' => 54, 'country' => 'US', 'language' => 'en'],
        'rmcsport.bfmtv.com' => ['name' => 'RMC Sport', 'bias_rating' => 'center', 'ownership_type' => 'telecom', 'owner_name' => 'Altice France', 'credibility_score' => 66, 'country' => 'FR', 'language' => 'fr'],
        'rollingstone.com' => ['name' => 'Rolling Stone', 'bias_rating' => 'left', 'ownership_type' => 'corporation', 'owner_name' => 'Penske Media Corporation', 'credibility_score' => 68, 'country' => 'US', 'language' => 'en'],
        'rtl.fr' => ['name' => 'RTL.fr', 'bias_rating' => 'center', 'ownership_type' => 'conglomerate', 'owner_name' => 'Groupe M6 / RTL Group', 'credibility_score' => 74, 'country' => 'FR', 'language' => 'fr'],
        'rt.com' => ['name' => 'RT', 'bias_rating' => 'right', 'ownership_type' => 'government', 'owner_name' => 'TV-Novosti (Russian government-funded)', 'credibility_score' => 35, 'country' => 'RU', 'language' => 'en'],
        'sciencealert.com' => ['name' => 'ScienceAlert', 'bias_rating' => 'center', 'ownership_type' => 'independent', 'owner_name' => 'ScienceAlert Pty Ltd', 'credibility_score' => 76, 'country' => 'AU', 'language' => 'en'],
        'sciencedaily.com' => ['name' => 'Science Daily', 'bias_rating' => 'center', 'ownership_type' => 'corporation', 'owner_name' => 'ScienceDaily LLC', 'credibility_score' => 78, 'country' => 'US', 'language' => 'en'],
        'sciencesetavenir.fr' => ['name' => 'Sciences et Avenir', 'bias_rating' => 'center', 'ownership_type' => 'conglomerate', 'owner_name' => 'LVMH / Groupe Perdriel transition', 'credibility_score' => 76, 'country' => 'FR', 'language' => 'fr'],
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
        'tf1info.fr' => ['name' => 'TF1 Info', 'bias_rating' => 'center', 'ownership_type' => 'conglomerate', 'owner_name' => 'Groupe TF1 / Bouygues', 'credibility_score' => 70, 'country' => 'FR', 'language' => 'fr'],
        'touteleurope.eu' => ['name' => 'Touteleurope.eu', 'bias_rating' => 'center', 'ownership_type' => 'independent', 'owner_name' => "Centre d'information sur l'Europe", 'credibility_score' => 76, 'country' => 'FR', 'language' => 'fr'],
        'tradersunion.com' => ['name' => 'Traders Union', 'bias_rating' => 'center', 'ownership_type' => 'corporation', 'owner_name' => 'Traders Union', 'credibility_score' => 55, 'country' => 'CY', 'language' => 'en'],
        'tradingview.com' => ['name' => 'TradingView', 'bias_rating' => 'center', 'ownership_type' => 'corporation', 'owner_name' => 'TradingView Inc.', 'credibility_score' => 66, 'country' => 'US', 'language' => 'fr'],
        'trtfrancais.com' => ['name' => 'TRT Francais', 'bias_rating' => 'right', 'ownership_type' => 'government', 'owner_name' => 'Turkish Radio and Television Corporation', 'credibility_score' => 52, 'country' => 'TR', 'language' => 'fr'],
        'telerama.fr' => ['name' => 'Telerama', 'bias_rating' => 'center', 'ownership_type' => 'conglomerate', 'owner_name' => 'Groupe Le Monde', 'credibility_score' => 74, 'country' => 'FR', 'language' => 'fr'],
        'temoignages.re' => ['name' => 'Temoignages.RE', 'bias_rating' => 'left', 'ownership_type' => 'independent', 'owner_name' => 'Temoignages', 'credibility_score' => 62, 'country' => 'FR', 'language' => 'fr'],
        'tvline.com' => ['name' => 'TVLine', 'bias_rating' => 'center', 'ownership_type' => 'corporation', 'owner_name' => 'Penske Media Corporation', 'credibility_score' => 68, 'country' => 'US', 'language' => 'en'],
        'tv5monde.com' => ['name' => 'TV5MONDE', 'bias_rating' => 'center', 'ownership_type' => 'government', 'owner_name' => 'TV5MONDE public media consortium', 'credibility_score' => 78, 'country' => 'FR', 'language' => 'fr'],
        'umontreal.ca' => ['name' => 'Universite de Montreal', 'bias_rating' => 'center', 'ownership_type' => 'government', 'owner_name' => 'Universite de Montreal', 'credibility_score' => 82, 'country' => 'CA', 'language' => 'fr'],
        'un.org' => ['name' => 'United Nations', 'bias_rating' => 'center', 'ownership_type' => 'government', 'owner_name' => 'United Nations', 'credibility_score' => 82, 'country' => 'US', 'language' => 'en'],
        'usatoday.com' => ['name' => 'USA Today', 'bias_rating' => 'center', 'ownership_type' => 'private_equity', 'owner_name' => 'Gannett Co.', 'credibility_score' => 76, 'country' => 'US', 'language' => 'en'],
        'vox.com' => ['name' => 'Vox', 'bias_rating' => 'left', 'ownership_type' => 'corporation', 'owner_name' => 'Vox Media', 'credibility_score' => 72, 'country' => 'US', 'language' => 'en'],
        'variety.com' => ['name' => 'Variety', 'bias_rating' => 'center', 'ownership_type' => 'corporation', 'owner_name' => 'Penske Media Corporation', 'credibility_score' => 76, 'country' => 'US', 'language' => 'en'],
        'warhammer-community.com' => ['name' => 'Warhammer Community', 'bias_rating' => 'center', 'ownership_type' => 'corporation', 'owner_name' => 'Games Workshop', 'credibility_score' => 62, 'country' => 'GB', 'language' => 'en'],
        'videocardz.com' => ['name' => 'VideoCardz', 'bias_rating' => 'center', 'ownership_type' => 'independent', 'owner_name' => 'VideoCardz', 'credibility_score' => 64, 'country' => 'PL', 'language' => 'en'],
        'washingtonpost.com' => ['name' => 'The Washington Post', 'bias_rating' => 'left', 'ownership_type' => 'individual', 'owner_name' => 'Nash Holdings (Jeff Bezos)', 'credibility_score' => 82, 'country' => 'US', 'language' => 'en'],
        'washingtontimes.com' => ['name' => 'The Washington Times', 'bias_rating' => 'right', 'ownership_type' => 'corporation', 'owner_name' => 'Operations Holdings (Unification Church)', 'credibility_score' => 56, 'country' => 'US', 'language' => 'en'],
        'wccftech.com' => ['name' => 'Wccftech', 'bias_rating' => 'center', 'ownership_type' => 'independent', 'owner_name' => 'Wccftech', 'credibility_score' => 58, 'country' => 'PK', 'language' => 'en'],
        'webmanagercenter.com' => ['name' => 'Webmanagercenter', 'bias_rating' => 'center', 'ownership_type' => 'corporation', 'owner_name' => 'Webmanagercenter', 'credibility_score' => 62, 'country' => 'TN', 'language' => 'fr'],
        'wired.com' => ['name' => 'Wired', 'bias_rating' => 'center', 'ownership_type' => 'corporation', 'owner_name' => 'Conde Nast (Advance Publications)', 'credibility_score' => 76, 'country' => 'US', 'language' => 'en'],
        'woopets.fr' => ['name' => 'Woopets', 'bias_rating' => 'center', 'ownership_type' => 'corporation', 'owner_name' => 'Woopets', 'credibility_score' => 58, 'country' => 'FR', 'language' => 'fr'],
        'wsj.com' => ['name' => 'The Wall Street Journal', 'bias_rating' => 'right', 'ownership_type' => 'conglomerate', 'owner_name' => 'News Corp (Murdoch family)', 'credibility_score' => 84, 'country' => 'US', 'language' => 'en'],
        'yahoo.com' => ['name' => 'Yahoo', 'bias_rating' => 'center', 'ownership_type' => 'private_equity', 'owner_name' => 'Yahoo Inc. (Apollo Global Management)', 'credibility_score' => 68, 'country' => 'US', 'language' => 'en'],
        'zonebourse.com' => ['name' => 'Zonebourse Suisse', 'bias_rating' => 'center', 'ownership_type' => 'corporation', 'owner_name' => 'Surperformance', 'credibility_score' => 70, 'country' => 'CH', 'language' => 'fr'],
    ];

    /**
     * @var array<string, string>
     */
    private const NAME_TO_DOMAIN = [
        'abc news' => 'abcnews.go.com',
        'abc news au' => 'abc.net.au',
        'anadolu ajansi' => 'aa.com.tr',
        'anadolu ajansı' => 'aa.com.tr',
        'actualite cd' => 'actualite.cd',
        'actu17' => 'actu17.fr',
        'afp' => 'afp.com',
        'afd agence francaise de developpement' => 'afd.fr',
        'afd agence française de développement' => 'afd.fr',
        'agence media palestine' => 'agencemediapalestine.fr',
        'al jazeera english' => 'aljazeera.com',
        'actu fr' => 'actu.fr',
        'actu' => 'actu.fr',
        'alwihda info' => 'alwihdainfo.com',
        'amnesty international' => 'amnesty.org',
        'android police' => 'androidpolice.com',
        'associated press' => 'apnews.com',
        'ap' => 'apnews.com',
        'axios' => 'axios.com',
        'a voir a lire' => 'avoir-alire.com',
        'avoir alire' => 'avoir-alire.com',
        'avoir alire com' => 'avoir-alire.com',
        '9to5google com' => '9to5google.com',
        '9to5google' => '9to5google.com',
        '9to5mac' => '9to5mac.com',
        'bbc' => 'bbc.com',
        'bbc news' => 'bbc.com',
        'bgr' => 'bgr.com',
        'blast' => 'blast-info.fr',
        'blast info' => 'blast-info.fr',
        'bleepingcomputer' => 'bleepingcomputer.com',
        'bleeping computer' => 'bleepingcomputer.com',
        'bloody elbow' => 'bloodyelbow.com',
        'bloomberg' => 'bloomberg.com',
        'boursier com' => 'boursier.com',
        'boursorama' => 'boursorama.com',
        'breitbart' => 'breitbart.com',
        'breitbart news' => 'breitbart.com',
        'business insider' => 'businessinsider.com',
        'cbc news' => 'cbc.ca',
        'cbs news' => 'cbsnews.com',
        'centre presse aveyron' => 'centrepresseaveyron.fr',
        'charente libre' => 'charentelibre.fr',
        'cinemateaser' => 'cinemateaser.com',
        'circus daily' => 'circusdaily.com',
        'cleveland com' => 'cleveland.com',
        'cnews' => 'cnews.fr',
        'cnbc' => 'cnbc.com',
        'cnn' => 'cnn.com',
        'cath ch' => 'cath.ch',
        'cageside seats' => 'cagesideseats.com',
        'corse net infos' => 'corsenetinfos.corsica',
        'corse matin' => 'corsematin.com',
        'cult news' => 'cult.news',
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
        'el moudjahid' => 'elmoudjahid.dz',
        'entertainment weekly' => 'ew.com',
        'espn' => 'espn.com',
        'epoch times france' => 'epochtimes.fr',
        'epochtimes fr' => 'epochtimes.fr',
        'europe 1' => 'europe1.fr',
        'earth com' => 'earth.com',
        'energynews pro' => 'energynews.pro',
        'e sante fr' => 'e-sante.fr',
        'e santé fr' => 'e-sante.fr',
        'eurogamer net' => 'eurogamer.net',
        'eurogamer' => 'eurogamer.net',
        'euronews' => 'euronews.com',
        'euronews com' => 'euronews.com',
        'fabula' => 'fabula.org',
        'fabula la recherche en litterature' => 'fabula.org',
        'fabula la recherche en littérature' => 'fabula.org',
        'financial afrik' => 'financialafrik.com',
        'financial times' => 'ft.com',
        'forbes' => 'forbes.com',
        'forbes afrique' => 'forbesafrique.com',
        'fox news' => 'foxnews.com',
        'france palestine solidarite' => 'france-palestine.org',
        'france palestine solidarité' => 'france-palestine.org',
        'franceguyane fr' => 'franceguyane.fr',
        'france 3 regions' => 'france3-regions.franceinfo.fr',
        'france 3 régions' => 'france3-regions.franceinfo.fr',
        'france 24' => 'france24.com',
        'france tv' => 'france.tv',
        'france antilles guadeloupe' => 'franceantilles.fr',
        'franceinfo' => 'francetvinfo.fr',
        'frequence protestante' => 'frequenceprotestante.com',
        'fréquence protestante' => 'frequenceprotestante.com',
        'game focus' => 'game-focus.com',
        'game focus com' => 'game-focus.com',
        'gematsu' => 'gematsu.com',
        'geeky gadgets' => 'geeky-gadgets.com',
        'global news' => 'globalnews.ca',
        'heat com' => 'heatworld.com',
        'heat' => 'heatworld.com',
        'hackaday' => 'hackaday.com',
        'hitc football gaming movies tv music' => 'grv.media',
        'hitc' => 'grv.media',
        'huffpost' => 'huffpost.com',
        'humanite' => 'humanite.fr',
        'humanité' => 'humanite.fr',
        'ici' => 'ici.fr',
        'ici fr' => 'ici.fr',
        'ici beyrouth' => 'icibeyrouth.com',
        'imdb' => 'imdb.com',
        'if saint etienne' => 'if-saint-etienne.fr',
        'instyle' => 'instyle.com',
        'indian defence review' => 'indiandefencereview.com',
        'indiandefencereview com' => 'indiandefencereview.com',
        'insider gaming' => 'insider-gaming.com',
        'insider gaming com' => 'insider-gaming.com',
        'investing com france' => 'investing.com',
        'investing france' => 'investing.com',
        'jalopnik' => 'jalopnik.com',
        'journal de kinshasa' => 'journaldekinshasa.com',
        'kotaku' => 'kotaku.com',
        'la croix' => 'la-croix.com',
        'la montagne' => 'lamontagne.fr',
        'la provence' => 'laprovence.com',
        'la radio plus' => 'laradioplus.com',
        'la vie senegalaise' => 'laviesenegalaise.com',
        'la tribune' => 'latribune.fr',
        'ladepeche fr' => 'ladepeche.fr',
        'la depeche' => 'ladepeche.fr',
        'l actualite' => 'lactualite.com',
        'l actualité' => 'lactualite.com',
        'le figaro' => 'lefigaro.fr',
        'le jdd' => 'lejdd.fr',
        'le journal du dimanche' => 'lejdd.fr',
        'le monde' => 'lemonde.fr',
        'le temps' => 'letemps.ch',
        'le vif' => 'levif.be',
        'lejdd' => 'lejdd.fr',
        'lejdd fr' => 'lejdd.fr',
        'live science' => 'livescience.com',
        'liberation' => 'liberation.fr',
        'libre journal' => 'librejournal.fr',
        'l humanite' => 'humanite.fr',
        'l humanité' => 'humanite.fr',
        'l orient le jour' => 'lorientlejour.com',
        'macrumors' => 'macrumors.com',
        'marianne' => 'marianne.net',
        'marqueur com' => 'marqueur.com',
        'mashable' => 'mashable.com',
        'mediapart' => 'mediapart.fr',
        'meteo city' => 'meteocity.com',
        'meteocity' => 'meteocity.com',
        'météocity' => 'meteocity.com',
        'msn' => 'msn.com',
        'msnbc' => 'msnbc.com',
        'mlb trade rumors' => 'mlbtraderumors.com',
        'motorsport com' => 'motorsport.com',
        'motorsport' => 'motorsport.com',
        'my nintendo news' => 'mynintendonews.com',
        'nantes infos fr' => 'nantes-infos.fr',
        'national review' => 'nationalreview.com',
        'nbc news' => 'nbcnews.com',
        'nbcsports com' => 'nbcsports.com',
        'nbc sports' => 'nbcsports.com',
        'newsweek' => 'newsweek.com',
        'new york post' => 'nypost.com',
        'newsnationnow com' => 'newsnationnow.com',
        'newsnation' => 'newsnationnow.com',
        'news eco com' => 'news-eco.com',
        'nhl news' => 'nhl.com',
        'nhl' => 'nhl.com',
        'nintendo life' => 'nintendolife.com',
        'npr' => 'npr.org',
        'orange actualites' => 'orange.fr',
        'orange actualités' => 'orange.fr',
        'orthodoxie com' => 'orthodoxie.com',
        'l opinion independante' => 'opinion-internationale.com',
        'l opinion indépendante' => 'opinion-internationale.com',
        'outre mer la 1ere' => 'la1ere.franceinfo.fr',
        'outre mer la 1ère' => 'la1ere.franceinfo.fr',
        'zone militaire' => 'opex360.com',
        'ouest france' => 'ouest-france.fr',
        'pbs' => 'pbs.org',
        'phonearena' => 'phonearena.com',
        'phonearena com' => 'phonearena.com',
        'phoronix' => 'phoronix.com',
        'phys org' => 'phys.org',
        'planet' => 'planet.fr',
        'planet fr' => 'planet.fr',
        'pleine vie' => 'pleinevie.fr',
        'politico' => 'politico.com',
        'politico europe' => 'politico.eu',
        'psypost' => 'psypost.org',
        'radio canada' => 'radio-canada.ca',
        'ringside news' => 'ringsidenews.com',
        'rolling stone' => 'rollingstone.com',
        'reuters' => 'reuters.com',
        'rmc sport' => 'rmcsport.bfmtv.com',
        'rt' => 'rt.com',
        'rtl' => 'rtl.fr',
        'rtl fr' => 'rtl.fr',
        'sciencealert' => 'sciencealert.com',
        'science daily' => 'sciencedaily.com',
        'sciencedaily' => 'sciencedaily.com',
        'sciences et avenir' => 'sciencesetavenir.fr',
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
        'tf1 info' => 'tf1info.fr',
        'time' => 'time.com',
        'tipranks com' => 'tipranks.com',
        'tipranks' => 'tipranks.com',
        'tmz' => 'tmz.com',
        'tom s guide' => 'tomsguide.com',
        'toms guide' => 'tomsguide.com',
        'temoignages re' => 'temoignages.re',
        'témoignages re' => 'temoignages.re',
        'touteleurope eu' => 'touteleurope.eu',
        'traders union' => 'tradersunion.com',
        'tradingview' => 'tradingview.com',
        'tradingview france' => 'tradingview.com',
        'trt francais' => 'trtfrancais.com',
        'trt français' => 'trtfrancais.com',
        'telerama fr' => 'telerama.fr',
        'telerama' => 'telerama.fr',
        'tvline' => 'tvline.com',
        'tv5monde' => 'tv5monde.com',
        'universite de montreal' => 'umontreal.ca',
        'université de montréal' => 'umontreal.ca',
        'welcome to the united nations' => 'un.org',
        'united nations' => 'un.org',
        'usa today' => 'usatoday.com',
        'variety' => 'variety.com',
        'warhammer community com' => 'warhammer-community.com',
        'warhammer community' => 'warhammer-community.com',
        'videocardz com' => 'videocardz.com',
        'videocardz' => 'videocardz.com',
        'vox' => 'vox.com',
        'wccftech' => 'wccftech.com',
        'webmanagercenter' => 'webmanagercenter.com',
        'wired' => 'wired.com',
        'woopets' => 'woopets.fr',
        'yahoo entertainment' => 'yahoo.com',
        'zonebourse suisse' => 'zonebourse.com',
        'zonebourse' => 'zonebourse.com',
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
