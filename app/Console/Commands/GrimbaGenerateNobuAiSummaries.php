<?php

namespace App\Console\Commands;

use App\Services\GrimbaNobuAi;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class GrimbaGenerateNobuAiSummaries extends Command
{
    protected $signature = 'grimba:nobuai-summaries
        {--limit=20 : Max story clusters to process}
        {--cluster= : Process one story_cluster_id only}
        {--force : Regenerate summaries that already exist}
        {--stale : Regenerate only summaries older than their latest article update}
        {--dry-run : Show queued clusters without calling providers or writing}';

    protected $description = 'Generate cluster-level NobuAI summaries from multi-source coverage and persist them onto posts.';

    public function handle(GrimbaNobuAi $nobuAi): int
    {
        if (! Schema::hasColumn('posts', 'summary_nobuai')) {
            $this->error('posts.summary_nobuai is missing. Run migrations before generating NobuAI summaries.');
            return self::FAILURE;
        }

        if (! $nobuAi->enabled()) {
            $this->warn('No NobuAI LLM provider keys configured. Add keys in /admin/grimba/translation first.');
            return self::SUCCESS;
        }

        $limit = max(1, (int) $this->option('limit'));
        $clusterId = $this->option('cluster') ? (int) $this->option('cluster') : null;
        $force = (bool) $this->option('force');
        $staleOnly = (bool) $this->option('stale');
        $dryRun = (bool) $this->option('dry-run');

        $query = DB::table('posts')
            ->where('status', 'published')
            ->whereNotNull('story_cluster_id')
            ->selectRaw("
                story_cluster_id,
                COUNT(*) as post_count,
                MAX(updated_at) as latest_at,
                MAX(summary_generated_at) as summary_generated_at,
                MAX(CASE WHEN summary_nobuai IS NOT NULL AND summary_nobuai != '' THEN 1 ELSE 0 END) as has_summary
            ")
            ->groupBy('story_cluster_id')
            ->havingRaw('COUNT(*) >= 2')
            ->orderByDesc('latest_at')
            ->limit($limit);

        if ($clusterId) {
            $query->where('story_cluster_id', $clusterId);
        }

        if ($staleOnly) {
            $query
                ->havingRaw("MAX(CASE WHEN summary_nobuai IS NOT NULL AND summary_nobuai != '' THEN 1 ELSE 0 END) = 1")
                ->havingRaw('MAX(summary_generated_at) IS NOT NULL')
                ->havingRaw('MAX(updated_at) > MAX(summary_generated_at)');
        } elseif (! $force) {
            $query->where(function ($q): void {
                $q->whereNull('summary_nobuai')
                    ->orWhere('summary_nobuai', '');
            });
        }

        $clusters = $query->get();

        if ($clusters->isEmpty()) {
            $this->info('No clusters queued for NobuAI summaries.');
            return self::SUCCESS;
        }

        $this->info(sprintf('NobuAI providers available: %s', implode(', ', $nobuAi->configuredDrivers())));
        $this->info(sprintf('%d cluster(s) queued%s.', $clusters->count(), $staleOnly ? ' for stale refresh' : ''));

        $ok = 0;
        $fail = 0;

        foreach ($clusters as $cluster) {
            $posts = DB::table('posts')
                ->where('status', 'published')
                ->where('story_cluster_id', $cluster->story_cluster_id)
                ->orderByRaw("CASE bias_rating WHEN 'left' THEN 1 WHEN 'center' THEN 2 WHEN 'right' THEN 3 ELSE 4 END")
                ->orderByDesc('updated_at')
                ->get([
                    'id',
                    'name',
                    'translated_name',
                    'description',
                    'translated_description',
                    'source_name',
                    'bias_rating',
                ]);

            $topic = (string) ($posts->first()->translated_name ?: $posts->first()->name ?: 'Dossier #' . $cluster->story_cluster_id);
            $this->line(sprintf('  #%d %s (%d articles)', $cluster->story_cluster_id, Str::limit($topic, 80), $posts->count()));

            if ($dryRun) {
                continue;
            }

            $result = $nobuAi->complete($this->buildPrompt($topic, $posts), $this->systemPrompt($nobuAi));

            if (! $result || trim($result['text']) === '') {
                $this->warn('    failed: no provider returned a summary');
                $fail++;
                continue;
            }

            $summary = $this->normalizeSummary($result['text']);
            if ($summary === '') {
                $this->warn('    failed: empty normalized summary');
                $fail++;
                continue;
            }

            // S-LANG-08 (Vader 2026-05-17) — tag the summary's locale.
            // The current prompt is hardcoded French (see systemPrompt
            // + buildPrompt below); record that explicitly so a future
            // cluster-aware generator can produce EN summaries without
            // colliding with the FR cache.
            //
            // Zen audit fix 2026-05-17: only set the locale tag to 'fr'
            // when it's currently NULL or already 'fr'. Future EN-aware
            // generators will write 'en' once; this FR pass must not
            // clobber it. Guard via two passes: first an UPDATE of the
            // summary fields scoped to those rows, then a separate
            // UPDATE of the locale tag scoped to the safe subset.
            DB::table('posts')
                ->where('story_cluster_id', $cluster->story_cluster_id)
                ->update([
                    'summary_nobuai' => $summary,
                    'summary_generated_at' => now(),
                    'summary_driver' => $result['driver'],
                    'updated_at' => now(),
                ]);

            if (Schema::hasColumn('posts', 'summary_nobuai_locale')) {
                DB::table('posts')
                    ->where('story_cluster_id', $cluster->story_cluster_id)
                    ->where(function ($q): void {
                        $q->whereNull('summary_nobuai_locale')
                          ->orWhere('summary_nobuai_locale', '')
                          ->orWhere('summary_nobuai_locale', 'fr');
                    })
                    ->update(['summary_nobuai_locale' => 'fr']);
            }

            $this->line(sprintf('    wrote %d lines via %s', substr_count($summary, "\n") + 1, $result['driver']));
            $ok++;
        }

        $this->info(sprintf('Done. ok=%d fail=%d%s', $ok, $fail, $dryRun ? ' (dry-run)' : ''));

        return $fail > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function systemPrompt(GrimbaNobuAi $nobuAi): string
    {
        return $nobuAi->editorialSystemPrompt(
            'Write evidence-bound editorial synthesis in French from an African and Pan-African analytical perspective. '
            . 'Use only the supplied article metadata. Return labeled lines that help readers understand framing differences across sources.'
        );
    }

    private function buildPrompt(string $topic, iterable $posts): string
    {
        $lines = [
            'Sujet: ' . $topic,
            '',
            'Répartition des sources:',
            '- gauche: ' . collect($posts)->where('bias_rating', 'left')->count(),
            '- centre: ' . collect($posts)->where('bias_rating', 'center')->count(),
            '- droite: ' . collect($posts)->where('bias_rating', 'right')->count(),
            '- non classé: ' . collect($posts)->reject(fn ($post) => in_array($post->bias_rating, ['left', 'center', 'right'], true))->count(),
            '',
            'Articles:',
        ];

        foreach ($posts as $post) {
            $title = trim((string) ($post->translated_name ?: $post->name));
            $description = trim(strip_tags((string) ($post->translated_description ?: $post->description)));

            $lines[] = sprintf(
                '- Source: %s | angle: %s | titre: %s | résumé: %s',
                $post->source_name ?: 'Source inconnue',
                $post->bias_rating ?: 'unknown',
                Str::limit($title, 180),
                Str::limit($description, 360)
            );
        }

        $lines[] = '';
        $lines[] = 'Tâche: Produis 4 à 6 lignes en français, format strict "Libellé: texte".';
        $lines[] = 'Libellés autorisés: Ce qui est confirmé, Perspective africaine, Ce que dit la gauche, Ce que dit le centre, Ce que dit la droite, Angle mort, Pourquoi ça compte.';
        $lines[] = 'Inclure seulement les lignes supportées par les articles. Si un camp manque, utilise Angle mort pour le signaler.';
        $lines[] = 'Ne jamais écrire "Ce que dit la gauche/le centre/la droite: Angle mort"; cette information doit être une ligne "Angle mort: ...".';
        $lines[] = 'Chaque texte doit rester court, concret et vérifiable. Retourne seulement les lignes.';

        return implode("\n", $lines);
    }

    private function normalizeSummary(string $text): string
    {
        $lines = preg_split("/\r\n|\n|\r/", trim($text)) ?: [];

        $normalized = collect($lines)
            ->map(static function (string $line): string {
                $line = trim(preg_replace('/^\s*[-*•\d\.)]+\s*/u', '', $line) ?? '');

                if (preg_match('/^Ce que dit (la gauche|le centre|la droite)\s*:\s*(angle mort|aucun|aucune|absent|absente|non représenté|non représentée)\.?$/iu', $line, $matches)) {
                    $side = match (mb_strtolower($matches[1])) {
                        'la gauche' => 'à gauche',
                        'le centre' => 'au centre',
                        default => 'à droite',
                    };

                    return "Angle mort: Aucun article classé {$side} ne figure dans ce dossier.";
                }

                if (preg_match('/^([^:]{2,80})\s*:\s*(.+)$/u', $line, $matches)) {
                    $label = self::canonicalSummaryLabel(trim($matches[1]));
                    if ($label !== null) {
                        return $label . ': ' . trim($matches[2]);
                    }
                }

                return $line;
            })
            ->filter(static fn (string $line): bool => $line !== '')
            ->unique(static fn (string $line): string => mb_strtolower($line))
            ->values();

        $selected = $normalized->take(5);
        $perspective = $normalized->first(static fn (string $line): bool => str_starts_with($line, 'Perspective africaine:'));

        if ($perspective !== null && ! $selected->contains($perspective)) {
            $selected = $selected->take(4)->push($perspective);
        }

        return $selected
            ->implode("\n");
    }

    private static function canonicalSummaryLabel(string $label): ?string
    {
        $folded = Str::of($label)
            ->lower()
            ->ascii()
            ->replaceMatches('/\s+/u', ' ')
            ->trim()
            ->toString();

        return match ($folded) {
            'ce qui est confirme', 'faits confirmes', 'confirme' => 'Ce qui est confirmé',
            'perspective africaine', 'angle africain', 'lecture africaine' => 'Perspective africaine',
            'ce que dit la gauche', 'gauche' => 'Ce que dit la gauche',
            'ce que dit le centre', 'centre' => 'Ce que dit le centre',
            'ce que dit la droite', 'droite' => 'Ce que dit la droite',
            'angle mort', 'angles morts' => 'Angle mort',
            'pourquoi ca compte', 'pourquoi cela compte', 'importance' => 'Pourquoi ça compte',
            default => null,
        };
    }
}
