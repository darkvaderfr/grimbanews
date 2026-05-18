<?php

namespace App\Support;

use App\Ground\Regions;
use Botble\Blog\Models\Category;
use Illuminate\Support\Collection;

class GrimbaEditorialCategories
{
    /**
     * @return array<string, array{name: string, order: int, icon: string, description: string}>
     */
    public static function editionRows(): array
    {
        return [
            'africa' => [
                'name' => 'Afrique',
                'order' => 1,
                'icon' => 'ti ti-map-pin',
                'description' => 'Actualité du continent africain, de ses institutions, peuples, économies et diasporas.',
            ],
            'europe' => [
                'name' => 'Europe',
                'order' => 2,
                'icon' => 'ti ti-building-bank',
                'description' => 'Actualité européenne, institutions, sociétés, conflits, économies et débats publics.',
            ],
            'americas' => [
                'name' => 'Amériques',
                'order' => 3,
                'icon' => 'ti ti-map-2',
                'description' => 'Actualité des Amériques, de l’Amérique du Nord aux Caraïbes et à l’Amérique latine.',
            ],
            'international' => [
                'name' => 'International',
                'order' => 4,
                'icon' => 'ti ti-world',
                'description' => "Actualité mondiale lue depuis ses conséquences pour les publics de GrimbaNews.",
            ],
        ];
    }

    /**
     * @return array<int, array{name: string, order: int, icon: string, description: string}>
     */
    public static function topicRows(): array
    {
        return [
            ['name' => 'À la une', 'order' => 10, 'icon' => 'ti ti-news', 'description' => 'Les histoires majeures à suivre maintenant.'],
            ['name' => 'Politique', 'order' => 11, 'icon' => 'ti ti-building-parliament', 'description' => 'Pouvoirs, élections, partis, gouvernements et décisions publiques.'],
            ['name' => 'Économie', 'order' => 12, 'icon' => 'ti ti-chart-bar', 'description' => 'Marchés, entreprises, budgets, commerce, emploi et finance publique.'],
            ['name' => 'Monde', 'order' => 13, 'icon' => 'ti ti-world-search', 'description' => 'Affaires internationales, diplomatie, relations entre États et institutions mondiales.'],
            ['name' => 'Géopolitique', 'order' => 14, 'icon' => 'ti ti-shield', 'description' => 'Conflits, sécurité, défense, alliances et rapports de force.'],
            ['name' => 'Société', 'order' => 15, 'icon' => 'ti ti-users', 'description' => 'Éducation, familles, mouvements sociaux et vie quotidienne.'],
            ['name' => 'Immigration', 'order' => 23, 'icon' => 'ti ti-plane-arrival', 'description' => 'Migration, frontières, asile, intégration et politiques migratoires.'],
            ['name' => 'Justice', 'order' => 16, 'icon' => 'ti ti-scale', 'description' => 'Tribunaux, enquêtes, droits, police et institutions judiciaires.'],
            ['name' => 'Tech & Numérique', 'order' => 17, 'icon' => 'ti ti-device-laptop', 'description' => 'Technologie, plateformes, IA, cybersécurité, télécoms et économie numérique.'],
            ['name' => 'Climat & Environnement', 'order' => 18, 'icon' => 'ti ti-leaf', 'description' => 'Climat, énergie, biodiversité, agriculture, catastrophes et transition écologique.'],
            ['name' => 'Santé', 'order' => 19, 'icon' => 'ti ti-heartbeat', 'description' => 'Santé publique, médecine, hôpitaux, recherche médicale et accès aux soins.'],
            ['name' => 'Sciences', 'order' => 20, 'icon' => 'ti ti-microscope', 'description' => 'Recherche, espace, découvertes, universités et culture scientifique.'],
            ['name' => 'Sports', 'order' => 21, 'icon' => 'ti ti-ball-football', 'description' => 'Compétitions, clubs, athlètes, fédérations et économie du sport.'],
            ['name' => 'Culture', 'order' => 22, 'icon' => 'ti ti-palette', 'description' => 'Arts, cinéma, musique, médias, livres, patrimoine et industries culturelles.'],
        ];
    }

