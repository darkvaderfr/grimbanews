<?php

namespace App\Services;

use Botble\Blog\Models\Category;
use Illuminate\Support\Facades\DB;

/*
 * S165 — keyword-based category classifier.
 *
 * Maps a post (title + description + source) onto one or two of the
 * 15 news categories seeded by GrimbaCategoriesSeeder. Pure heuristic
 * (no LLM call), runs at ingest time and inside the
 * grimba:classify-categories backfill artisan.
 *
 * Strategy: per category, a list of FR + EN trigger keywords +
 * a list of source-name shortcuts. A post matches a category when
 * any trigger keyword appears in title + description (case-insensitive,
 * diacritic-folded) OR its source has a hard-coded category mapping
 * (e.g. L'Équipe → Sports, Les Echos → Économie).
 *
 * A post can land in multiple categories (a Macron-on-climate story
 * goes in both Politique AND Climat), capped at 3 to avoid noise.
 * Every post also gets "À la une" stamped — that's the default
 * front-page category we route generic ingest to.
 */
class GrimbaCategoryClassifier
{
    private const TRIGGERS = [
        'Politique' => ['macron','elysée','assemblée','sénat','parlement','élection','élections','présidentielle','législatives','ministre','gouvernement','premier ministre','politique','parti','candidat','vote','scrutin','rn','lfi','renaissance','melenchon','ps ','ps,','les républicains','election','elections','president','minister','party','vote','election day','primary','primaries','senate','congress','parliament','prime minister','democrat','republican'],
        'Économie'  => ['économie','economy','marché','marchés','bourse','stock','wall street','cac 40','dow jones','nasdaq','inflation','récession','recession','pib','gdp','chômage','unemployment','emploi','jobs','licenciement','fusion','acquisition','startup','startups','entreprise','company','companies','business','banque','bce','fed','federal reserve','salaire','wage'],
        'Tech & Numérique' => ['intelligence artificielle','ia générative','ai ','artificial intelligence','machine learning','algorithm','algorithme','numérique','digital','data','tech ','technology','silicon valley','google','apple','meta','facebook','microsoft','amazon','nvidia','openai','chatgpt','anthropic','claude','android','ios','iphone','smartphone','startup','5g','blockchain','crypto','bitcoin','ethereum','cybersécurité','cybersecurity','hack','data breach','privacy','vie privée','cloud computing'],
        'Climat & Environnement' => ['climat','climate','réchauffement','warming','co2','émissions','emissions','effet de serre','greenhouse','biodiversité','biodiversity','espèces menacées','endangered','pollution','plastique','plastic','déforestation','deforestation','énergie renouvelable','renewable','solaire','solar','éolien','wind power','nucléaire','nuclear','pétrole','oil','gaz','gas','accord de paris','paris agreement','cop28','cop29','cop30','ipcc','giec','sécheresse','drought','inondation','flood','tempête','storm','ouragan','hurricane','typhon','typhoon'],
        'Santé'     => ['santé','health','médecin','doctor','hôpital','hospital','virus','vaccine','vaccin','épidémie','epidemic','pandemic','pandémie','covid','grippe','influenza','cancer','diabète','diabetes','obésité','obesity','dépression','depression','santé mentale','mental health','médicament','drug','clinical trial','essai clinique','sécurité sociale','medicare'],
        'Sciences'  => ['recherche scientifique','science','scientific','découverte','discovery','espace ','space','nasa','spacex','mars','satellite','planète','planet','asteroid','astéroïde','physique','physics','chimie','chemistry','biologie','biology','adn','dna','quantum','quantique','prix nobel','nobel prize','telescope','télescope','laboratoire','laboratory'],
        'Sports'    => ['football','soccer','psg','om ','om,','marseille','rugby','tennis','roland-garros','roland garros','wimbledon','open d\'australie','us open','formule 1','f1 ','formula 1','grand prix','jo ','jo,','jeux olympiques','olympics','olympic','coupe du monde','world cup','champions league','ligue 1','ligue 2','premier league','liga','bundesliga','serie a','nba','nfl','mlb','nhl','golf','cyclisme','cycling','tour de france','marathon','athlétisme','athletics','athlete','swimming','natation','basket','basketball'],
        'Culture'   => ['cinéma','cinema','film','movie','série','series','tv show','netflix','disney','hbo','prime video','musique','music','album','concert','tournée','tour ','spectacle','théâtre','theatre','theater','livre','book','roman','novel','prix littéraire','art ','art,','exposition','exhibition','musée','museum','festival','cannes','venise','venice','oscars','grammy','césar','goncourt','renaudot'],
        'Société'   => ['société','society','éducation','education','école','school','université','university','enseignant','teacher','élève','student','immigration','migrant','réfugié','refugee','famille','family','femme','women','féministe','feminist','manifestation','protest','grève','strike','syndicat','union','religion','laïcité','islam','catholic','catholique'],
        'Justice'   => ['justice','tribunal','court','procès','trial','condamn','sentenced','jugement','verdict','prison','jail','police','enquête','investigation','garde à vue','arrest','arrestation','meurtre','murder','assassinat','assassination','viol ','rape','agression','assault','attentat','attack','terrorisme','terrorism','terroriste','terrorist','escroquerie','fraud','corruption','blanchiment'],
        'Géopolitique' => ['ukraine','russie','russia','poutine','putin','iran','israël','israel','gaza','palestine','hamas','hezbollah','liban','lebanon','syrie','syria','irak','iraq','afghanistan','taliban','chine','china','xi jinping','taïwan','taiwan','corée du nord','north korea','corée du sud','south korea','venezuela','sanctions','otan','nato','onu','united nations','ue ','european union','union européenne','sommet','summit','diplomate','diplomat','ambassadeur','ambassador','traité','treaty','guerre','war','conflit','conflict'],
        'Afrique'   => ['afrique','africa','algérie','algeria','maroc','morocco','tunisie','tunisia','égypte','egypt','sénégal','senegal','côte d\'ivoire','ivory coast','mali','burkina','burkina faso','niger','tchad','chad','cameroun','cameroon','gabon','gabón','congo','rdc','drc','kenya','éthiopie','ethiopia','soudan','sudan','nigeria','ghana','afrique du sud','south africa','rwanda','tanzanie','tanzania','aes ','sahel','wagner','africorps'],
        'Monde'     => ['monde','world','international','global','foreign','étranger','asia','asie','europe','américain','american','britannique','british','allemand','german','japonais','japanese'],
        'France'    => ['france','français','française','hexagone','paris','marseille','lyon','toulouse','bordeaux','élysée','matignon','assemblée nationale'],
    ];

