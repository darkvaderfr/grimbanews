# GrimbaNews — Ombudsman Complaint Public Log Plan

**Status:** plan v0
**Owner:** Maya Patel (Compliance) + Michael O'Connor (Tech Writer) + Lucy Leai (Strategy)
**Walks:** Mythos S2030 (complaint workflow — anonymized-but-public log) deferred → partial
**Gating dependency:** S2029 complaint workflow shipped + per-case anonymization SOP.

## Why this exists

Without a public log, the ombudsman role is opaque. Public anonymized logs are how outlets like ProPublica + The Guardian's readers' editor demonstrate the function is real.

## v1 schema

```sql
CREATE TABLE ombudsman_cases (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  case_number VARCHAR(16) UNIQUE NOT NULL,    -- e.g. OMB-2026-0042
  received_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  category ENUM('factual-error', 'bias-concern', 'source-credibility', 'process', 'other') NOT NULL,
  summary TEXT NOT NULL,                       -- anonymized summary
  resolution TEXT NULL,                        -- public-facing resolution
  status ENUM('received', 'investigating', 'resolved', 'no-action', 'escalated') DEFAULT 'received',
  resolved_at TIMESTAMP NULL,
  public BOOLEAN DEFAULT FALSE                 -- ombudsman opts in to public after anonymization review
);
```

## Public surface

- Page: `/ombudsman/cas`.
- Filterable by category + year.
- Each case: number, category, summary, resolution, dates.
- No reader identification, no source identification unless the source consented.

## Anonymization SOP

- Maya + ombudsman review each case before public-log opt-in.
- Removal of: names, IPs, identifying article-detail combinations.
- 30-day cooling-off before any case is published.

## Cross-references

Master plan: S2030. Sister: S2021 (charter), S2025 (anon tips), S2031 (annual ombudsman report), S2034 (correction authority).
