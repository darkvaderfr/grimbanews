# GrimbaNews — CSP Violation Reporting Design

**Status:** plan v0 (CSP shipped, report-uri not wired)
**Owner:** Sara Chen (CISO) + Jacob Lee (DevOps)
**Walks:** Mythos S1084 (CSP violation reporting design) deferred → partial
**Gating dependency:** report-uri.com (or self-hosted collector) + alert channel.

(Sister doc to existing GRIMBANEWS_CSP_VIOLATION_REPORTING_PLAN.md — this one captures the data-flow design while the sister captures the operator wiring.)

## Data flow

```
Browser → CSP violation
   ↓
POST <report-uri or self-hosted /csp-report>
   ↓
JSON body: { document-uri, blocked-uri, violated-directive, source-file, line-number }
   ↓
Server stores in csp_violations table
   ↓
Daily aggregator emits report (top-10 blocked URIs)
   ↓
Cockpit `/admin/grimba/csp-violations` page renders
   ↓
Spike alert (>100/hr) via `slack_webhook` job key
```

## Schema (gates on Vader migration approval)

```
csp_violations:
  id INTEGER PRIMARY KEY,
  document_uri TEXT,
  blocked_uri TEXT,
  violated_directive VARCHAR(64),
  source_file TEXT,
  line_no INT,
  user_agent TEXT,
  reported_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  noise_filter VARCHAR(32) NULL  -- 'browser_ext', 'self_test', etc.
```

## Noise filter

Browser extensions inject content that violates CSP. Filter out by user-agent + blocked-uri patterns:

- `chrome-extension://*`, `moz-extension://*` blocked-uris → noise
- Self-test scans → noise
- Known-bad ad-tech that we don't load → flag for further investigation

## Per-violation triage

1. New CSP violation → store.
2. Daily aggregator: top-10 by count over 7-day window.
3. Editor reviews list. For each:
   - Genuine bug (template introduces inline style without nonce)? → fix template.
   - Browser extension? → add to noise filter.
   - Third-party script injection attempt? → alert + investigate.

## Cross-references

Master plan: S1084. Sister: `docs/GRIMBANEWS_CSP_VIOLATION_REPORTING_PLAN.md`, `docs/GRIMBANEWS_VULNERABILITY_SCAN_RUNBOOK.md`.
Code: `app/Http/Middleware/GrimbaSecurityHeaders.php`.
