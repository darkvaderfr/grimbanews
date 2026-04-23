<?php

namespace Database\Seeders;

use Botble\Blog\Models\Category;
use Botble\Blog\Models\Post;
use Botble\Slug\Facades\SlugHelper;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class GrimbaContentSeeder extends Seeder
{
    /**
     * Seed 3 story clusters (3 posts each, Left/Centre/Droite) and
     * 6 standalone French posts so the GroundNews-style homepage
     * has real material to render. Attaches posts to existing
     * news_sources rows — the Post::saving hook propagates bias,
     * ownership, credibility and source_name automatically.
     */
    public function run(): void
    {
        $seedDir = public_path('storage/grimba-seeds');
        File::ensureDirectoryExists($seedDir);

        $sources = DB::table('news_sources')->pluck('id', 'name');

        $authorId = \Botble\ACL\Models\User::query()->value('id') ?? 1;

        // ────────────────────────────────────────────────────────
        // Clusters — same story, three sides.
        // ────────────────────────────────────────────────────────
        $clusters = [
            1001 => [
                'topic' => 'Réforme des retraites 2026',
                'tint'  => '#4a3b2a',
                'category' => 'Business',
                'posts' => [
                    [
                        'source' => 'Le Monde',
                        'name'   => 'Réforme des retraites : les syndicats appellent à une grève générale le 3 mai',
                        'desc'   => 'Unité intersyndicale inédite. Les huit principales centrales dénoncent un texte qui allongerait la durée de cotisation à 43 ans d\'ici 2029 et réclament son retrait pur et simple. Mobilisation annoncée dans 180 villes.',
                        'featured' => true,
                    ],
                    [
                        'source' => 'AFP',
                        'name'   => 'Retraites : le gouvernement confirme l\'allongement à 43 ans, l\'Assemblée débat mardi',
                        'desc'   => 'Le Premier ministre a présenté ce lundi les grandes lignes de la réforme devant les partenaires sociaux. Le texte sera examiné en première lecture dès mardi. Calendrier serré, points d\'équilibre encore en négociation.',
                        'featured' => true,
                    ],
                    [
                        'source' => 'Le Figaro',
                        'name'   => 'Retraites : pourquoi l\'allongement de la durée de cotisation est devenu incontournable',
                        'desc'   => 'Vieillissement démographique, déficit croissant du régime général et pression des marchés obligataires : l\'éditorial défend une réforme présentée comme condition de la soutenabilité du système français.',
                    ],
                ],
            ],
            1002 => [
                'topic' => 'Accord climat de l\'Union européenne',
                'tint'  => '#1f4d3a',
                'category' => 'Uncategorized',
                'posts' => [
                    [
                        'source' => 'Mediapart',
                        'name'   => 'Climat : derrière l\'accord de Bruxelles, les ONG dénoncent des exemptions pour l\'industrie lourde',
                        'desc'   => 'L\'enquête révèle plus de 180 pages d\'annexes techniques ouvrant des dérogations au marché carbone pour l\'acier, le ciment et la chimie. Six ONG parlent d\'un « texte vidé de sa substance ».',
                    ],
                    [
                        'source' => 'France 24',
                        'name'   => 'L\'Union européenne adopte son nouveau paquet climat : −55 % d\'émissions en 2030',
                        'desc'   => 'Après 28 heures de négociations à Bruxelles, les vingt-sept États membres ont acté un nouveau mécanisme d\'ajustement carbone aux frontières et l\'extension de l\'ETS au transport maritime et à la construction.',
                        'featured' => true,
                    ],
                    [
                        'source' => 'Valeurs Actuelles',
                        'name'   => 'Accord climat européen : un coût de 400 milliards pour les ménages, alerte la CEA',
                        'desc'   => 'Le rapport du Conseil d\'analyse économique chiffre l\'impact du paquet sur le pouvoir d\'achat. Les filières automobile et logement seront les plus exposées. L\'éditorial réclame une « pause réglementaire ».',
                    ],
                ],
            ],
            1003 => [
                'topic' => 'Sahel — retrait des forces françaises',
                'tint'  => '#5a3b1f',
                'category' => 'Travel',
                'posts' => [
                    [
                        'source' => 'Libération',
                        'name'   => 'Sahel : l\'armée française quitte le Niger, Paris cherche un nouveau cadre',
                        'desc'   => 'Plus de mille soldats ont été repliés vers N\'Djamena. La tribune d\'une vingtaine de chercheurs africains appelle la France à sortir de la logique militaire et à soutenir les initiatives de la CEDEAO.',
                    ],
                    [
                        'source' => 'AFP',
                        'name'   => 'Dépêche : le dernier convoi militaire français a franchi la frontière nigéro-tchadienne',
                        'desc'   => 'Confirmation officielle du porte-parole de l\'état-major. Fin d\'une présence de onze ans. Les autorités militaires nigériennes saluent « un retrait ordonné ». Réactions diplomatiques attendues en fin de journée.',
                    ],
                    [
                        'source' => 'L\'Opinion',
                        'name'   => 'Sahel : le retrait français ouvre un boulevard à la Russie et à la Chine, analyse',
                        'desc'   => 'L\'éditorialiste décrypte les implications géopolitiques du désengagement. Groupe Wagner, contrats miniers, diplomatie parallèle : l\'analyse plaide pour un repositionnement stratégique.',
                    ],
                ],
            ],
        ];

        // ────────────────────────────────────────────────────────
        // Standalones
        // ────────────────────────────────────────────────────────
        $standalones = [
            [
                'source' => 'Financial Afrik',
                'category' => 'Business',
                'name' => 'Le Sénégal annonce un plan d\'investissement de 2 milliards dans les énergies renouvelables',
                'desc' => 'Le ministre de l\'Énergie a présenté une feuille de route sur cinq ans. Objectif : 40 % du mix électrique couvert par le solaire et l\'éolien d\'ici 2031. Financement international à hauteur de 60 %.',
                'tint' => '#1f3a4d',
            ],
            [
                'source' => 'Reuters',
                'category' => 'Business',
                'name' => 'Inflation : la BCE maintient ses taux directeurs à 3,25 % pour le troisième mois consécutif',
                'desc' => 'Christine Lagarde évoque une « trajectoire de désinflation en cours mais fragile ». L\'indice des prix à la consommation de la zone euro ressort à 2,4 % en glissement annuel en avril.',
                'tint' => '#2a2a3a',
            ],
            [
                'source' => 'BBC',
                'category' => 'Entertainment',
                'name' => 'Nouvelle vague d\'IA générative : Paris accueille le sommet AI Action 2026',
                'desc' => 'Quarante chefs d\'État, OpenAI, Anthropic, Mistral et les acteurs africains de l\'IA convergent à Bercy. Un « pacte de Paris sur l\'IA » pourrait encadrer la transparence des modèles de fondation.',
                'tint' => '#3a2a5a',
            ],
            [
                'source' => 'The Guardian',
                'category' => 'Uncategorized',
                'name' => 'Procès climat : condamnation historique d\'une major pétrolière par le tribunal de Paris',
                'desc' => 'Première décision française rendant une multinationale responsable de ses émissions indirectes (scope 3). Astreinte de 75 millions d\'euros par an jusqu\'à la mise en conformité de la trajectoire climatique.',
                'tint' => '#3a4d2a',
                'blindspot' => true,
            ],
            [
                'source' => 'Jeune Afrique',
                'category' => 'Travel',
                'name' => 'Côte d\'Ivoire : la présidentielle 2026 entre en campagne, trois candidats se détachent',
                'desc' => 'Le parti au pouvoir, le RHDP, aligne son poulain face à deux figures historiques de l\'opposition. Commission électorale indépendante sous tension. Observateurs de l\'UA déployés dès le 15 mai.',
                'tint' => '#4d3a1f',
            ],
            [
                'source' => 'Associated Press',
                'category' => 'Sport',
                'name' => 'Tennis : Djokovic atteint les demi-finales à Roland-Garros malgré une blessure',
                'desc' => 'Le Serbe s\'impose en cinq sets contre l\'Italien Musetti. Genou droit strappé, il affrontera Alcaraz vendredi. Première demi-finale pour le numéro 3 mondial depuis deux saisons.',
                'tint' => '#2a4d3a',
            ],
        ];

        // Purge previous grimba seed posts so re-runs stay idempotent.
        // Anything with our cluster IDs, or any grimba-seeds image path.
        Post::query()
            ->where(function ($q) {
                $q->whereIn('story_cluster_id', [1001, 1002, 1003])
                  ->orWhere('image', 'like', 'grimba-seeds/%');
            })
            ->delete();

        // ────────────────────────────────────────────────────────
        // Create clusters.
        // ────────────────────────────────────────────────────────
        foreach ($clusters as $clusterId => $cluster) {
            $categoryId = Category::query()->where('name', $cluster['category'])->value('id') ?? 1;

            foreach ($cluster['posts'] as $item) {
                $imgPath = 'grimba-seeds/cluster-' . $clusterId . '-' . \Illuminate\Support\Str::slug($item['source']) . '.svg';
                File::put(public_path('storage/' . $imgPath), $this->makeCoverSvg(
                    $cluster['topic'],
                    $item['source'],
                    $cluster['tint']
                ));

                $post = Post::query()->create([
                    'name'        => $item['name'],
                    'description' => $item['desc'],
                    'content'     => '<p>' . $item['desc'] . '</p><p>Article de démonstration — GrimbaNews. Le contenu complet sera fourni par la rédaction.</p>',
                    'image'       => $imgPath,
                    'status'      => 'published',
                    'is_featured' => $item['featured'] ?? false,
                    'author_id'   => $authorId,
                    'author_type' => \Botble\ACL\Models\User::class,
                    'views'       => rand(500, 5000),
                ]);

                // Trigger the saving hook: setting source_id copies
                // bias/ownership/credibility/source_name from news_sources.
                $post->story_cluster_id = $clusterId;
                $post->source_id        = $sources[$item['source']] ?? null;
                $post->save();

                $post->categories()->sync([$categoryId]);
                SlugHelper::createSlug($post);
            }
        }

        // ────────────────────────────────────────────────────────
        // Create standalones.
        // ────────────────────────────────────────────────────────
        foreach ($standalones as $item) {
            $categoryId = Category::query()->where('name', $item['category'])->value('id') ?? 1;
            $imgPath = 'grimba-seeds/stand-' . \Illuminate\Support\Str::slug($item['source'] . '-' . mb_substr($item['name'], 0, 30)) . '.svg';
            File::put(public_path('storage/' . $imgPath), $this->makeCoverSvg(
                $item['name'],
                $item['source'],
                $item['tint']
            ));

            $post = Post::query()->create([
                'name'        => $item['name'],
                'description' => $item['desc'],
                'content'     => '<p>' . $item['desc'] . '</p><p>Article de démonstration — GrimbaNews.</p>',
                'image'       => $imgPath,
                'status'      => 'published',
                'is_featured' => false,
                'author_id'   => $authorId,
                'author_type' => \Botble\ACL\Models\User::class,
                'views'       => rand(200, 2500),
            ]);

            $post->source_id   = $sources[$item['source']] ?? null;
            $post->is_blindspot = $item['blindspot'] ?? false;
            $post->save();

            $post->categories()->sync([$categoryId]);
            SlugHelper::createSlug($post);
        }
    }

    /**
     * Generate a branded SVG cover: dark tint gradient, mono kicker,
     * Fraunces italic title. Keeps us content-agnostic until a real
     * editor uploads photos.
     */
    /**
     * Generate a pure-texture placeholder cover: radial glow + grain +
     * newsprint rules + a tiny monogram. No baked-in title — the
     * partial always overlays headline text, so we only need atmosphere.
     */
    protected function makeCoverSvg(string $title, string $kicker, string $tint): string
    {
        // Derive a seed offset from the title for variety between covers.
        $seed = crc32($title);
        $cx = 20 + ($seed % 60);
        $cy = 20 + (($seed >> 4) % 60);

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 800" preserveAspectRatio="xMidYMid slice">
  <defs>
    <radialGradient id="glow" cx="{$cx}%" cy="{$cy}%" r="75%">
      <stop offset="0%" stop-color="{$tint}" stop-opacity="0.95"/>
      <stop offset="65%" stop-color="#1a1713" stop-opacity="1"/>
      <stop offset="100%" stop-color="#0a0806" stop-opacity="1"/>
    </radialGradient>
    <pattern id="grain" patternUnits="userSpaceOnUse" width="3" height="3">
      <circle cx="0.8" cy="0.8" r="0.5" fill="#f6f1e8" opacity="0.045"/>
    </pattern>
    <pattern id="rules" patternUnits="userSpaceOnUse" width="1200" height="44">
      <line x1="0" y1="0" x2="1200" y2="0" stroke="#f6f1e8" stroke-opacity="0.025" stroke-width="1"/>
    </pattern>
  </defs>
  <rect width="1200" height="800" fill="url(#glow)"/>
  <rect width="1200" height="800" fill="url(#grain)"/>
  <rect width="1200" height="800" fill="url(#rules)"/>
  <g fill="#f6f1e8" opacity="0.18">
    <circle cx="1100" cy="720" r="2"/>
    <circle cx="1120" cy="720" r="2"/>
    <circle cx="1140" cy="720" r="2"/>
  </g>
</svg>
SVG;
    }
}
