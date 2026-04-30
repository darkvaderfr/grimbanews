<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/*
 * S165/S007 — edition classifier.
 *
 * Maps a post (title + description + source) onto one of the two
 * canonical editorial categories seeded by GrimbaCategoriesSeeder:
 * Afrique or International. Pure heuristic (no LLM call), runs at
 * ingest time and inside the grimba:classify-categories backfill
 * artisan.
 *
 * Strategy: African sources and Africa-specific keywords route to
 * Afrique; everything else routes to International. This keeps the
 * public taxonomy editorially simple while preserving bias/source
 * analysis as separate metadata.
 */
class GrimbaCategoryClassifier
{
    private const TRIGGERS = [
        'Afrique'   => ['afrique','africa','algérie','algeria','maroc','morocco','tunisie','tunisia','égypte','egypt','sénégal','senegal','côte d\'ivoire','ivory coast','mali','burkina','burkina faso','niger','tchad','chad','cameroun','cameroon','gabon','gabón','congo','rdc','drc','kenya','éthiopie','ethiopia','soudan','sudan','nigeria','ghana','afrique du sud','south africa','rwanda','tanzanie','tanzania','aes ','sahel','wagner','africorps'],
    ];

    /** Shortcut: NewsAPI sources whose primary category is fixed. */
    private const SOURCE_CATEGORY = [
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
     * Returns the category id that should be attached to the post.
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

        if ($matches === [] && isset($catalog['International'])) {
            $matches['International'] = 1;
        }

        if ($matches === [] && isset($catalog['Monde'])) {
            $matches['Monde'] = 1;
        }

        // Sort by score desc, take the single canonical category.
        arsort($matches);
        $top = array_slice($matches, 0, 1, true);

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
