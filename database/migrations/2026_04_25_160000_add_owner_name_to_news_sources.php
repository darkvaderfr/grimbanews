<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * S139 — parent-company column for the GrimbaNews ownership map.
 *
 * `ownership_type` (corporate / public / state-owned / etc.) was a
 * coarse signal — a reader looking at "Disney" would not know it owns
 * ABC News. This adds a free-text owner name (e.g. "The Walt Disney
 * Company", "News Corp", "Jeff Bezos", "BBC Trust") so per-source
 * pages can render anti-monopoly transparency.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('news_sources', function (Blueprint $table): void {
            if (! Schema::hasColumn('news_sources', 'owner_name')) {
                $table->string('owner_name', 191)->nullable()->after('ownership_type');
            }
        });

        // Backfill known parent companies. Public-record holdings only.
        $owners = [
            'CNN'                         => 'Warner Bros. Discovery',
            'MSNBC'                       => 'NBCUniversal (Comcast)',
            'NBC News'                    => 'NBCUniversal (Comcast)',
            'HuffPost'                    => 'BuzzFeed Inc.',
            'Buzzfeed'                    => 'BuzzFeed Inc.',
            'The Washington Post'         => 'Nash Holdings (Jeff Bezos)',
            'The New York Times'          => 'The New York Times Company',
            'Politico'                    => 'Axel Springer SE',
            'Time'                        => 'Marc Benioff',
            'Newsweek'                    => 'IBT Media',
            'Reuters'                     => 'Thomson Reuters',
            'Reuters UK'                  => 'Thomson Reuters',
            'Associated Press'            => 'AP cooperative (member-owned)',
            'Bloomberg'                   => 'Bloomberg L.P. (Michael Bloomberg)',
            'Business Insider'            => 'Axel Springer SE',
            'USA Today'                   => 'Gannett Co.',
            'ABC News'                    => 'The Walt Disney Company',
            'CBS News'                    => 'Paramount Global',
            'Fox News'                    => 'Fox Corporation (Murdoch family)',
            'The Wall Street Journal'     => 'News Corp (Murdoch family)',
            'Breitbart News'              => 'Breitbart News Network LLC',
            'National Review'             => 'National Review Inc.',
            'The American Conservative'   => 'The American Ideas Institute',
            'The Washington Times'        => 'Operations Holdings (Unification Church)',
            'The Hill'                    => 'Nexstar Media Group',
            'BBC News'                    => 'BBC (British public broadcaster)',
            'BBC Sport'                   => 'BBC (British public broadcaster)',
            'The Guardian'                => 'Scott Trust Limited',
            'The Independent'             => 'Sultan Mohamed Abuljadayel + Evgeny Lebedev',
            'Daily Mail'                  => 'Daily Mail and General Trust',
            'The Telegraph'               => 'RedBird IMI',
            'Financial Times'             => 'Nikkei Inc.',
            'The Times of India'          => 'Bennett, Coleman & Co. Ltd.',
            'Al Jazeera English'          => 'Qatar Media Corporation (state-funded)',
            'RT'                          => 'TV-Novosti (Russian government-funded)',
            'CBC News'                    => 'CBC/Radio-Canada (Canadian public broadcaster)',
            'ABC News (AU)'               => 'Australian Broadcasting Corporation (public)',
            'RTÉ News'                    => 'Raidió Teilifís Éireann (Irish public broadcaster)',
            'TechCrunch'                  => 'Yahoo Inc. (Apollo Global Management)',
            'Wired'                       => 'Condé Nast (Advance Publications)',
            'The Verge'                   => 'Vox Media',
            'Engadget'                    => 'Yahoo Inc. (Apollo Global Management)',
            'Recode'                      => 'Vox Media',
            'CryptoCoins News'            => 'CCN.com',
            'Hacker News'                 => 'Y Combinator',
            'Ars Technica'                => 'Condé Nast (Advance Publications)',
            'National Geographic'         => 'The Walt Disney Company',
            'Le Monde'                    => 'Xavier Niel + Daniel Křetínský',
            'Libération'                  => 'Patrick Drahi (Altice)',
            'Mediapart'                   => 'Société des Amis de Mediapart (employee-owned)',
            'Les Echos'                   => 'LVMH (Bernard Arnault)',
            "L'Équipe"                    => 'Groupe Amaury (famille Amaury)',
            'France 24'                   => 'France Médias Monde (state)',
            'AFP'                         => 'Agence France-Presse (public-private)',
            'Le Figaro'                   => 'Groupe Dassault',
            'Google News (France)'       => 'Alphabet Inc.',
        ];

        foreach ($owners as $name => $owner) {
            DB::table('news_sources')
                ->where('name', $name)
                ->whereNull('owner_name')
                ->update(['owner_name' => $owner, 'updated_at' => now()]);
        }
    }

    public function down(): void
    {
        Schema::table('news_sources', function (Blueprint $table): void {
            if (Schema::hasColumn('news_sources', 'owner_name')) {
                $table->dropColumn('owner_name');
            }
        });
    }
};
