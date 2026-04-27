# GrimbaNews Admin Cinematic SOK

**Date:** 2026-04-27
**Decision:** Ship after S244
**Scope:** Custom GrimbaNews backend surfaces under `/admin/grimba/*`

## Steve Design Bar

- Reader/admin continuity: the backend uses the same paper, ink, mono, Fraunces, and L/C/R visual language as the public reader.
- No translucent dropdowns: admin dropdowns, popovers, provider menus, and nested panels are solid, readable, and stacked above page actions.
- Dark/light parity: sidebar, navbar, cards, forms, alerts, empty states, and dense tables keep contrast in both themes.
- Editorial command center: the cockpit surfaces draft pressure, ingest health, coverage balance, stale NobuAI insights, and direct runbook actions.
- Dense workflows: RSS, NewsAPI, sources, subscribers, clusters, coverage map, and triage tables have mobile labels and larger inline hit areas.
- Clear wayfinding: Grimba admin forms, settings, queues, triage, coverage map, cookie settings, and list pages expose a visible return path.
- Action clarity: primary, secondary, warning, and destructive actions have distinct states; destructive controls avoid symbol-only labels.
- Useful empty states: core queues explain what happened and offer a next action instead of stock blank rows.
- Provider safety: reader-facing AI stays branded as NobuAI; provider names and failure diagnostics remain admin-only.
- Test lock: admin chrome, shell adoption, empty states, responsive tables, form sections, wayfinders, and representative renders are covered.

## Remaining Non-Blocking Follow-Ups

- Full Botble core pages outside `/admin/grimba/*` still inherit the global chrome but not every custom Grimba component.
- Production smoke should confirm the same dropdown and dark-mode behavior after deployment cache/build steps.

## Outcome

The SOK outcome is **ship / continue**. The backend now reads as a GrimbaNews editorial product rather than stock Botble, and the remaining work can proceed as feature iteration rather than redesign remediation.
