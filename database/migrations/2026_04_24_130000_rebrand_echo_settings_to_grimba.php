<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/*
 * Rebrand user-visible "Echo" template strings to GrimbaNews.
 *
 * Leaves the internal PHP namespace Theme\Echo\ alone (that'd touch
 * 20+ files and the autoload registration — a separate sprint). This
 * just nukes the visible strings that leak into:
 *   - the <title> on every page ("News & Magazine Laravel Script.")
 *   - the Botble admin toolbar brand at the top of the reader
 *   - the meta description tag
 *   - the newsletter popup copy (was English, now French)
 *
 * Idempotent: uses updateOrInsert on each key. Safe to re-run.
 */
return new class extends Migration {
    public function up(): void
    {
        $map = [
            'theme-echo-site_name'                 => 'Grimba News',
            'theme-echo-site_title'                => 'Grimba News — Voyez chaque angle de chaque histoire',
            'theme-echo-seo_description'           => "Grimba News classe les biais éditoriaux, détecte les angles morts et compare les sources — en français et désormais en anglais.",
            'theme-echo-newsletter_popup_title'    => 'Rejoignez la Briefing GrimbaNews',
            'theme-echo-newsletter_popup_subtitle' => "L'essentiel chaque semaine",
            // Logo swap — SVG files rendered from the same Fraunces-bold
            // masthead Steve shipped in S83. Stored in the media path
            // (main/general/) because Botble's RvMedia::getImageUrl
            // prepends /storage/ to any non-http path; filing them
            // elsewhere 404s the admin toolbar. Bypasses Botble's
            // thumbnail variants entirely since SVG scales on its own.
            'theme-echo-logo'                      => 'main/general/grimba-logo.svg',
            'theme-echo-logo_dark'                 => 'main/general/grimba-logo-dark.svg',
            'admin_logo'                           => 'main/general/grimba-logo.svg',
            'admin_favicon'                        => 'main/general/grimba-logo.svg',
        ];

        foreach ($map as $key => $value) {
            $exists = DB::table('settings')->where('key', $key)->exists();
            if ($exists) {
                DB::table('settings')->where('key', $key)->update([
                    'value'      => $value,
                    'updated_at' => now(),
                ]);
            } else {
                DB::table('settings')->insert([
                    'key'        => $key,
                    'value'      => $value,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        // Rebrand is one-way. Down() is a no-op — the previous "Echo"
        // strings would leak the upstream template's identity if restored.
    }
};
