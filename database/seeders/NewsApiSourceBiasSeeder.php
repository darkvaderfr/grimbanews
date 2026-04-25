<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/*
 * S129 — bias / ownership / credibility seed for the major NewsAPI
 * source IDs that GrimbaNews ingests at scale.
 *
 * Bias columns mirror the existing news_sources schema:
 *   bias_rating       : left | center | right | unknown
 *   ownership_type    : independent | corporate | public | state-owned | foundation | cooperative
 *   credibility_score : 0–100 (~Ad Fontes "reliability score" rounded)
 *
 * Sources for the ratings: AllSides Media Bias chart (left/right
 * lean + factuality), Ad Fontes Media (reliability + bias score),
 * MBFC for ownership confirmation. Where AllSides + Ad Fontes
 * disagree, we lean toward AllSides for political bias since their
 * methodology is consensus-driven by readers across the spectrum.
 *
 * Idempotent: re-runs only update fields that aren't already set.
 * Editor-set values (someone customised an outlet's bias in admin)
 * are preserved.
 */
class NewsApiSourceBiasSeeder extends Seeder
{
    public function run(): void
    {
        $catalog = $this->catalog();

        $created = 0;
        $updated = 0;
        $skipped = 0;

        foreach ($catalog as $row) {
            $existing = DB::table('news_sources')
                ->where('api_id', $row['api_id'])
                ->orWhere('name', $row['name'])
                ->first();

            $slug = Str::slug($row['name']);

            if (! $existing) {
                DB::table('news_sources')->insert([
                    'name'             => $row['name'],
                    'slug'             => $this->uniqueSlug($slug),
                    'api_id'           => $row['api_id'],
                    'website'          => $row['website']    ?? null,
                    'bias_rating'      => $row['bias']       ?? 'unknown',
                    'ownership_type'   => $row['ownership']  ?? null,
                    'credibility_score'=> $row['credibility']?? null,
                    'country'          => $row['country']    ?? null,
                    'language'         => $row['language']   ?? null,
                    'description'      => $row['description']?? null,
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ]);
                $created++;
                continue;
            }

            // Update fields ONLY when they're currently empty/null.
            // Editor-set values stay sacred.
            $updates = [];
            if (empty($existing->api_id))            $updates['api_id'] = $row['api_id'];
            if (empty($existing->website))           $updates['website'] = $row['website'] ?? null;
            if (($existing->bias_rating ?? 'unknown') === 'unknown' && ! empty($row['bias']))
                $updates['bias_rating'] = $row['bias'];
            if (empty($existing->ownership_type))    $updates['ownership_type'] = $row['ownership'] ?? null;
            if (empty($existing->credibility_score) && isset($row['credibility']))
                $updates['credibility_score'] = $row['credibility'];
            if (empty($existing->country))           $updates['country'] = $row['country'] ?? null;
            if (empty($existing->language))          $updates['language'] = $row['language'] ?? null;
            if (empty($existing->description))       $updates['description'] = $row['description'] ?? null;

            if (! empty($updates)) {
                $updates['updated_at'] = now();
                DB::table('news_sources')->where('id', $existing->id)->update($updates);
                $updated++;
            } else {
                $skipped++;
            }
        }

        $this->command?->info(sprintf(
            'NewsApiSourceBiasSeeder: %d created, %d updated, %d unchanged.',
            $created, $updated, $skipped
        ));
    }

    private function uniqueSlug(string $base): string
    {
        $slug = $base ?: 'source';
        $i = 2;
        while (DB::table('news_sources')->where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i;
            $i++;
        }
        return $slug;
    }

