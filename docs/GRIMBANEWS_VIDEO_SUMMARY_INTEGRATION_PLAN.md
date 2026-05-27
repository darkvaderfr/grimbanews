# GrimbaNews — Video Summary Integration Plan

**Status:** plan v0
**Owner:** Steve Jobs (CPO) + Liam Smith (PM) + Lucy Leai (Strategy)
**Walks:** Mythos S1434 (video summary integration) deferred → partial
**Gating dependency:** Video-generation pipeline (e.g. Synthesia, HeyGen, or open-source Wav2Lip) + per-video storage tier.

## Why this exists

Younger reader cohorts strongly prefer short-form video (TikTok/Reels/Shorts cadence). A 60-second per-cluster video summary lets them get the gist without reading or listening.

## v1 design

For top-10 daily dossiers, generate a 60-second video:

1. NobuAI generates 60-second script (Wave AACC research mode minus the multi-step structure).
2. TTS converts script to MP3 (Wave AABB pipeline).
3. Stock-footage / b-roll provider supplies visuals via API (e.g. Pexels, Pixabay).
4. Video assembly via FFmpeg (kicks off background job).
5. Stored at `storage/app/public/video/cluster-{id}-{hash}.mp4`.
6. Subtitles burned in (per-locale).

## Distribution

- Inline on /comparatif/{id} cluster page.
- Per-cluster TikTok upload (gates on Vader brand-account onboarding).
- Per-cluster Reels upload (Meta API gates).
- Per-cluster Shorts upload (YouTube API gates).

## Cost

- Per-video: ~$0.30 (TTS + b-roll + FFmpeg compute).
- 10 videos/day × 30 days = ~$90/month. Negligible at scale.

## Editor review

- 30-min daily window for editor to reject any of the 10 before they ship.
- Per-video disclaimer in description: "Vidéo générée à partir de la couverture éditoriale GrimbaNews."

## Cross-references

Master plan: S1434. Sister: `docs/GRIMBANEWS_AUDIO_NARRATION_PLAN.md`, `docs/GRIMBANEWS_PODCAST_PUBLISHING_PIPELINE.md`.
