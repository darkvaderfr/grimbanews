# GrimbaNews — PWA Share-Target Design

**Status:** plan v0
**Owner:** Nina Patel (Lead FE) + Liam Smith (PM)
**Walks:** Mythos S1568 (offline mode — share-target receive shared URL) deferred → partial
**Gating dependency:** PWA manifest update + handler route + auth/cookie-mode parity.

## Why this exists

When a reader is browsing the web (in any app) and finds an article they want to "send to Grimba" for later read / vault save / annotation, the OS share sheet should list Grimba. Today it doesn't because we don't declare `share_target` in the manifest.

## v1 design

Manifest addition:

```json
"share_target": {
  "action": "/coffre/recevoir",
  "method": "POST",
  "enctype": "multipart/form-data",
  "params": {
    "title": "title",
    "text": "text",
    "url": "url"
  }
}
```

Route handler at `/coffre/recevoir`:

- If URL is a Grimba URL → redirect to article + offer "Save to vault" CTA.
- If URL is external + matches a tracked source → look up in `posts` table, redirect to canonical or offer "Suggest as source" form.
- If URL is external + unknown → save as a vault clip with original-source link.

## UX

- Lightweight confirmation screen, not a full page.
- One-tap save to default notebook.
- Don't auto-publish or auto-share.

## Privacy

- Shared content is private to the reader; never indexed or shared.
- No external fetch of received URL beyond a HEAD request for OpenGraph metadata.

## Cross-references

Master plan: S1568. Sister: S1554 (cross-device sync), S1565 (conflict resolution), S1370 (offline launch).