    /**
     * Hand-curated NewsAPI source map. Bias / credibility / ownership
     * derived from AllSides + Ad Fontes Media (public ratings).
     *
     * @return array<int, array<string, mixed>>
     */
    private function catalog(): array
    {
        return [
            // FRENCH
            ['api_id' => 'le-monde',     'name' => 'Le Monde',     'website' => 'lemonde.fr',     'bias' => 'left',   'ownership' => 'independent', 'credibility' => 88, 'country' => 'FR', 'language' => 'fr', 'description' => 'Quotidien français de référence, ligne éditoriale centre-gauche, propriété de Xavier Niel et Daniel Křetínský.'],
            ['api_id' => 'liberation',   'name' => 'Libération',   'website' => 'liberation.fr',  'bias' => 'left',   'ownership' => 'corporate',   'credibility' => 80, 'country' => 'FR', 'language' => 'fr', 'description' => 'Quotidien français à orientation gauche, fondé en 1973 par Jean-Paul Sartre et Serge July.'],
            ['api_id' => 'les-echos',    'name' => 'Les Echos',    'website' => 'lesechos.fr',    'bias' => 'center', 'ownership' => 'corporate',   'credibility' => 85, 'country' => 'FR', 'language' => 'fr', 'description' => 'Quotidien économique français, propriété du groupe LVMH.'],
            ['api_id' => 'lequipe',      'name' => 'L\'Équipe',    'website' => 'lequipe.fr',     'bias' => 'center', 'ownership' => 'corporate',   'credibility' => 82, 'country' => 'FR', 'language' => 'fr', 'description' => 'Quotidien sportif français, propriété du groupe Amaury.'],
            ['api_id' => 'google-news-fr','name' => 'Google News (France)', 'website' => 'news.google.com', 'bias' => 'unknown', 'ownership' => 'corporate', 'credibility' => 75, 'country' => 'FR', 'language' => 'fr', 'description' => 'Agrégateur Google News, édition française.'],

            // US — left-leaning
            ['api_id' => 'cnn',          'name' => 'CNN',          'website' => 'cnn.com',        'bias' => 'left',   'ownership' => 'corporate',   'credibility' => 65, 'country' => 'US', 'language' => 'en', 'description' => 'Cable news network owned by Warner Bros. Discovery. AllSides rates as left.'],
            ['api_id' => 'msnbc',        'name' => 'MSNBC',        'website' => 'msnbc.com',      'bias' => 'left',   'ownership' => 'corporate',   'credibility' => 55, 'country' => 'US', 'language' => 'en', 'description' => 'Cable news network owned by NBCUniversal. AllSides rates as left.'],
            ['api_id' => 'the-huffington-post', 'name' => 'HuffPost', 'website' => 'huffpost.com', 'bias' => 'left', 'ownership' => 'corporate', 'credibility' => 60, 'country' => 'US', 'language' => 'en', 'description' => 'Online news outlet owned by BuzzFeed. AllSides rates as left.'],
            ['api_id' => 'vice-news',    'name' => 'Vice News',    'website' => 'vice.com',       'bias' => 'left',   'ownership' => 'corporate',   'credibility' => 62, 'country' => 'US', 'language' => 'en', 'description' => 'Vice Media news vertical. AllSides rates as left.'],
            ['api_id' => 'buzzfeed',     'name' => 'Buzzfeed',     'website' => 'buzzfeed.com',   'bias' => 'left',   'ownership' => 'corporate',   'credibility' => 55, 'country' => 'US', 'language' => 'en', 'description' => 'BuzzFeed News + lifestyle. AllSides rates as left.'],
            ['api_id' => 'the-washington-post', 'name' => 'The Washington Post', 'website' => 'washingtonpost.com', 'bias' => 'left', 'ownership' => 'corporate', 'credibility' => 78, 'country' => 'US', 'language' => 'en', 'description' => 'US daily owned by Jeff Bezos. AllSides rates as lean-left.'],
            ['api_id' => 'the-new-york-times', 'name' => 'The New York Times', 'website' => 'nytimes.com', 'bias' => 'left', 'ownership' => 'corporate', 'credibility' => 80, 'country' => 'US', 'language' => 'en', 'description' => 'US daily of record. AllSides rates as lean-left on the news side.'],
            ['api_id' => 'politico',     'name' => 'Politico',     'website' => 'politico.com',   'bias' => 'left',   'ownership' => 'corporate',   'credibility' => 78, 'country' => 'US', 'language' => 'en', 'description' => 'US political news outlet owned by Axel Springer. AllSides rates as lean-left.'],
            ['api_id' => 'time',         'name' => 'Time',         'website' => 'time.com',       'bias' => 'left',   'ownership' => 'corporate',   'credibility' => 75, 'country' => 'US', 'language' => 'en', 'description' => 'US weekly. AllSides rates as lean-left.'],
            ['api_id' => 'newsweek',     'name' => 'Newsweek',     'website' => 'newsweek.com',   'bias' => 'center', 'ownership' => 'corporate',   'credibility' => 65, 'country' => 'US', 'language' => 'en', 'description' => 'US weekly. AllSides rates as center.'],

            // US — center
            ['api_id' => 'reuters',      'name' => 'Reuters',      'website' => 'reuters.com',    'bias' => 'center', 'ownership' => 'corporate',   'credibility' => 92, 'country' => 'US', 'language' => 'en', 'description' => 'International wire service owned by Thomson Reuters. AllSides rates as center.'],
            ['api_id' => 'associated-press', 'name' => 'Associated Press', 'website' => 'apnews.com', 'bias' => 'center', 'ownership' => 'cooperative', 'credibility' => 92, 'country' => 'US', 'language' => 'en', 'description' => 'US wire service cooperative. AllSides rates as lean-left to center.'],
            ['api_id' => 'bloomberg',    'name' => 'Bloomberg',    'website' => 'bloomberg.com',  'bias' => 'center', 'ownership' => 'corporate',   'credibility' => 85, 'country' => 'US', 'language' => 'en', 'description' => 'Business news + data terminal. AllSides rates as center.'],
            ['api_id' => 'business-insider', 'name' => 'Business Insider', 'website' => 'businessinsider.com', 'bias' => 'center', 'ownership' => 'corporate', 'credibility' => 70, 'country' => 'US', 'language' => 'en', 'description' => 'Business news outlet owned by Axel Springer.'],
            ['api_id' => 'usa-today',    'name' => 'USA Today',    'website' => 'usatoday.com',   'bias' => 'center', 'ownership' => 'corporate',   'credibility' => 75, 'country' => 'US', 'language' => 'en', 'description' => 'US daily owned by Gannett. AllSides rates as lean-left to center.'],
            ['api_id' => 'abc-news',     'name' => 'ABC News',     'website' => 'abcnews.go.com', 'bias' => 'center', 'ownership' => 'corporate',   'credibility' => 75, 'country' => 'US', 'language' => 'en', 'description' => 'Broadcast news network owned by Disney.'],
            ['api_id' => 'cbs-news',     'name' => 'CBS News',     'website' => 'cbsnews.com',    'bias' => 'center', 'ownership' => 'corporate',   'credibility' => 75, 'country' => 'US', 'language' => 'en', 'description' => 'Broadcast news network owned by Paramount.'],
            ['api_id' => 'nbc-news',     'name' => 'NBC News',     'website' => 'nbcnews.com',    'bias' => 'left',   'ownership' => 'corporate',   'credibility' => 72, 'country' => 'US', 'language' => 'en', 'description' => 'Broadcast news network owned by NBCUniversal.'],

            // US — right-leaning
            ['api_id' => 'fox-news',     'name' => 'Fox News',     'website' => 'foxnews.com',    'bias' => 'right',  'ownership' => 'corporate',   'credibility' => 50, 'country' => 'US', 'language' => 'en', 'description' => 'Cable news network owned by Fox Corporation. AllSides rates as right.'],
            ['api_id' => 'breitbart-news','name' => 'Breitbart News', 'website' => 'breitbart.com', 'bias' => 'right', 'ownership' => 'corporate',  'credibility' => 35, 'country' => 'US', 'language' => 'en', 'description' => 'Conservative news outlet. AllSides rates as right.'],
            ['api_id' => 'national-review','name' => 'National Review', 'website' => 'nationalreview.com', 'bias' => 'right', 'ownership' => 'corporate', 'credibility' => 60, 'country' => 'US', 'language' => 'en', 'description' => 'Conservative magazine founded 1955. AllSides rates as right.'],
            ['api_id' => 'the-american-conservative', 'name' => 'The American Conservative', 'website' => 'theamericanconservative.com', 'bias' => 'right', 'ownership' => 'foundation', 'credibility' => 60, 'country' => 'US', 'language' => 'en', 'description' => 'Conservative opinion magazine. AllSides rates as right.'],
            ['api_id' => 'the-washington-times', 'name' => 'The Washington Times', 'website' => 'washingtontimes.com', 'bias' => 'right', 'ownership' => 'corporate', 'credibility' => 55, 'country' => 'US', 'language' => 'en', 'description' => 'US daily, conservative editorial line.'],
            ['api_id' => 'the-wall-street-journal', 'name' => 'The Wall Street Journal', 'website' => 'wsj.com', 'bias' => 'right', 'ownership' => 'corporate', 'credibility' => 80, 'country' => 'US', 'language' => 'en', 'description' => 'US business daily owned by News Corp. AllSides rates as lean-right (news) / right (opinion).'],
            ['api_id' => 'the-hill',     'name' => 'The Hill',     'website' => 'thehill.com',    'bias' => 'center', 'ownership' => 'corporate',   'credibility' => 72, 'country' => 'US', 'language' => 'en', 'description' => 'US politics outlet. AllSides rates as center.'],

            // UK
            ['api_id' => 'bbc-news',     'name' => 'BBC News',     'website' => 'bbc.com/news',   'bias' => 'center', 'ownership' => 'public',      'credibility' => 88, 'country' => 'GB', 'language' => 'en', 'description' => 'UK public broadcaster. AllSides rates as center.'],
            ['api_id' => 'bbc-sport',    'name' => 'BBC Sport',    'website' => 'bbc.com/sport',  'bias' => 'center', 'ownership' => 'public',      'credibility' => 88, 'country' => 'GB', 'language' => 'en', 'description' => 'BBC sports vertical.'],
            ['api_id' => 'the-guardian-uk','name' => 'The Guardian','website' => 'theguardian.com','bias' => 'left',   'ownership' => 'foundation',  'credibility' => 82, 'country' => 'GB', 'language' => 'en', 'description' => 'UK daily owned by Scott Trust. AllSides rates as left.'],
            ['api_id' => 'independent',  'name' => 'The Independent','website' => 'independent.co.uk','bias' => 'left','ownership'=> 'corporate',     'credibility' => 75, 'country' => 'GB', 'language' => 'en', 'description' => 'UK online daily.'],
            ['api_id' => 'reuters',      'name' => 'Reuters UK',   'website' => 'reuters.com',    'bias' => 'center', 'ownership' => 'corporate',   'credibility' => 92, 'country' => 'GB', 'language' => 'en', 'description' => 'Reuters UK edition.'],
            ['api_id' => 'daily-mail',   'name' => 'Daily Mail',   'website' => 'dailymail.co.uk','bias' => 'right',  'ownership' => 'corporate',   'credibility' => 50, 'country' => 'GB', 'language' => 'en', 'description' => 'UK daily tabloid. AllSides rates as right.'],
            ['api_id' => 'the-telegraph','name' => 'The Telegraph','website' => 'telegraph.co.uk','bias' => 'right',  'ownership' => 'corporate',   'credibility' => 70, 'country' => 'GB', 'language' => 'en', 'description' => 'UK daily, conservative editorial line.'],
            ['api_id' => 'the-times-of-india', 'name' => 'The Times of India', 'website' => 'timesofindia.indiatimes.com', 'bias' => 'center', 'ownership' => 'corporate', 'credibility' => 70, 'country' => 'IN', 'language' => 'en', 'description' => 'Indian English-language daily.'],
            ['api_id' => 'financial-times', 'name' => 'Financial Times', 'website' => 'ft.com', 'bias' => 'center', 'ownership' => 'corporate', 'credibility' => 88, 'country' => 'GB', 'language' => 'en', 'description' => 'UK business daily owned by Nikkei.'],

            // International
            ['api_id' => 'al-jazeera-english','name' => 'Al Jazeera English','website' => 'aljazeera.com','bias' => 'left','ownership' => 'state-owned','credibility' => 70,'country' => 'QA','language' => 'en','description' => 'Qatari state-funded broadcaster, English service.'],
            ['api_id' => 'rt',           'name' => 'RT',           'website' => 'rt.com',         'bias' => 'right',  'ownership' => 'state-owned', 'credibility' => 25, 'country' => 'RU', 'language' => 'en', 'description' => 'Russian state-funded outlet. Low credibility per multiple ratings.'],
            ['api_id' => 'cbc-news',     'name' => 'CBC News',     'website' => 'cbc.ca/news',    'bias' => 'center', 'ownership' => 'public',      'credibility' => 85, 'country' => 'CA', 'language' => 'en', 'description' => 'Canadian public broadcaster.'],
            ['api_id' => 'abc-news-au',  'name' => 'ABC News (AU)','website' => 'abc.net.au',     'bias' => 'center', 'ownership' => 'public',      'credibility' => 85, 'country' => 'AU', 'language' => 'en', 'description' => 'Australian public broadcaster.'],
            ['api_id' => 'rte',          'name' => 'RTÉ News',     'website' => 'rte.ie',         'bias' => 'center', 'ownership' => 'public',      'credibility' => 82, 'country' => 'IE', 'language' => 'en', 'description' => 'Irish public broadcaster.'],

            // Tech / business
            ['api_id' => 'techcrunch',   'name' => 'TechCrunch',   'website' => 'techcrunch.com', 'bias' => 'center', 'ownership' => 'corporate',   'credibility' => 70, 'country' => 'US', 'language' => 'en', 'description' => 'Tech industry news. AllSides rates as lean-left to center.'],
            ['api_id' => 'wired',        'name' => 'Wired',        'website' => 'wired.com',      'bias' => 'left',   'ownership' => 'corporate',   'credibility' => 75, 'country' => 'US', 'language' => 'en', 'description' => 'Tech + culture magazine owned by Condé Nast.'],
            ['api_id' => 'the-verge',    'name' => 'The Verge',    'website' => 'theverge.com',   'bias' => 'left',   'ownership' => 'corporate',   'credibility' => 72, 'country' => 'US', 'language' => 'en', 'description' => 'Tech news outlet, owned by Vox Media.'],
            ['api_id' => 'engadget',     'name' => 'Engadget',     'website' => 'engadget.com',   'bias' => 'center', 'ownership' => 'corporate',   'credibility' => 72, 'country' => 'US', 'language' => 'en', 'description' => 'Tech news outlet.'],
            ['api_id' => 'recode',       'name' => 'Recode',       'website' => 'vox.com/recode', 'bias' => 'left',   'ownership' => 'corporate',   'credibility' => 72, 'country' => 'US', 'language' => 'en', 'description' => 'Tech business news, Vox Media.'],
            ['api_id' => 'crypto-coins-news','name' => 'CryptoCoins News','website' => 'ccn.com', 'bias' => 'center','ownership' => 'corporate',   'credibility' => 60, 'country' => 'US', 'language' => 'en', 'description' => 'Crypto-finance outlet.'],
            ['api_id' => 'hacker-news',  'name' => 'Hacker News',  'website' => 'news.ycombinator.com','bias' => 'unknown','ownership' => 'corporate','credibility' => 75,'country' => 'US','language' => 'en','description' => 'Y Combinator community-driven aggregator.'],
            ['api_id' => 'ars-technica', 'name' => 'Ars Technica', 'website' => 'arstechnica.com','bias' => 'left',   'ownership' => 'corporate',   'credibility' => 80, 'country' => 'US', 'language' => 'en', 'description' => 'Tech + science publication, Condé Nast.'],

            // Misc
            ['api_id' => 'national-geographic','name' => 'National Geographic','website' => 'nationalgeographic.com','bias' => 'center','ownership' => 'corporate','credibility' => 88,'country' => 'US','language' => 'en','description' => 'Geography + science magazine, Disney.'],
        ];
    }
}
