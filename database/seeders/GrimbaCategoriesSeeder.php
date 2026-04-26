<?php

namespace Database\Seeders;

use Botble\Blog\Models\Category;
use Botble\Slug\Facades\SlugHelper;
use Botble\Slug\Models\Slug;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/*
 * S165 — proper news categories.
 *
 * The Echo theme shipped placeholder taxonomy (Uncategorized,
 * Videos, Podcasts, Healthy, Travel, Business, Entertainment, Sport)
 * that doesn't match how leading newsrooms — Le Monde, NYT, BBC,
 * Guardian — organise coverage. Vader: "this is just subpar".
 *
 * This seeder lays down 15 francophone-first news categories with
 * orderable position. Idempotent: existing categories matching by
 * name keep their id; everything else is created. Shipped with
 * matching slug rows so the /blog/{cat-slug} route resolves.
 *
 * Categories that the NewsAPI catalog uses (business, entertainment,
 * sports, technology, health, science) get aliased to French
 * equivalents inside GrimbaCategoryClassifier — those legacy rows
 * stay published so old post_category pivots don't break, but they
 * don't appear in the topic-chips strip (order > 100).
 */
class GrimbaCategoriesSeeder extends Seeder
{
    public function run(): void
    {
        // Order: lower = earlier in the topic-chips strip.
        $catalog = [
            ['name' => 'À la une',         'order' => 1,  'icon' => 'ti ti-flame',     'description' => 'Histoires majeures du moment, suivies par GrimbaNews.'],
            ['name' => 'France',           'order' => 2,  'icon' => 'ti ti-flag',      'description' => 'Politique, société et actualité française.'],
            ['name' => 'Monde',            'order' => 3,  'icon' => 'ti ti-world',     'description' => 'Couverture internationale, hors France.'],
            ['name' => 'Politique',        'order' => 4,  'icon' => 'ti ti-building',  'description' => 'Vie politique, élections, parlement, gouvernement.'],
            ['name' => 'Économie',         'order' => 5,  'icon' => 'ti ti-trending-up','description' => 'Marchés, entreprises, finance, emploi.'],
            ['name' => 'Tech & Numérique', 'order' => 6,  'icon' => 'ti ti-cpu',       'description' => 'Technologie, IA, plateformes, cybersécurité, télécoms.'],
            ['name' => 'Climat & Environnement','order' => 7,'icon' => 'ti ti-leaf',  'description' => 'Crise climatique, biodiversité, énergie, pollution.'],
            ['name' => 'Santé',            'order' => 8,  'icon' => 'ti ti-heartbeat', 'description' => 'Santé publique, médecine, épidémies, système de soins.'],
            ['name' => 'Sciences',         'order' => 9,  'icon' => 'ti ti-microscope','description' => 'Recherche scientifique, espace, découvertes.'],
            ['name' => 'Sports',           'order' => 10, 'icon' => 'ti ti-soccer-field','description' => 'Compétitions, transferts, événements sportifs.'],
            ['name' => 'Culture',          'order' => 11, 'icon' => 'ti ti-palette',   'description' => 'Cinéma, musique, livres, arts, spectacles.'],
            ['name' => 'Société',          'order' => 12, 'icon' => 'ti ti-users',     'description' => 'Famille, éducation, religion, immigration, manifestations.'],
            ['name' => 'Justice',          'order' => 13, 'icon' => 'ti ti-scale',     'description' => 'Affaires judiciaires, procès, droit, sécurité publique.'],
            ['name' => 'Géopolitique',     'order' => 14, 'icon' => 'ti ti-globe',     'description' => 'Conflits, diplomatie, alliances, défense, tensions internationales.'],
            ['name' => 'Afrique',          'order' => 15, 'icon' => 'ti ti-map-pin',   'description' => 'Actualité du continent africain, par sous-régions.'],
        ];

        $now = now();

        foreach ($catalog as $row) {
            $existing = DB::table('categories')->where('name', $row['name'])->first();

            if ($existing) {
                DB::table('categories')->where('id', $existing->id)->update([
                    'order'       => $row['order'],
                    'icon'        => $row['icon']        ?? null,
                    'description' => $row['description'] ?? null,
                    'status'      => 'published',
                    'updated_at'  => $now,
                ]);
                $id = (int) $existing->id;
            } else {
                $id = (int) DB::table('categories')->insertGetId([
                    'name'        => $row['name'],
                    'parent_id'   => 0,
                    'order'       => $row['order'],
                    'icon'        => $row['icon']        ?? null,
                    'description' => $row['description'] ?? null,
                    'status'      => 'published',
                    'author_id'   => 1,
                    'author_type' => \Botble\ACL\Models\User::class,
                    'is_featured' => 0,
                    'is_default'  => 0,
                    'created_at'  => $now,
                    'updated_at'  => $now,
                ]);
            }

            // Ensure a Botble slug row exists pointing at this category.
            $slugBase = Str::slug($row['name']);
            $slugExists = Slug::where('reference_id', $id)
                ->where('reference_type', Category::class)
                ->exists();
            if (! $slugExists && $slugBase !== '') {
                $slug = $slugBase;
                $i = 2;
                while (Slug::where('key', $slug)->where('reference_type', Category::class)->exists()) {
                    $slug = $slugBase . '-' . $i;
                    $i++;
                }
                Slug::create([
                    'key'            => $slug,
                    'reference_id'   => $id,
                    'reference_type' => Category::class,
                    'prefix'         => SlugHelper::getPrefix(Category::class) ?? 'blog',
                ]);
            }
        }

        // Demote the legacy placeholder cats to high-order so they fall
        // off the topic-chips strip without orphaning old post pivots.
        $demote = ['Uncategorized', 'Videos', 'Podcasts', 'Healthy', 'Travel', 'Business', 'Entertainment'];
        DB::table('categories')->whereIn('name', $demote)->update([
            'order'      => 999,
            'updated_at' => $now,
        ]);

        $this->command?->info('GrimbaCategoriesSeeder: 15 news categories ordered 1-15, ' . count($demote) . ' legacy demoted to 999.');
    }
}
