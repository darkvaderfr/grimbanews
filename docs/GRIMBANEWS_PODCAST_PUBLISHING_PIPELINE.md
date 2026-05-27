# GrimbaNews — Podcast Publishing Pipeline

**Status:** plan v0
**Owner:** Lucy Leai (Strategy) + Lisa Nguyen (data) + Liam Smith (PM)
**Walks:** Mythos S1433 (podcast publishing pipeline) deferred → partial
**Gating dependency:** Apple Podcasts + Spotify for Podcasters distribution accounts + RSS-podcast spec generator.

## Concept

Daily 5-minute podcast: "Le tour des perspectives" — automatically-generated narration of top dossiers + Middle Ground signal + 1 blindspot. Audio + RSS feed distributed to standard podcast clients.

## Pipeline

1. Daily 06:00 UTC cron: `grimba:publish-podcast` (planned Artisan command).
2. Gathers: top-3 dossiers by source count + 1 highest-velocity Middle Ground + 1 most-recent Blindspot.
3. NobuAI generates 5-min script per locale (FR default; EN gates on per-locale editor sign-off).
4. TTS via Wave AABB audio-narration pipeline → MP3.
5. Stored at `storage/app/public/podcast/{locale}/{YYYY-MM-DD}.mp3`.
6. RSS feed at `/feed.podcast.{locale}.xml` per Apple Podcasts spec.
7. Auto-uploaded to Spotify via S4P API (gates on account).

## RSS feed spec

iTunes-namespace XML:
```xml
<rss xmlns:itunes="...">
  <channel>
    <title>GrimbaNews — Le tour des perspectives</title>
    <itunes:author>GrimbaNews</itunes:author>
    <itunes:owner>...</itunes:owner>
    <itunes:image>...</itunes:image>
    <itunes:category text="News"/>
    <item>
      <title>YYYY-MM-DD — Tour des perspectives</title>
      <enclosure url="..." type="audio/mpeg" length="..."/>
      <itunes:duration>5:00</itunes:duration>
    </item>
  </channel>
</rss>
```

## Editor review

- Per-day editor reviews the auto-generated script before TTS fires.
- 30-min editorial window 05:30-06:00 UTC.
- Editor can reject + reschedule for next day.

## Cross-references

Master plan: S1433. Sister: `docs/GRIMBANEWS_AUDIO_NARRATION_PLAN.md`, `docs/GRIMBANEWS_VIDEO_SUMMARY_INTEGRATION_PLAN.md`.
