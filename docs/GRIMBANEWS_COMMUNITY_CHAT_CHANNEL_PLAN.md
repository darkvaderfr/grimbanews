# GrimbaNews — OSS Community Chat Channel Plan

**Status:** plan v0
**Owner:** Maria Lopez (Community) + Michael O'Connor (Tech Writer)
**Walks:** Mythos S2090 (community — Discord / Matrix / Slack channel) deferred → partial
**Gating dependency:** S2043 OSS org + moderator roster (≥ 2 humans).

## Why this exists

OSS contributors need a low-latency channel for questions that don't merit a GitHub issue. Without one, knowledge fragments to private DMs, slows new-contributor onboarding, and concentrates load on maintainers.

## v1 channel choice

| Option | Pros | Cons |
|---|---|---|
| Discord | Mainstream, voice support, low setup | Centralized, walled-garden |
| Matrix (Element) | Federated, self-host option, FOSS-aligned | Smaller community |
| Slack | Familiar to corporate contributors | Free tier loses history |
| Zulip | Threaded conversation, good for OSS | Niche |

**v1 recommendation: Matrix (Element)** — aligns with OSS-first ethos + supports federation.
**v1 fallback: Discord** if Matrix uptake is < 10 active members in first 3 months.

## Channel structure

- `#general` — open chat.
- `#help` — newcomer questions.
- `#contributing` — PR / issue discussions.
- `#announcements` — read-only, maintainer posts.
- `#methodology` — for academic-tier contributors.

## Moderation

- Code of Conduct enforced (per S2099).
- ≥ 2 moderators always on call.
- 24h response SLA for #help.
- Quarterly community-call (S2089) hosted in voice.

## Cross-references

Master plan: S2090. Sister: S2043 (OSS org), S2087 (recognition), S2089 (community call), S2099 (CoC enforcement).
