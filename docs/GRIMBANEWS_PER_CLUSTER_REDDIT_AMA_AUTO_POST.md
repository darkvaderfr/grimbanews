# GrimbaNews — Per-Cluster Reddit AMA Auto-Post

**Status:** plan v0
**Owner:** Lucy Leai (Strategy) + Liam Smith (PM)
**Walks:** Mythos S1684 (per-cluster Reddit AMA auto-post) deferred → partial
**Gating dependency:** Reddit API + per-subreddit moderator approval.

## Why this exists

Reddit's r/news, r/europe, r/france etc. drive significant news traffic. Per-major-cluster post to relevant subreddits surfaces GrimbaNews to a high-engagement community.

## v1 design

For top-3 dossiers daily with strong reader engagement:

1. NobuAI generates Reddit-style post:
   - Headline (concise + descriptive)
   - 3-paragraph TL;DR
   - L/C/R coverage breakdown
   - "Why we made this cluster" (editorial transparency)
   - Link to /comparatif/{id}
2. Posted via Reddit API to per-relevant subreddit (mapped from cluster.editorial_region + topic).

## Subreddit mapping

- /r/france — FR coverage
- /r/europe — EU + multi-country
- /r/worldnews — international
- /r/brasil — BR coverage
- /r/germany — DE coverage
- /r/argentina, /r/mexico, etc. — per-country LATAM
- /r/AfricaNews — Africa

## Moderation respect

- Always respect subreddit rules (no spam labels).
- Per-subreddit weekly post cap (subreddits often limit publisher posts).
- Per-subreddit Mod outreach pre-launch.

## Cross-references

Master plan: S1684. Sister: Wave AALL platform sister docs.
