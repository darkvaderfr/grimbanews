<?php

namespace App\Services;

use App\Ground\Regions;
use App\Support\GrimbaEditorialCategories;
use Illuminate\Support\Facades\DB;

/*
 * S165/S007 — edition classifier.
 *
 * Maps a post (title + description + source) onto one edition category
 * plus one topical news category. Pure heuristic (no LLM call), runs
 * at ingest time and inside the grimba:classify-categories backfill
 * artisan.
 */
class GrimbaCategoryClassifier
{
    private const AFRICA_TRIGGERS = [
        'Afrique'   => ['afrique','africa','algérie','algeria','maroc','morocco','tunisie','tunisia','égypte','egypt','sénégal','senegal','côte d\'ivoire','ivory coast','mali','burkina','burkina faso','niger','tchad','chad','cameroun','cameroon','gabon','gabón','congo','rdc','drc','kenya','éthiopie','ethiopia','soudan','sudan','nigeria','ghana','afrique du sud','south africa','rwanda','tanzanie','tanzania','aes ','sahel','wagner','africorps'],
    ];

    private const TOPIC_TRIGGERS = [
        'Politique' => ['election','élection','president','président','gouvernement','government','parlement','parliament','ministre','minister','senat','sénat','parti politique','campaign','campagne','vote','ballot'],
        'Économie' => ['économie','economie','economic','economy','business','market','marché','inflation','budget','taxe','tax','tariff','tarif','commerce','trade','emploi','jobs','finance','banque','bank','bourse','stock'],
        'Géopolitique' => ['guerre','war','ukraine','russie','russia','gaza','israel','israël','iran','militaire','military','armée','army','defense','défense','security','sécurité','sanction','nato','otan','ceasefire','cessez-le-feu'],
        'Société' => ['société','societe','education','éducation','school','école','migration','immigration','famille','family','logement','housing','protest','manifestation','student','étudiant','travailleur','worker'],
        'Justice' => ['justice','court','tribunal','procès','proces','trial','judge','juge','police','enquête','investigation','crime','lawsuit','plainte','arrestation','prison','droits','rights'],
        'Tech & Numérique' => ['tech','technology','technologie','numérique','numerique','digital','ai ','ia ','artificial intelligence','intelligence artificielle','cyber','software','logiciel','startup','platform','plateforme','data','données'],
        'Climat & Environnement' => ['climat','climate','environment','environnement','énergie','energie','energy','oil','pétrole','petrole','gas','gaz','biodiversity','biodiversité','pollution','flood','inondation','drought','sécheresse','secheresse'],
        'Santé' => ['santé','sante','health','hospital','hôpital','hopital','medical','médical','doctor','médecin','medicine','vaccin','vaccine','covid','disease','maladie','pharma','soins'],
        'Sciences' => ['science','scientific','scientifique','space','espace','nasa','research','recherche','study','étude','etude','university','université','universite','physics','physique','archaeology','archéologie'],
        'Sports' => ['sport','football','soccer','nba','basket','tennis','rugby','can-2027','world cup','coupe du monde','olympic','olympique','athlete','athlète','match','club'],
        'Culture' => ['culture','cinema','cinéma','film','music','musique','book','livre','festival','art','artist','artiste','museum','musée','musee','media','média','television','télévision'],
        'Monde' => ['international','world','monde','global','diplomacy','diplomatie','united nations','nations unies','onu','summit','sommet','foreign','étranger','etranger'],
    ];

    /** Shortcut: NewsAPI sources whose primary category is fixed. */
    private const SOURCE_CATEGORY = [
        'Jeune Afrique' => 'Afrique',
        'RFI Afrique'  => 'Afrique',
        'All Africa'   => 'Afrique',
        'Cameroon Tribune' => 'Afrique',
        'Le Pays'      => 'Afrique',
        'Le Soleil'    => 'Afrique',
        'Financial Afrik' => 'Afrique',
    ];

    /** @var array<string, int>|null  cache of name → category_id */
    private ?array $catalog = null;

    /**
     * Returns the category ids that should be attached to the post.
     *
     * @return array<int, int>
     */
    public function classify(string $title, ?string $description = null, ?string $sourceName = null, ?string $sourceCountry = null): array
    {
        $catalog = $this->catalog();
        $sourceCountry = $sourceCountry ?: $this->sourceCountry($sourceName);

        $blob = mb_strtolower($title . ' ' . ($description ?? ''));
        // Diacritic fold so accented FR matches plain triggers.
        $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $blob);
        if ($ascii !== false && $ascii !== '') $blob = $blob . ' ' . mb_strtolower($ascii);

        $categoryNames = [];

        $editionName = $this->editionName($sourceCountry, $sourceName, $blob);
        if (isset($catalog[$editionName])) {
            $categoryNames[] = $editionName;
        }

        $topicName = $this->topicName($blob, $catalog);
        if (isset($catalog[$topicName])) {
            $categoryNames[] = $topicName;
        } elseif (isset($catalog['À la une'])) {
            $categoryNames[] = 'À la une';
        }

        return collect($categoryNames)
            ->unique()
            ->map(fn (string $name): int => $catalog[$name])
            ->values()
            ->all();
    }

    private function editionName(?string $country, ?string $sourceName, string $blob): string
    {
        if ($sourceName && isset(self::SOURCE_CATEGORY[$sourceName])) {
            return self::SOURCE_CATEGORY[$sourceName];
        }

        $country = mb_strtoupper((string) $country);
        if ($country !== '') {
            if (in_array($country, Regions::AFRICA, true)) {
                return 'Afrique';
            }
            if (in_array($country, Regions::EUROPE, true)) {
                return 'Europe';
            }
            if (in_array($country, Regions::AMERICAS, true)) {
                return 'Amériques';
            }
        }

        foreach (self::AFRICA_TRIGGERS['Afrique'] as $needle) {
            if (str_contains($blob, $needle)) {
                return 'Afrique';
            }
        }

        return 'International';
    }

    /**
     * @param array<string, int> $catalog
     */
    private function topicName(string $blob, array $catalog): string
    {
        $matches = [];

        foreach (self::TOPIC_TRIGGERS as $catName => $triggers) {
            if (! isset($catalog[$catName])) continue;
            foreach ($triggers as $needle) {
                if (str_contains($blob, $needle)) {
                    $matches[$catName] = ($matches[$catName] ?? 0) + 10;
                    // Don't break — count multiple hits as stronger signal.
                }
            }
        }

        if ($matches === []) {
            return 'À la une';
        }

        arsort($matches);

        return (string) array_key_first($matches);
    }

    private function sourceCountry(?string $sourceName): ?string
    {
        if (! $sourceName) {
            return null;
        }

        static $cache = [];
        if (array_key_exists($sourceName, $cache)) {
            return $cache[$sourceName];
        }

        $cache[$sourceName] = DB::table('news_sources')
            ->where('name', $sourceName)
            ->value('country');

        return $cache[$sourceName];
    }

    /** @return array<string, int>  name → id */
    private function catalog(): array
    {
        if ($this->catalog !== null) return $this->catalog;

        $knownNames = array_merge(
            GrimbaEditorialCategories::editionNames(),
            GrimbaEditorialCategories::topicNames()
        );

        $rows = DB::table('categories')
            ->whereIn('name', $knownNames)
            ->get(['id', 'name']);
        $this->catalog = [];
        foreach ($rows as $r) {
            $this->catalog[$r->name] = (int) $r->id;
        }
        return $this->catalog;
    }
}
