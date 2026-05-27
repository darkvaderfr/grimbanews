# GrimbaNews — Daily Report Email Template Design

**Status:** plan v0
**Owner:** Steve Jobs (CPO) + Liam Smith (PM) + Lucy Leai (Strategy)
**Walks:** Mythos S1700 (daily report email template) deferred → partial
**Gating dependency:** Newsletter v2 (S1271-S1290 partial per LLL) + per-region cadence (Wave KKKK).

## Template

Email per-locale, per-region, per-reader-tier (free vs premium):

```
From: GrimbaNews <noreply@grimbanews.com>
Subject: {region greeting} · {top headline} · {N more}

[GrimbaNews logo header]

{Personal greeting}

📰 LE TOUR DES PERSPECTIVES
[Top 3 cluster cards]
- Cluster 1 title (L/C/R bar)
- Cluster 2 title (Middle Ground badge)
- Cluster 3 title (Blindspot badge)

⊕ JUSTE MILIEU
[1-2 MG-tagged clusters with summary]

🕳️ ANGLES MORTS
[1 BS-tagged cluster]

🔭 RECOMMANDÉ POUR VOUS (premium only)
[3 personalized picks]

---
[Methodology link, footer, unsubscribe]
```

## A/B variants

Per `docs/GRIMBANEWS_AB_PERSONALIZATION_FLEET_DESIGN.md`:
- Subject line: question vs declarative
- Greeting: formal vs casual
- Card layout: stacked vs grid

## Per-locale variations

- FR: full template, formal tone (vous)
- EN: full template, casual tone (you)
- DE: formal Sie, compact compound nouns
- PT-BR: formal você, Brazilian register
- Others: per-locale onboarding

## Cross-references

Master plan: S1700. Sister: `docs/GRIMBANEWS_PER_TOPIC_NEWSLETTER_DESIGN.md`, `docs/GRIMBANEWS_PER_READER_WEEKLY_DIGEST_PLAN.md`.
