# GrimbaNews — Prompt A/B Harness Plan

**Status:** plan v0 (no A/B engine wired for prompts)
**Owner:** Rajesh Kumar (backend) + Lisa Nguyen (data)
**Walks:** Mythos S1073 (prompt-A/B harness) deferred → partial
**Gating dependency:** A/B harness for reader cohorts shipped (`docs/GRIMBANEWS_AB_HARNESS_DESIGN.md` Wave LLL).

## Why this exists

Prompts are infrastructure. Today they live as PHP constants in services — change requires deploy, no rollback path without revert. Operator can't test variant prompts in flight. Prompt A/B harness fixes this.

## v1 design

New table `nobuai_prompt_variants`:

```
id | prompt_key (e.g. 'summary.v1') | template (string) | active (bool) | weight (int, default 100) | created_at
```

`GrimbaNobuAi::renderPrompt($key, $context)` picks a variant weighted by `weight`. Defaults to the highest-weight `active=true` variant.

For per-call A/B: pass `$variant_hint = hash($post_id) % 100` to pin variant per-post.

## Variant comparison

Per-variant metrics tracked:
- Output length (median + p95)
- Editor review approval rate (sample)
- Per-variant failure rate (parsing errors, empty response)
- Per-variant cost (token count)

## Rollback path

Operator updates `active=false` on the bad variant; harness falls back to the next-highest-weight.

## Schema (gates on Vader migration approval)

```
nobuai_prompt_variants:
  id | prompt_key | template | active | weight | created_at | updated_at
nobuai_prompt_decisions:
  id | post_id | prompt_key | variant_id | output | latency_ms | cost_usd | decided_at
```

## Editor review surface

Admin-only `/admin/grimba/prompt-variants` page:
- Active variants per key
- Per-variant sample outputs
- Per-variant quality rating (editor-set)
- Quick toggle active/inactive

## Cross-references

Master plan: S1073, S1074, S1075. Sister: `docs/GRIMBANEWS_AB_HARNESS_DESIGN.md`, `docs/GRIMBANEWS_AB_PERSONALIZATION_FLEET_DESIGN.md`.
Code: `app/Services/GrimbaNobuAi.php`.