    /**
     * @return array<int, array{name: string, order: int, icon: string, description: string}>
     */
    public static function rows(): array
    {
        return array_values(array_merge(self::editionRows(), self::topicRows()));
    }

    /**
     * @return array<int, string>
     */
    public static function editionNames(): array
    {
        return array_values(array_map(fn (array $row): string => $row['name'], self::editionRows()));
    }

    public static function editionNameForRegion(?string $region = null): string
    {
        $region = $region
            ? Regions::migrate($region)
            : Regions::migrate((string) request()->cookie('grimba_region', 'international'));

        return self::editionRows()[$region]['name'] ?? self::editionRows()['international']['name'];
    }

    /**
     * @return array<int, string>
     */
    public static function topicNames(bool $includeFront = true): array
    {
        return collect(self::topicRows())
            ->pluck('name')
            ->when(! $includeFront, fn (Collection $names) => $names->reject(fn (string $name): bool => $name === 'À la une'))
            ->values()
            ->all();
    }

    /**
     * @return array<int, string>
     */
    public static function internalReviewNames(): array
    {
        return [
            'Trusted Source Credibility',
            'Unclassified Source Bias',
        ];
    }

    /**
     * S-CAT-01 (Vader 2026-05-18) — pick the most representative
     * TOPIC category for a post. Vader's directive: "each article
     * is within its category (ie culture, politics, sports etc.)
     * even for breaking news, top stories, latest stories".
     *
     * The category-badge partial uses this to render a small
     * topic chip on every card surface (hero, briefing, most-read).
     *
     * Picks the first attached category in this order:
     *   1. Topic category (Politique, Culture, Sports, …) — skips
     *      regional bins (Afrique, Europe, Amériques, International)
     *      and the "À la une" front-page bucket since both duplicate
     *      signals already visible elsewhere.
     *   2. Falls back to the first non-internal-review category if
     *      no topic match (very rare given the corpus).
     *   3. Returns null when the post is uncategorized OR carries
     *      only regional + housekeeping tags.
     *
     * Posts must be `with('categories')` loaded — the caller is
     * responsible for warming the relation to avoid N+1.
     */
    public static function primaryTopicFor(object $post): ?object
    {
        $cats = $post->categories ?? null;
        if ($cats === null) {
            return null;
        }
        // Normalize Collection / array / iterable into a flat
        // collection of category models.
        $list = $cats instanceof Collection ? $cats : collect($cats);
        if ($list->isEmpty()) {
            return null;
        }

        $editionNames = self::editionNames();
        $internalNames = self::internalReviewNames();
        $skip = array_merge($editionNames, $internalNames, ['À la une']);

        $topicNames = self::topicNames(includeFront: false);

        // Prefer topic categories.
        $topic = $list->first(fn ($c) => in_array((string) ($c->name ?? ''), $topicNames, true));
        if ($topic === null) {
            // Fallback: any non-skipped category.
            $topic = $list->first(fn ($c) => ! in_array((string) ($c->name ?? ''), $skip, true));
        }

        if ($topic === null) {
            return null;
        }

        // S-CAT-07 (Vader 2026-05-18) — the clickable badge needs
        // $category->url which requires the slug relation. Most
        // callers eager-load `categories` without the slug nested
        // relation, so the accessor returns empty. Load it now if
        // missing — single query when the chain isn't warmed.
        if (
            $topic instanceof \Botble\Blog\Models\Category
            && method_exists($topic, 'relationLoaded')
            && ! $topic->relationLoaded('slugable')
        ) {
            try {
                $topic->load('slugable');
            } catch (\Throwable $e) {
                // Slug system absent — fall through with un-loaded
                // model; badge renders as non-clickable rather than fail.
            }
        }

        return $topic;
    }

