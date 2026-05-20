# S009 — Shipped Commit Map

**Generated:** 2026-05-19
**Repo:** `darkvaderfr/grimbanews:main`
**Total commits in history:** 633 (`git log --oneline | wc -l`)

## Recent commits — Wave RRRRRR → CCCCCCCC (2026-05-19 SEO + security + sprint reconciliation block)

| Commit | Wave | Subject |
|---|---|---|
| `75c59234` | CCCCCCCC | master-plan sprint reconciliation sweep — 52 new evidence rows |
| `af8a7bf1` | BBBBBBBB | preserve ?page=N in canonical — unblocks indexing of pages 2+ |
| `4fe1c958` | AAAAAAAA | dynamic sitemap-grimba.xml — lastmod tracks real content |
| `f2719ae6` | ZZZZZZZ | single-owner Theme-flag cleanup per Zen audit MEDIUM |
| `12c6a799` | YYYYYYY | REVERT SSSSSSS — CSRF token would leak via shared CDN cache |
| `e3e2b053` | XXXXXXX | lock every JSON-LD block as valid parseable JSON |
| `dbb45fd2` | WWWWWWW | 404 page — drop canonical, force noindex, skip 301 hop |
| `5072f555` | VVVVVVV | tighten robots.txt — Disallow auth-gated, keep noindex crawlable |
| `ae068dab` | UUUUUUU | backfill sitemap gap with theme-only routes |
| `1a40e787` | TTTTTTT | lock security-headers + cache-control contract via tests |
| `f1367de3` | SSSSSSS | (REVERTED) public cache on static editorial pages — CSRF token leak via CDN |
| `0f0a067b` | RRRRRRR | /feed.xml gets public cache (was no-cache, private) |
| `e6014aca` | QQQQQQQ | lock open-redirect rejection + img-proxy SSRF guard |
| `5750d327` | PPPPPPP | lock security.txt RFC 9116 shape |
| `1cec3afe` | OOOOOOO | SECURITY — escape `</script>` in JSON-LD blocks (stored-reflected XSS) |
| `7a473b4c` | NNNNNNN | security.txt for responsible disclosure (RFC 9116) |
| `da16feb7` | MMMMMMM | /comparatif/{abc} 500 → 404 (numeric route constraint) |
| `c01628f2` | LLLLLLL | noindex predicate matches /pour-vous (was looking for /for-you) |
| `c018740b` | KKKKKKK | 404 on missing /comparatif/{id} (was 200 + thin shell) |
| `3215c3e4` | JJJJJJJ | lock /sitemap.xml shape |

## Cumulative band progress (post-sweep 2026-05-19)

- **S-LANG band:** 16/16 closed (S301-S330 evidenced)
- **S-LSAT band:** 21/21 closed (translation rules + admin form + scheduler)
- **S-PILL band:** 10/10 closed (info-pill audit + polish)
- **S-MODE band:** 11/11 closed (dark/light parity)
- **S-ADS band:** 12/12 closed (sponsor homepage + admin lead intake)
- **S-CAT band:** 10/10 closed (category anchoring)
- **S-NDI band:** 12/20 closed (newsdata.io integration, ~8 remain blocked on operator/API-key tasks)
- **Master ledger:** 79/1000 = 7.9% formally evidenced; ~40-42% practical readiness

## Lettered waves (2026-05-16 → 2026-05-19)

A–Z + AA–ZZ + AAA–ZZZ + AAAA–ZZZZ + AAAAA–CCCCCCCC sequences. Full log in `git log --oneline`.

Most impactful from latest auto-mode session (Waves RRRRRR → CCCCCCCC):

- Wave OOOOOOO — critical XSS fix (stored-reflected via /search?q=)
- Wave WWWWWWW — 404 SEO fix (no more canonical-to-broken-URL, no more `index, follow` on errors)
- Wave BBBBBBBB — pagination canonical fix (Google can now index pages 2+)
- Wave VVVVVVV — robots.txt tightening (auth-gated paths Disallowed, noindex paths kept crawlable)
- Wave AAAAAAAA — dynamic sitemap (lastmod now tracks real content)
- Wave YYYYYYY — revert SSSSSSS after Zen audit caught CSRF-token leak via CDN cache

## Closes

- S009 (shipped commit map)
