<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NewsSourcesSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $sources = [
            // France — broad spectrum
            ['Le Monde',        'lemonde.fr',         'left',    'independent', 88, 'FR', 'fr'],
            ['Libération',      'liberation.fr',      'left',    'corporate',   80, 'FR', 'fr'],
            ['Mediapart',       'mediapart.fr',       'left',    'independent', 82, 'FR', 'fr'],
            ['AFP',             'afp.com',            'center',  'state',       92, 'FR', 'fr'],
            ['France 24',       'france24.com',       'center',  'state',       85, 'FR', 'fr'],
            ['Le Figaro',       'lefigaro.fr',        'right',   'corporate',   85, 'FR', 'fr'],
            ['Valeurs Actuelles','valeursactuelles.com','right', 'corporate',   60, 'FR', 'fr'],
            ['L\'Opinion',      'lopinion.fr',        'right',   'corporate',   72, 'FR', 'fr'],

            // International wires / anglophone benchmarks
            ['Reuters',         'reuters.com',        'center',  'corporate',   94, 'GB', 'en'],
            ['Associated Press','apnews.com',         'center',  'nonprofit',   93, 'US', 'en'],
            ['BBC',             'bbc.com',            'center',  'state',       90, 'GB', 'en'],
            ['The Guardian',    'theguardian.com',    'left',    'nonprofit',   85, 'GB', 'en'],
            ['Wall Street Journal','wsj.com',         'right',   'corporate',   86, 'US', 'en'],

            // African & francophone Africa
            ['Jeune Afrique',   'jeuneafrique.com',   'center',  'independent', 82, 'CI', 'fr'],
            ['RFI Afrique',     'rfi.fr',             'center',  'state',       86, 'FR', 'fr'],
            ['Le Pays',         'lepays.bf',          'center',  'independent', 70, 'BF', 'fr'],
            ['Le Soleil',       'lesoleil.sn',        'center',  'state',       72, 'SN', 'fr'],
            ['Cameroon Tribune','cameroon-tribune.cm','center',  'state',       65, 'CM', 'fr'],
            ['Financial Afrik', 'financialafrik.com', 'center',  'independent', 75, 'CI', 'fr'],
            ['All Africa',      'allafrica.com',      'center',  'nonprofit',   78, 'ZA', 'en'],
        ];

        foreach ($sources as [$name, $site, $bias, $ownership, $score, $country, $lang]) {
            DB::table('news_sources')->updateOrInsert(
                ['name' => $name],
                [
                    'website'           => $site,
                    'bias_rating'       => $bias,
                    'ownership_type'    => $ownership,
                    'credibility_score' => $score,
                    'country'           => $country,
                    'language'          => $lang,
                    'updated_at'        => $now,
                    'created_at'        => $now,
                ]
            );
        }
    }
}
