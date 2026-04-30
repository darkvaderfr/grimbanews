<?php

namespace Database\Seeders;

use Botble\Blog\Models\Category;
use Botble\Slug\Facades\SlugHelper;
use Botble\Slug\Models\Slug;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/*
 * S165/S007 — canonical editorial categories.
 *
 * The public editorial taxonomy is intentionally reduced to two
 * editions: Afrique and International. Legacy topical rows are kept
 * demoted so old pivots and URLs do not break, but new classification
 * and the homepage chips use only these two categories.
 *
 * Idempotent: existing categories matching by name keep their id.
 * Slug rows are maintained so /blog/afrique and /blog/international
 * remain stable Botble category routes.
 */
class GrimbaCategoriesSeeder extends Seeder
{
    public function run(): void
    {
        // Order: lower = earlier in the topic-chips strip.
        $catalog = [
            ['name' => 'Afrique', 'order' => 1, 'icon' => 'ti ti-map-pin', 'description' => 'Actualité du continent africain, de ses institutions, peuples, économies et diasporas.'],
            ['name' => 'International', 'order' => 2, 'icon' => 'ti ti-world', 'description' => "Actualité mondiale lue depuis ses conséquences pour l'Afrique et ses publics."],
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

        // Demote legacy topical rows so they fall off public editorial
        // navigation without orphaning old post pivots.
        $demote = [
            'À la une', 'France', 'Monde', 'Politique', 'Économie',
            'Tech & Numérique', 'Climat & Environnement', 'Santé',
            'Sciences', 'Sports', 'Culture', 'Société', 'Justice',
            'Géopolitique', 'Uncategorized', 'Videos', 'Podcasts',
            'Healthy', 'Travel', 'Business', 'Entertainment',
        ];
        DB::table('categories')->whereIn('name', $demote)->update([
            'order'      => 999,
            'updated_at' => $now,
        ]);

        $this->command?->info('GrimbaCategoriesSeeder: Afrique + International ordered first, ' . count($demote) . ' legacy categories demoted to 999.');
    }
}
