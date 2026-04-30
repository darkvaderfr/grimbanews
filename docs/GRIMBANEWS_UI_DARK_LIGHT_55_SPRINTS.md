# GrimbaNews UI Dark/Light 55-Sprint Plan

**Status:** active UI hardening plan  
**Created:** 2026-04-30  
**Lead:** Steve Jobs  
**Team:** Liam Smith, Alex Morgan, Marissa Mayer, Nina Patel, Lisa Nguyen, Alice Chen, Sara Kim, Zenkai, Echo, Mnemo, Sara Chen, Maya Patel

## Goal

Eliminate public-reader dark/light regressions like orphan modal backdrops, unreadable dark overlays, weak contrast, stale theme state, and route-specific UI assumptions. Every sprint below must leave evidence: changed files, screenshot or smoke route, test command, risk, and next dependency.

## Sprint Queue

| Sprint | Owner | Outcome |
|---|---|---|
| UI-DL-001 | Steve Jobs | Public shell orphan backdrop audit and fix. |
| UI-DL-002 | Nina Patel | Homepage dark mode first viewport screenshot pass. |
| UI-DL-003 | Lisa Nguyen | Homepage light mode first viewport screenshot pass. |
| UI-DL-004 | Alice Chen | Mobile homepage dark/light nav contrast pass. |
| UI-DL-005 | Liam Smith | Theme toggle state and cookie sync audit. |
| UI-DL-006 | Alex Morgan | Stock Botble popup/backdrop suppression audit. |
| UI-DL-007 | Marissa Mayer | Newsletter modal dark/light contrast pass. |
| UI-DL-008 | Nina Patel | Onboarding modal dark/light contrast pass. |
| UI-DL-009 | Lisa Nguyen | Region/edition dropdown solid surface pass. |
| UI-DL-010 | Alice Chen | Language switcher dark/light state pass. |
| UI-DL-011 | Sara Kim | Desktop Playwright home smoke for no global overlay. |
| UI-DL-012 | Zenkai | Mobile Playwright home smoke for no global overlay. |
| UI-DL-013 | Echo | Incognito home smoke for no auto-open modal. |
| UI-DL-014 | Mnemo | Screenshot evidence archive format. |
| UI-DL-015 | Sara Chen | Consent banner z-index/security audit. |
| UI-DL-016 | Maya Patel | Focus trap and Escape behavior audit. |
| UI-DL-017 | Steve Jobs | Story page dark first viewport hierarchy pass. |
| UI-DL-018 | Nina Patel | Story page light first viewport hierarchy pass. |
| UI-DL-019 | Lisa Nguyen | Story NobuAI chip contrast pass. |
| UI-DL-020 | Alice Chen | Story full-article gate dark/light pass. |
| UI-DL-021 | Liam Smith | Source page card contrast pass. |
| UI-DL-022 | Alex Morgan | Sources index dark/light filter pass. |
| UI-DL-023 | Marissa Mayer | Search page facets dark/light pass. |
| UI-DL-024 | Nina Patel | Search result card image fallback contrast. |
| UI-DL-025 | Sara Kim | `/search` browser smoke light/dark. |
| UI-DL-026 | Zenkai | `/sources` browser smoke light/dark. |
| UI-DL-027 | Echo | `/article/*` browser smoke light/dark. |
| UI-DL-028 | Mnemo | Route evidence ledger update. |
| UI-DL-029 | Sara Chen | Public provider-name leak check in dark/light UI. |
| UI-DL-030 | Maya Patel | Keyboard navigation pass for public header. |
| UI-DL-031 | Steve Jobs | Footer dark/light hierarchy pass. |
| UI-DL-032 | Nina Patel | Mobile bottom nav contrast pass. |
| UI-DL-033 | Lisa Nguyen | Vault FAB dark/light pass. |
| UI-DL-034 | Alice Chen | Saved-story buttons dark/light pass. |
| UI-DL-035 | Liam Smith | Home ad slots dark/light frame pass. |
| UI-DL-036 | Alex Morgan | Story ad slots dark/light frame pass. |
| UI-DL-037 | Marissa Mayer | Empty states dark/light copy and contrast. |
| UI-DL-038 | Sara Kim | Axe smoke on home/story/search. |
| UI-DL-039 | Zenkai | Reduced-motion modal and header pass. |
| UI-DL-040 | Echo | 200% zoom no-overlap pass. |
| UI-DL-041 | Mnemo | Visual regression route matrix update. |
| UI-DL-042 | Steve Jobs | Admin Grimba cockpit dark/light quick pass. |
| UI-DL-043 | Nina Patel | Admin translation vault dark/light quick pass. |
| UI-DL-044 | Lisa Nguyen | Admin RSS/NewsAPI queues dark/light quick pass. |
| UI-DL-045 | Alice Chen | Admin source forms dark/light quick pass. |
| UI-DL-046 | Liam Smith | Shared CSS token audit for one-note palettes. |
| UI-DL-047 | Alex Morgan | Shared z-index register for public/admin overlays. |
| UI-DL-048 | Marissa Mayer | Public typography/container overflow pass. |
| UI-DL-049 | Sara Chen | Cookie/consent overlay privacy copy visibility. |
| UI-DL-050 | Maya Patel | Screen reader modal announcement pass. |
| UI-DL-051 | Sara Kim | Full public route smoke pack. |
| UI-DL-052 | Zenkai | Dark/light screenshot diff triage. |
| UI-DL-053 | Echo | Regression test for orphan Bootstrap backdrops. |
| UI-DL-054 | Mnemo | Release-gate UI evidence summary. |
| UI-DL-055 | Steve Jobs | Final Steve/team signoff on dark/light readiness. |

