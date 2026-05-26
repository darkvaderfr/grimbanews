# GrimbaNews — A/B Harness Design

**Status:** design v0 (no experiments / experiment_assignments tables; cookie patterns proven)
**Owner:** Jacob Lee (DevOps) on infra + Liam Smith (PM) on experiment lifecycle + Steve Jobs (CPO) on UX guardrails + Sara Chen (CISO) on privacy
**Walks:** Mythos S1721 (experiment-registry schema), S1722 (traffic-splitter), S1723 (Blade variant hook), S1724 (assignment cookie) deferred → partial
**Gating dependency:** First experiment to ship (operator-side pick) + observability surface for outcome events. Design itself is operator-side.

## Why this exists

S1721-S1730 form the A/B harness band. None are blocked by external infra — they're a coherent engineering scope that hasn't been picked yet. This document defines the design so the moment Liam picks an experiment to ship, the harness lands as a straight build.

## Today's state

- **No `experiments` table.**
- **No `experiment_assignments` table.**
- **Cookie patterns proven** by existing `grimba_region`, `grimba_local_*`, `grimba_for_you_recent` cookies.
- **`vault_events` ledger** is the cookie-only privacy-safe event ledger pattern (per S1725 partial).
- **A/B-blocked deferrals:** S1284 (newsletter subject A/B), S1588 (subject A/B), S1589 (send-time A/B), S1346 (rank A/B), S1729 (experiment retrospective).

## Schema

```sql
CREATE TABLE experiments (
  id BIGINT PRIMARY KEY AUTOINCREMENT,
  slug VARCHAR(64) NOT NULL UNIQUE,            -- 'search-v2-rank-blend', 'newsletter-subject-q3-2026'
  name VARCHAR(128) NOT NULL,
  hypothesis TEXT NOT NULL,
  surface VARCHAR(64) NOT NULL,                -- 'search' / 'pour-vous' / 'newsletter-subject' / 'home-rail'
  status ENUM('draft','running','paused','completed','aborted') DEFAULT 'draft',
  variants JSON NOT NULL,                      -- [{slug:'control',weight:50},{slug:'v2',weight:50}]
  primary_metric VARCHAR(64) NOT NULL,         -- 'ctr', 'session_length', 'save_rate'
  secondary_metrics JSON DEFAULT '[]',
  guardrail_metrics JSON DEFAULT '[]',         -- ['bias_spread_floor','diversity_floor']
  min_sample_size INT NOT NULL,
  max_runtime_days INT DEFAULT 30,
  started_at TIMESTAMP NULL,
  ended_at TIMESTAMP NULL,
  winner_variant VARCHAR(64) NULL,
  retro_doc_path VARCHAR(255) NULL,
  owner_member_id BIGINT NOT NULL,
  created_at TIMESTAMP,
  updated_at TIMESTAMP,
  INDEX (status, surface),
  INDEX (started_at, ended_at)
);

CREATE TABLE experiment_assignments (
  id BIGINT PRIMARY KEY AUTOINCREMENT,
  experiment_id BIGINT NOT NULL,
  visitor_id_hash VARCHAR(64) NOT NULL,        -- sha256(cookie + salt); not raw cookie
  variant_slug VARCHAR(64) NOT NULL,
  assigned_at TIMESTAMP NOT NULL,
  first_outcome_at TIMESTAMP NULL,
  UNIQUE (experiment_id, visitor_id_hash),
  INDEX (experiment_id, variant_slug)
);

CREATE TABLE experiment_outcomes (
  id BIGINT PRIMARY KEY AUTOINCREMENT,
  experiment_id BIGINT NOT NULL,
  visitor_id_hash VARCHAR(64) NOT NULL,
  variant_slug VARCHAR(64) NOT NULL,
  metric VARCHAR(64) NOT NULL,                 -- 'ctr', 'save_rate', 'bias_spread_breach'
  metric_value FLOAT NOT NULL,
  occurred_at TIMESTAMP NOT NULL,
  context JSON DEFAULT '{}',
  INDEX (experiment_id, variant_slug, metric),
  INDEX (occurred_at)
);
```

## Traffic-splitter middleware (S1722)

`app/Http/Middleware/GrimbaExperimentAssignment.php` (new):

```php
public function handle(Request $request, Closure $next)
{
    $cookie = $request->cookie('grimba_exp_id') ?? $this->mintCookie();
    $hash = hash_hmac('sha256', $cookie, config('app.key'));
    
    foreach (Cache::remember('experiments.active', 60, fn() => Experiment::active()->get()) as $exp) {
        if (!$exp->appliesToRoute($request)) continue;
        $assignment = $this->assignmentFor($exp, $hash);
        $request->attributes->set("exp.{$exp->slug}", $assignment->variant_slug);
    }
    
    $response = $next($request);
    return $response->withCookie(cookie('grimba_exp_id', $cookie, 60*24*365));
}
```

**Deterministic assignment** — same cookie always gets the same variant for the same experiment. Hash mod weight buckets.

## Blade variant hook (S1723)

```blade
@experiment('search-v2-rank-blend')
  @variant('control')
    @include('partials.search.lexical-rank')
  @endvariant
  @variant('v2')
    @include('partials.search.semantic-rank')
  @endvariant
@endexperiment
```

