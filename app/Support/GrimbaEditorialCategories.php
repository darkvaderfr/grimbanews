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
            ['name' => 'Société', 'order' => 15, 'icon' => 'ti ti-users', 'description' => 'Éducation, migrations, familles, mouvements sociaux et vie quotidienne.'],
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
     * @return \Illuminate\Support\Collection<int, \Botble\Blog\Models\Category>
     */
    public static function homepageChips(int $limit = 10): Collection
    {
        $names = self::topicNames();

        // Posts count respects the active region scope so an Africa
        // reader sees how many Politique stories Africa has, not the
        // global total. International readers see the global count
        // because the scope short-circuits to "no filter" there.
        return Category::query()
            ->where('status', 'published')
            ->whereIn('name', $names)
            ->withCount([
                'posts' => fn ($query) => $query
                    ->where('posts.status', 'published'),
            ])
            ->orderBy('order')
            ->get()
            ->take($limit)
            ->values();
    }

    /**
     * @return \Illuminate\Support\Collection<int, \Botble\Blog\Models\Category>
     */
    public static function sectionTopics(int $limit = 2): Collection
    {
        return Category::query()
            ->where('status', 'published')
            ->whereIn('name', self::topicNames(includeFront: false))
            ->withCount(['posts' => fn ($query) => $query->where('posts.status', 'published')])
            ->orderByDesc('posts_count')
            ->orderBy('order')
            ->get()
            ->filter(fn (Category $category): bool => (int) ($category->posts_count ?? 0) > 0)
            ->take($limit)
            ->values();
    }
}