    /**
     * @return \Illuminate\Support\Collection<int, \Botble\Blog\Models\Category>
     */
    public static function homepageChips(int $limit = 10): Collection
    {
        $names = self::topicNames();

        // Posts count respects the active region scope so an Africa
        // reader sees how many Politique stories Africa has, not the
        // global total. International readers see the global count
        // because the scope short-circuits to "no filter" there.
        $chips = Category::query()
            ->where('status', 'published')
            ->whereIn('name', $names)
            ->withCount([
                'posts' => fn ($query) => $query
                    ->where('posts.status', 'published'),
            ])
            ->orderBy('order')
            ->get();

        // Vader 2026-05-16 BACKFILL-CAT-2 — chip gate. Hide
        // thin-content categories during pre-launch validation so
        // readers don't land on a near-empty rubrique page. The
        // threshold is configurable via `grimba_chip_min_articles`
        // (default 0 = ungated; flip to 500 to enforce Vader's
        // pre-launch threshold). Once a category crosses the line
        // it shows up automatically on the next page render.
        $minArticles = (int) self::chipMinArticles();
        if ($minArticles > 0) {
            $chips = $chips->filter(
                fn (Category $c): bool => (int) ($c->posts_count ?? 0) >= $minArticles
            );
        }

        return $chips->take($limit)->values();
    }

    /**
     * Threshold below which an editorial chip is suppressed from the
     * homepage rail. 0 = ungated. Operator flips to 500 once
     * `grimba:backfill-category` has populated all chosen categories.
     */
    public static function chipMinArticles(): int
    {
        $raw = function_exists('setting') ? setting('grimba_chip_min_articles', 0) : 0;

        return max(0, (int) $raw);
    }

    /**
     * @return \Illuminate\Support\Collection<int, \Botble\Blog\Models\Category>
     */
    public static function sectionTopics(int $limit = 2): Collection
    {
        // S-CAT-03 (Vader 2026-05-18) — operator-pinned slots first.
        // `grimba_section_pin_1` / `grimba_section_pin_2` (etc.)
        // hold category names. Empty = auto-pick. Pins take
        // priority over auto-picked categories, then we fill the
        // remaining slots with auto-pick to reach `$limit`.
        $pinned = self::pinnedSectionCategories($limit);
        $pinnedIds = $pinned->pluck('id')->all();

        if ($pinned->count() >= $limit) {
            return $pinned->take($limit)->values();
        }

        $remaining = max(0, $limit - $pinned->count());
        $auto = Category::query()
            ->where('status', 'published')
            ->whereIn('name', self::topicNames(includeFront: false))
            ->when(! empty($pinnedIds), fn ($q) => $q->whereNotIn('id', $pinnedIds))
            ->withCount(['posts' => fn ($query) => $query->where('posts.status', 'published')])
            ->orderByDesc('posts_count')
            ->orderBy('order')
            ->get()
            ->filter(fn (Category $category): bool => (int) ($category->posts_count ?? 0) > 0)
            ->take($remaining)
            ->values();

        return $pinned->concat($auto)->take($limit)->values();
    }

    /**
     * Resolve operator-pinned section categories from settings.
     *
     * @return \Illuminate\Support\Collection<int, \Botble\Blog\Models\Category>
     */
    public static function pinnedSectionCategories(int $limit = 2): Collection
    {
        if (! function_exists('setting')) {
            return collect();
        }
        $names = [];
        for ($i = 1; $i <= $limit; $i++) {
            $raw = trim((string) setting('grimba_section_pin_' . $i, ''));
            if ($raw !== '') {
                $names[] = $raw;
            }
        }
        if (empty($names)) {
            return collect();
        }
        $valid = self::topicNames(includeFront: true);
        $names = array_values(array_intersect($names, $valid));
        if (empty($names)) {
            return collect();
        }
        return Category::query()
            ->where('status', 'published')
            ->whereIn('name', $names)
            ->get()
            // Preserve the operator's order — sort by position in $names.
            ->sortBy(fn (Category $c) => array_search($c->name, $names, true))
            ->values();
    }
}
