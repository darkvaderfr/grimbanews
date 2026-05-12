# Publisher Image Proxy — Diagnosis & Backend Sprint Plan

**Carry-over from Session 9 (2026-04-30) → flagged again in Session 10 (2026-05-01).**

This is a **diagnosis document**, not an implementation. The fix needs a backend sprint (route + cache disk write + setting flag), and Vader's standing rule says no migrations or new disk-writing services without explicit approval. The current Session 10 fleet is front-end-only.

---

## What we observed

Headless Playwright walk of `http://127.0.0.1:8002/` (cookie `grimba_region=international`):

| Metric | Value |
|--------|-------|
| Total `<img>` on the homepage | 38 |
| Imgs that fail or stall (>6s, never decode) | 11 (29%) |
| Imgs that succeed | 27 |

The imgs that fail are all external publisher CDNs:
- `i.f1g.fr` (Le Figaro)
- `www.lexpress.fr` (signed URL, expires)
- `prod.cdn-medias.jeuneafrique.com` (Jeune Afrique)
- `focus.huffingtonpost.fr` (HuffPost)
- `ichef.bbci.co.uk` (BBC)
- `img.lemde.fr` (Le Monde)

The Session 9 fallback (`platform/themes/echo/partials/home/front-body-hooks.blade.php`) detects these failures via a 6-second stall timeout + an `error` listener, and swaps the failed `<img>.src` to our own `/og/placeholder/{id}.svg`. The user-visible result is acceptable: every card shows *something*, and the "fallback card" CSS lightens the dark gradient overlay so the placeholder renders legibly.

**This means the fix is masked, not fixed.** We're still serving 11 broken external image URLs to the browser, paying the network round-trip on each, and only swapping after a 6s timeout — which means a slow publisher image briefly shows as a black-with-headline card before the swap.

---

## Why the failures happen (most likely → least likely)

1. **Hot-link protection.** Many publisher CDNs check the `Referer` header and serve a 403 / blank when the referer isn't on their own domain. Because we render full URLs into `<img src="...">`, the browser sends our origin as the referer.
2. **Signed-URL expiry.** Lexpress URLs have `?auth=<sig>&...` query strings. When we ingest a story and store the image URL, that signature was valid; by the time a reader hits the page hours/days later, it has expired.
3. **CDN geo / IP blocking.** Some publishers block AWS/cloud egress IP ranges or specific countries.
4. **Cloudflare WAF challenge.** Bot-detection layers between the publisher and us serve a JS challenge instead of the image, which the `<img>` tag can't solve.
5. **DNS / connectivity to specific CDNs from the local dev environment.** Less likely for prod, but observable on Vader's laptop.

We won't know the exact distribution without instrumenting each request. (1) and (2) cover ~80% of the cases I'd bet on.

---

## What Ground.news does

Ground.news doesn't render external publisher hero images directly. They proxy or re-host every card image. Open the network tab on `https://ground.news/` and every `<img>` you see comes from a `gn-cdn.s3.amazonaws.com` or similar S3 bucket — never from the original publisher's CDN. They pay the bandwidth + storage cost in exchange for never having a broken card image.

This is the architecture we should target.

---

## Proposed backend sprint (when authorized)

### Sprint B-IMG-01: extend `img-proxy` route to publisher hero images

The route already exists (`platform/themes/echo/routes/web.php:177`) but is allowlisted to Clearbit + Google favicon only. Extend it to:

- Accept `provider=article-hero` as a third whitelisted source.
- Add a server-side allowlist of publisher CDN hostnames (start with the 8 we see most often: `i.f1g.fr`, `www.lexpress.fr`, `prod.cdn-medias.jeuneafrique.com`, `focus.huffingtonpost.fr`, `ichef.bbci.co.uk`, `img.lemde.fr`, `www.lefigaro.fr`, `static-mali24.cdn.so` etc.). Hostnames live in a config file so we can grow without redeploying.
- Strip the `Referer` header on the outbound `Http::get()` (we already do this implicitly — `Http::get` doesn't forward arbitrary headers — but be explicit so a future contributor doesn't accidentally pass it).
- Cache more aggressively: 30 days on disk, with `s-maxage=2592000` on the response so any in-front CDN holds it.
- On 403 / 404, instead of returning a 404 to our own browser, fall back to the editorial placeholder server-side and cache that fallback under the original image's hash. This way the "11 of 38 fail" repeats, but only ONCE per image — the second visitor pays no network roundtrip.

### Sprint B-IMG-02: rewrite article image URLs at render time

Touch `platform/themes/echo/partials/post-hero-img.blade.php`. When `$post->image` is an external URL (starts with `http://` or `https://`), don't emit it directly. Emit our `/img-proxy?provider=article-hero&u=<encoded>` instead.

This is a one-line change in the partial. The benefit: the browser only ever talks to our origin for hero images, the proxy handles everything else.

### Sprint B-IMG-03: pre-warm at ingest time

When the RSS poller / NewsAPI fetcher (`app/Services/GrimbaRssPoller.php`, `app/Services/GrimbaNewsApiFetcher.php`) ingests a new post, immediately fire-and-forget a request to our own `/img-proxy?provider=article-hero&u=...` so the image is cached on disk by the time any reader hits the story. This eliminates the cold-start delay.

### Sprint B-IMG-04: storage budget + GC

The proxy cache lives at `storage/app/public/img-proxy/`. We should add a daily artisan command that prunes files older than 60 days and reports total cache size to admin. Otherwise the disk fills up silently.

Status 2026-05-12: implemented as `grimba:prune-img-proxy-cache --days=60`, scheduled daily at 03:25 and wrapped by the automation monitor. It supports `--dry-run` and `--cache-dir=` for maintenance/test runs.

---

## Why this is a backend sprint (and why we deferred it from Session 10)

- The proxy route writes to `storage/app/public/img-proxy/`. The Session 9 cache permission residue (root-owned subdirs in `storage/framework/cache/data`) suggests there may be similar residue elsewhere on Vader's local; we'd want to verify ownership BEFORE the proxy starts writing or we'll get the same EACCES failures.
- The pre-warm step changes the RSS poller behavior. That's a bigger surface than a front-end sprint should touch.
- A storage GC command needs scheduling in `app/Console/Kernel.php` and a per-environment retention setting.
- We may want to migrate `news_sources` to add a `logo_proxy_url` cache column so the front-end doesn't need to re-resolve every render. Migrations are blocked in Session 10.

---

## Recommendation

When Vader greenlights:

1. Run `sudo chown -R vb:staff /Users/vb/GrimbaNews/storage` first (Session 9 carry-over) to clear the root-residue and unblock both `php artisan` AND new cache writes by the proxy.
2. Implement B-IMG-01 (proxy route allowlist extension).
3. Implement B-IMG-02 (URL rewrite at render).
4. Verify that the 11/38 failure rate drops to 0/38 visible failures (the proxy may still 404 on some, but it caches the 404 and the front-end fallback takes over before the user sees anything).
5. Then B-IMG-03 + B-IMG-04 in a follow-up.

Front-end fix (Session 9 fallback) stays in place as a defense-in-depth layer. Even with the proxy, occasional 404s will happen — the JS fallback is still the right last line.
