# GrimbaNews — Per-Locale Moderation Policy

**Status:** plan v0 (single global moderation posture; no locale-specific carve-outs)
**Owner:** Sara Chen (CISO) on compliance + Lucy Leai (CEO) on editorial framing + per-locale editor TBD
**Walks:** Mythos S1145 (Per-locale moderation policy) deferred → partial
**Gating dependency:** Editorial workflow band (S1291-S1300) + per-locale editor seats (S1401) + counsel review per jurisdiction.

## Why this exists

S1145 acknowledges that moderation thresholds and protected categories differ by jurisdiction. Holocaust denial is criminal in DE/FR but legal in US. Blasphemy is criminal in some MENA jurisdictions. Today GrimbaNews applies a single global posture inherited from the EN/FR-centric source roster.

## Today's surrogate

- **`news_sources.factuality_score`** + `credibility_score` filter at ingest (single global threshold).
- **`GrimbaArticleDedupe`** drops obvious junk regardless of locale.

## Per-locale carve-outs (target)

| Locale | Hard prohibitions (counsel-defined) | Editorial defaults |
|---|---|---|
| fr | Holocaust denial (Gayssot Act 1990), incitement | Stricter on AdSense brand-safety |
| de | Holocaust denial (StGB §130), Nazi symbols | Same as FR + Bundesprüfstelle deference |
| en (UK) | Inciting racial hatred (Public Order Act 1986) | Defamation post-Brexit |
| en (US) | First Amendment defaults — minimal hard prohibitions | Brand-safety drives moderation |
| ar | Per-jurisdiction varies (KSA / UAE / EG) | Religious-content discretion |
| he | Incitement (Israeli Penal Code §144A) | Per-jurisdiction security review |

## Process

- `moderation_locale_rules` table (deferred) — `{locale, rule_key, action: block|flag|review, counsel_source_url}`.
- Per-locale editor (S1401) flags violations.
- Sara Chen reviews quarterly.

## Cross-references

- Master plan: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1145)
- Sister docs: `docs/GRIMBANEWS_CROSS_LOCALE_DISPUTE_ROUTING.md`, `docs/GRIMBANEWS_COMPLAINT_TRIAGE_RUBRIC_PLAN.md`
- Existing infra: `database/seeders/RssFeedsSeeder.php`
- Roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
