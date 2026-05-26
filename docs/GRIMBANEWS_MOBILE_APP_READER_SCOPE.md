# GrimbaNews — Mobile App Reader Scope

**Status:** plan v0 (web `/dossier/{id}` + `/blog/{slug}` are the surrogates inside WebView today)
**Owner:** Steve Jobs (CPO) signs reader UX + Nina Patel (Lead FE) implements native interactions + Alex Morgan (UI/UX) on typography mobile-first
**Walks:** Mythos S1164 (App reader) deferred → partial
**Gating dependency:** Native shell + Capacitor's `Filesystem` plugin for offline cache + reading-mode design (per `GRIMBANEWS_READING_MODE_DESIGN.md`)

## Why this exists

S1164 is the core reader experience inside the app. WebView reuse covers ~80%, but native-quality features (text selection, share sheet, haptic on save, swipe back) require Capacitor plugins.

## Today's surrogate

- **`/dossier/{id}`** — cluster view with bias-distribution rail.
- **`/blog/{slug}`** — single article view.
- **PWA** — service worker caches shell + recent reads.
- **Browser share** — works via Web Share API on supported devices.

## Native enhancements

| Enhancement | Plugin | Behavior |
|---|---|---|
| Native share sheet | `@capacitor/share` | Better than Web Share — guaranteed all apps |
| Haptic on save to coffre | `@capacitor/haptics` | `.impact({ style: 'light' })` |
| Text-to-speech | `@capacitor-community/text-to-speech` | Reader-side accessibility |
| Swipe-back gesture | iOS WKWebView native; Android needs custom | History pop on edge swipe |
| Reading-mode toggle | leverages `docs/GRIMBANEWS_READING_MODE_DESIGN.md` | Per-reader preference cookie + native cache |
| Bookmark sync | uses existing coffre + cross-device sync (S1554) | Native triggers identical to web vault save |

## Offline reading

- On `/dossier/{id}` first read: cache full HTML + images to `Filesystem`.
- Offline indicator on the article header.
- On reconnect: stale cache replaced.
- Per-article cache TTL: 7 days.

## Performance budget (mobile)

| Metric | Target | Source |
|---|---|---|
| FCP | <1.5s on 4G | Lighthouse mobile |
| LCP | <2.5s | Lighthouse mobile |
| TBT | <300ms | Lighthouse mobile |
| Bundle on first read | <500KB JS | webpack report |
| Image weight per article | <300KB total | `grimba:image-proxy` resize |

## Cross-references

- Master plan: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1164)
- Sister docs: `docs/GRIMBANEWS_READING_MODE_DESIGN.md`, `docs/GRIMBANEWS_FONT_SCALING_A11Y_MATRIX.md`, `docs/GRIMBANEWS_MOBILE_APP_FORYOU_SCOPE.md`, `docs/GRIMBANEWS_MOBILE_APP_LOCAL_EDITION_SCOPE.md`
- Existing routes: `/dossier/{id}`, `/blog/{slug}`
- Roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