    /** Shortcut: NewsAPI sources whose primary category is fixed. */
    private const SOURCE_CATEGORY = [
        'L\'Équipe'     => ['Sports'],
        'Les Echos'    => ['Économie'],
        'Bloomberg'    => ['Économie'],
        'Financial Times' => ['Économie'],
        'CNBC'         => ['Économie'],
        'MarketWatch'  => ['Économie'],
        'Fortune'      => ['Économie'],
        'TechCrunch'   => ['Tech & Numérique'],
        'Wired'        => ['Tech & Numérique'],
        'The Verge'    => ['Tech & Numérique'],
        'Engadget'     => ['Tech & Numérique'],
        'Ars Technica' => ['Tech & Numérique'],
        'Hacker News'  => ['Tech & Numérique'],
        'GamesIndustry.biz' => ['Tech & Numérique'],
        'iPhoneAddict.fr' => ['Tech & Numérique'],
        'Génération NT'   => ['Tech & Numérique'],
        'Les Numériques' => ['Tech & Numérique'],
        'Journal du geek' => ['Tech & Numérique'],
        'Daily Geek Show' => ['Tech & Numérique'],
        'Futura'       => ['Sciences'],
        'National Geographic' => ['Sciences'],
        'Hollywood Reporter' => ['Culture'],
        'Deadline'     => ['Culture'],
        'PEOPLE'       => ['Culture'],
        'BBC Sport'    => ['Sports'],
        'Courrier International' => ['Monde'],
        'Al Jazeera English' => ['Géopolitique'],
        'RT'           => ['Géopolitique'],
        'Jeune Afrique' => ['Afrique'],
        'RFI Afrique'  => ['Afrique'],
        'All Africa'   => ['Afrique'],
        'Cameroon Tribune' => ['Afrique'],
        'Le Pays'      => ['Afrique'],
        'Le Soleil'    => ['Afrique'],
        'Financial Afrik' => ['Afrique'],
    ];

    /** @var array<string, int>|null  cache of name → category_id */
    private ?array $catalog = null;

    /**
     * Returns the category ids that should be attached to the post.
     * Always includes À la une (front-page bucket). Up to 3 in total.
     *
     * @return array<int, int>
     */
    public function classify(string $title, ?string $description = null, ?string $sourceName = null): array
    {
        $catalog = $this->catalog();

        $blob = mb_strtolower($title . ' ' . ($description ?? ''));
        // Diacritic fold so accented FR matches plain triggers.
        $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $blob);
        if ($ascii !== false && $ascii !== '') $blob = $blob . ' ' . mb_strtolower($ascii);

        $matches = [];

        // Source shortcut first.
        if ($sourceName && isset(self::SOURCE_CATEGORY[$sourceName])) {
            foreach (self::SOURCE_CATEGORY[$sourceName] as $catName) {
                if (isset($catalog[$catName])) $matches[$catName] = 100;
            }
        }

        // Keyword scan.
        foreach (self::TRIGGERS as $catName => $triggers) {
            if (! isset($catalog[$catName])) continue;
            foreach ($triggers as $needle) {
                if (str_contains($blob, $needle)) {
                    $matches[$catName] = ($matches[$catName] ?? 0) + 10;
                    // Don't break — count multiple hits as stronger signal.
                }
            }
        }

        // Always include À la une as the front-page default.
        if (isset($catalog['À la une'])) {
            $matches['À la une'] = max($matches['À la une'] ?? 0, 1);
        }

        // Sort by score desc, take top 3.
        arsort($matches);
        $top = array_slice($matches, 0, 3, true);

        return array_values(array_map(fn ($name) => $catalog[$name], array_keys($top)));
    }

    /** @return array<string, int>  name → id */
    private function catalog(): array
    {
        if ($this->catalog !== null) return $this->catalog;

        $rows = DB::table('categories')->get(['id', 'name']);
        $this->catalog = [];
        foreach ($rows as $r) {
            $this->catalog[$r->name] = (int) $r->id;
        }
        return $this->catalog;
    }
}
