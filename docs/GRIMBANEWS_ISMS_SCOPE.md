# GrimbaNews — ISO 27001 ISMS Scope Statement (v0 draft)

**Status:** scope statement v0 (draft for ISMS lead-auditor review)
**Owner:** Sara Chen (CISO)
**Walks:** Mythos S1821 (ISO 27001 ISMS scope statement) deferred → partial
**Gating dependency:** No ISO 27001 lead auditor engaged (S1821-S1829 band). This document is the **v0 scope statement** that the auditor will accept, push back on, or trim during the certification kickoff.

## Why this exists

S1821 was honest-deferred as "operator-side document; Sara-Chen-owned." Drafting the scope statement is exactly the operator-side work that doesn't need an auditor — the auditor reviews and signs off on what the org states is in scope. This doc states what GrimbaNews considers in scope for an ISMS, enumerating the assets, locations, processes, and people. It's the precondition for the Statement of Applicability (S1822) and the risk-treatment plan (S1823).

## 1. Organization name

**Iboga Ventures** (operator: Vader), specifically the **GrimbaNews** product line within the Iboga product portfolio.

## 2. ISMS scope statement

The ISMS scope covers the development, deployment, and operation of the **GrimbaNews** news-aggregation platform — including its public reader-facing surfaces (grimbanews.com), its administrative surfaces (`/admin/grimba/*`), its scheduled ingestion + translation + summarization pipelines, its data stores (live SQLite + backups), and the operator and on-call personnel who administer the platform.

The ISMS *excludes* other Iboga product lines (NobuTrust, Incognito, LeafRelay, Yabacademy, BraightLegal, ONE CRM, GrimbaCare) unless and until they are individually scoped-in by a future scope-extension decision. Each of those products may pursue its own ISMS or share this one's policies under separate scope statements.

## 3. In-scope assets

### 3.1 Digital assets (information)

- **Live database** — SQLite at `database/sqlite/database.sqlite` containing: `posts`, `news_sources`, `members`, `subscribers`, `vault_events`, `saved_searches`, `grimba_automation_runs`, `grimba_live_news_provider_runs`, ~40 supporting tables.
- **Database backups** — `database/backups/*.sqlite` (rotated per `GrimbaDatabaseBackups`).
- **Storage volume** — `storage/app/` (image-proxy cache, release-evidence, vault-events archive).
- **Logs** — `storage/logs/laravel.log` (and rotated archives).
- **Configuration** — `.env` (secrets), `config/*` (non-secret config).
- **Source code** — `darkvaderfr/grimbanews` private GitHub mirror + local working tree at `/Users/vb/GrimbaNews/`.
- **Documentation** — `docs/` (this file lives here).

### 3.2 Physical assets

- VPS host (provider: see `docs/GRIMBANEWS_VENDOR_REGISTER.md` vendor #9) — single node, single region.
- Operator workstations (Vader laptop + any future Iboga ops team workstations) — to the extent they hold cached `.env` or local working trees.

### 3.3 People

- Sara Chen — CISO (ISMS owner).
- Jacob Lee — DevOps (operational controls).
- Hannah Kim — Platform (operational controls).
- Larry Ellison — VP DBA (DB-class controls).
- Lucy Leai — Strategy (content / policy direction).
- Steve Jobs — CPO (product / design).
- Vader — operator / founder.
- Zenkai — founder-ops signoff.

(Real names per `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`.)

### 3.4 Processes

- Source ingestion (RSS poller, NewsAPI fetch, newsdata.io fetch, live-news provider rotation).
- Article deduplication + clustering.
- Translation (FR↔EN, future locales).
- NobuAI summarization.
- Reader publication (homepage + categories + dossier + search).
- Member account management (Botble member plugin).
- Subscriber + vault digest management.
- Backups + restore.
- Security incident response.
- Change management (git + deploy).

## 4. Out-of-scope

- Other Iboga product lines (separate scope statements).
- Third-party SaaS internals (we contract them via DPAs per S1873; their internal ISMS is their concern).
- Operator personal devices not used for GrimbaNews administration.
- Vader's other projects on the same workstation (segregated by working tree).

## 5. Scope boundaries — interfaces

The ISMS interfaces with:

- VPS provider (vendor #9) — infrastructure boundary.
- DNS provider (Namecheap, vendor #8) — name resolution boundary.
- Source publishers (news outlets via RSS) — input boundary.
- LLM/translation providers (vendors #2-#6 per register) — content-processing boundary.
- Email-delivery vendor (LeafRelay, vendor #7) — output boundary.

Each interface will have a defined control set in the Statement of Applicability (S1822) and a vendor-risk assessment per `docs/GRIMBANEWS_VENDOR_REGISTER.md`.

## 6. Justification for exclusions (ISO 27001 Annex A controls deemed N/A)

- **A.14.3 Test data** — limited applicability; we use anonymized fixtures, no production-data copying into dev.
- **A.11.1 Physical security perimeter** — delegated to VPS provider per their attested controls; we do not operate a physical data center.
- **A.18.1.5 Cryptographic controls — regulation of** — limited applicability; no proprietary cryptography developed.

Justifications above are draft; auditor will challenge them at kickoff per ISO 27001 Clause 4.3 + 6.1.3.

## 7. ISMS owner + approvers

| Role | Name | Responsibility |
|---|---|---|
| ISMS owner | Sara Chen | Day-to-day ISMS operation, Statement of Applicability owner |
| Risk owner | Sara Chen + Larry Ellison | Risk-register cadence per S1837 |
| Top management | Vader | Final approval; resource allocation; management review per S1828 |
| Audit committee | Zen + Echo + Mnemo audit panel | Engineering-level audit (per `feedback_dream_team_audit.md`) |
| Internal audit | TBD per S1881 charter | Internal audit per ISO 27001 Clause 9.2 |
| External audit | TBD per S1891 | Lead auditor for certification |

## 8. Version + change log

- **v0 (2026-05-22)** — initial draft. Wave RRRRRRRRRR. Not yet reviewed by lead auditor.

Next versions to incorporate auditor feedback at kickoff.

## Cross-references

- Master plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1821 row; gates for S1822-S1829)
- Sister docs: `docs/GRIMBANEWS_VENDOR_REGISTER.md`, `docs/GRIMBANEWS_RISK_ASSESSMENT_METHODOLOGY.md`, `docs/GRIMBANEWS_POLICY_LIBRARY_INDEX.md`, `docs/GRIMBANEWS_SOC2_CONTROL_MAP.md`, `docs/GRIMBANEWS_INTERNAL_AUDIT_CHARTER.md`
- Iboga roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
- Database backup primitive: `app/Support/GrimbaDatabaseBackups.php`
- Information-asset enumeration (S1824 surrogate): `database/sqlite/database.sqlite`, `database/backups/`, `.env`, `storage/`