Compiles to:
```php
<?php $__variant = request()->attributes->get('exp.search-v2-rank-blend', 'control'); ?>
<?php if ($__variant === 'control'): ?>
  @include('partials.search.lexical-rank')
<?php elseif ($__variant === 'v2'): ?>
  @include('partials.search.semantic-rank')
<?php endif; ?>
```

## Assignment cookie (S1724)

- **Cookie name:** `grimba_exp_id` (single cookie, not per-experiment).
- **Value:** opaque 16-char base62 string minted once per visitor.
- **TTL:** 1 year.
- **Path:** `/`.
- **Cookie footprint disclosed** in `docs/GRIMBANEWS_CONSENT_LOG_DESIGN.md` (next refresh) under "experiment assignment (functional, no behavioral profiling)".
- **Same-site:** Lax.
- **Secure:** true.
- **HttpOnly:** true.

Per-experiment assignment derived from cookie + experiment slug + secret key (deterministic, server-side).

## Outcome event log (S1725 ship)

`App\Support\GrimbaExperimentOutcomes::record()`:

```php
public static function record(string $expSlug, string $metric, float $value, array $context = []): void
{
    $exp = Experiment::active()->where('slug', $expSlug)->first();
    if (!$exp) return;
    $variant = request()->attributes->get("exp.{$expSlug}");
    if (!$variant) return;
    
    ExperimentOutcome::create([
        'experiment_id' => $exp->id,
        'visitor_id_hash' => $this->visitorHash(),
        'variant_slug' => $variant,
        'metric' => $metric,
        'metric_value' => $value,
        'occurred_at' => now(),
        'context' => $context,
    ]);
}
```

Called at: click events (CTR), save events (save_rate), session-end (session_length), guardrail violations (bias_spread_breach).

## Sequential testing / stop-early (S1726)

- **Bayesian sequential approach** — stop when posterior probability of winner > 95%.
- **OR fixed-window approach** — stop at `max_runtime_days` regardless.
- **Guardrail breach** — auto-pause experiment if guardrail metric breaches floor (e.g., diversity floor in personalization, bias spread floor in search rank).

Library: `tbats/bayesian-ab-testing` or custom. Liam picks.

## Admin console (S1727)

`/admin/grimba/experiments`:

- List experiments (active, draft, completed) with status, variants, primary metric trend.
- Per-experiment detail page:
  - Live metrics per variant (CTR, save_rate, etc.).
  - Sample size + statistical power.
  - Guardrail status.
  - Pause / resume / abort buttons.
  - "Declare winner + promote to 100%" button (with confirmation).
- Per-experiment retrospective writer (S1729 dependency).

## Feature-flag rollout (S1728)

- Experiment promoted to winner = flag flipped to 100%.
- Loser variant retained behind flag for 30 days (rollback safety).
- Feature flag inspectable at `/admin/grimba/feature-flags`.

## Experiment retrospective template (S1729)

`docs/experiments/{slug}-{date}.md`:

```markdown
# Experiment: {Name}

**Slug:** {slug}
**Surface:** {surface}
**Owner:** {member}
**Duration:** {start} - {end}
**Sample size:** {n}

## Hypothesis
{hypothesis}

## Variants
- Control: {description}
- v2: {description}

## Results

| Metric | Control | v2 | Lift | p-value |
|---|---|---|---|---|
| {primary_metric} | x | y | +z% | 0.0X |

## Guardrails
- {floor1}: {pass/fail}
- {floor2}: {pass/fail}

## Decision
{Promote v2 to 100% / Stick with control / Re-run with different cohort}

## Learnings
{Bullet list.}

## Next experiments suggested
{Bullet list.}
```

## Privacy posture

- **No raw IP, no member PII** in any experiment table.
- **`visitor_id_hash`** = sha256(cookie + per-experiment salt + app-key).
- **Cookie is opaque** — no user-identifiable encoding.
- **Outcome events purged after 12 months** (retention).
- **GDPR data-export** honors cookie-tied outcome history per member preference.

## Engineering effort estimate

- Schema + migrations: 1 sprint.
- Traffic-splitter middleware + cookie: 1 sprint.
- Blade variant hook: 1 sprint.
- Outcome event ledger + helpers: 1 sprint.
- Admin console (CRUD + dashboards): 4 sprints.
- Sequential-test stats library: 2 sprints (Liam picks lib vs custom).
- Retrospective writer + template: 1 sprint.
- Feature-flag rollout surface: 1 sprint.
- Tests + privacy review: 2 sprints.
- **Full ship: ~14 sprints.**

## Cross-references

- Master plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1721-S1730)
- Sister docs: `docs/GRIMBANEWS_SEARCH_V2_LAUNCH_PLAYBOOK.md`, `docs/GRIMBANEWS_PERSONALIZATION_V2_LAUNCH_PLAYBOOK.md`, `docs/GRIMBANEWS_PER_REGION_DAILY_DIGEST_CADENCE.md`, `docs/GRIMBANEWS_CONSENT_LOG_DESIGN.md`, `docs/GRIMBANEWS_GDPR_ROPA.md`
- Cookie patterns proven by: `app/Http/Middleware/GrimbaLocaleEnforce.php`, `app/Support/GrimbaVault.php`
- Iboga roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
