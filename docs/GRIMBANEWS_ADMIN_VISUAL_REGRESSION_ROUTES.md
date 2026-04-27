# GrimbaNews Admin Visual Regression Routes

Capture these before production deploy and after deployment cache clear. Use the same viewport set for every run: desktop `1440x1000`, tablet `1024x768`, mobile `390x844`.

## Required Screenshots

- `/admin/grimba/cockpit` — command center, top dropdown stacking, quick actions.
- `/admin/grimba/translation` — NobuAI provider vault, provider cards, sticky save bar.
- `/admin/grimba/rss-drafts` — guardrail badges, bulk actions, empty state if filtered.
- `/admin/grimba/rss-feeds` — responsive table cards, inline destructive labels.
- `/admin/grimba/newsapi` — readiness guardrails and fetch controls.
- `/admin/grimba/news-sources` — source registry table and action affordances.
- `/admin/grimba/news-sources/triage` — inline classification controls.
- `/admin/grimba/story-clusters` — coverage actions and cluster table.
- `/admin/grimba/coverage-map` — L/C/R balance bars and mobile table labels.
- `/admin/grimba/subscribers` — audience metrics and subscriber actions.
- `/admin/grimba/cookies` — shared form sections and action bar.

## Pass Criteria

- Dropdowns are solid and above page content.
- Sidebar text is readable in light and dark mode.
- Topbar hover states are visible but not dark-mode tinted in light mode.
- Dense table rows become labeled cards on mobile.
- Destructive actions are explicit, not symbol-only.
- Reader-facing AI language remains NobuAI-only.
