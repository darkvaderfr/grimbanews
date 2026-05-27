# GrimbaNews — Audio Article Narration Plan

**Status:** plan v0
**Owner:** Steve Jobs (CPO) + Lucy Leai (Strategy) + Lisa Nguyen (data)
**Walks:** Mythos S1432 (audio article narration) deferred → partial (KKKK adjacent)
**Gating dependency:** TTS provider account (ElevenLabs, Azure Speech, or open-source Coqui).

## Why this exists

Accessibility: visually-impaired readers + commuters benefit from audio. Plus engagement: avg listen time is 4× avg read time per Pocket benchmarks.

## v1 design

Per-article "Écouter" button → triggers TTS via background job:

1. Strip article HTML to clean text.
2. TTS provider generates MP3 (~$0.002/min at provider rates).
3. Store in `storage/app/public/audio/article-<id>-<hash>.mp3`.
4. Subsequent loads serve from cache.

## TTS provider selection

| Provider | Cost / 1k chars | Voice quality | French/Multi |
|---|---|---|---|
| ElevenLabs | $0.15 | A | Excellent |
| Azure Speech | $4 | B+ | Very good |
| Google TTS | $4 | B | Good |
| Coqui (self-host) | $0 (compute only) | C+ | Good |

Vader call on which provider (Ray Dalio unit economics review).

## Reader UX

- Inline player below article hero.
- Per-article play / pause / seek / speed (0.5x-2x).
- Position remembered per-reader for return-visits.
- Per-article download (gated to premium tier).

## Per-locale voice

- FR: warm authoritative (Le Monde-podcast register).
- EN: NPR-style neutral.
- ES: castizo formal.
- DE: ARD-style clear.
- Other: TBD per locale launch.

## Schema (gates on migration)

```
article_audio:
  post_id | locale | mp3_url | duration_sec | generated_at | accessed_count
```

## Cross-references

Master plan: S1432. Sister: `docs/GRIMBANEWS_PODCAST_PUBLISHING_PIPELINE.md` (companion), `docs/GRIMBANEWS_FONT_SCALING_A11Y_MATRIX.md` (Wave LLL).
