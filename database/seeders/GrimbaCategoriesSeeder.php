<?php

namespace Database\Seeders;

use Botble\Blog\Models\Category;
use Botble\Slug\Facades\SlugHelper;
use Botble\Slug\Models\Slug;
use App\Support\GrimbaEditorialCategories;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/*
 * S165/S007 — canonical editorial categories.
 *
 * Idempotent: existing categories matching by name keep their id.
 * Slug rows are maintained so /blog/{category} remains stable.
 */
class GrimbaCategoriesSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        foreach (GrimbaEditorialCategories::rows() as $row) {
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

        // Demote stock/demo rows so they fall off public editorial
        // navigation without orphaning old post pivots.
        $demote = [
            'France', 'Uncategorized', 'Videos', 'Podcasts', 'Healthy',
            'Travel', 'Business', 'Entertainment',
        ];
        DB::table('categories')->whereIn('name', $demote)->update([
            'order'      => 999,
            'updated_at' => $now,
        ]);

        $this->command?->info('GrimbaCategoriesSeeder: edition + topical news categories restored, ' . count($demote) . ' stock categories demoted to 999.');
    }
}
