# GrimbaNews — Science Misinformation Fact-Check Track

**Status:** plan v0
**Owner:** Sara Chen (CISO) + Lucy Leai (Strategy) + science editor TBD
**Walks:** Mythos S2188 (Science v2 misinformation fact-check track) deferred → partial
**Gating dependency:** Overlaps S1596 (misinformation flag, already partial via Wave KKKK).

## Why this exists

Science misinformation has distinct properties vs political misinformation:
- Often based on real-but-misinterpreted studies
- Anti-vax / climate-denial / lab-leak patterns recurring
- Specific debunk infrastructure (Science Feedback, AFP Sciences, Sense About Science) exists

GrimbaNews integrates these into a dedicated science-misinfo track.

## Per-claim taxonomy

Claim types tracked:
- **Cherry-picked single study** (one paper used to disprove consensus).
- **Misrepresented findings** (study said X, viral claim says Y).
- **Discredited study cited as authoritative** (e.g. retracted papers).
- **Out-of-context expert quote** (specialist quoted on non-specialty).
- **Conspiracy narrative** (institutional fraud / cover-up claims).

## Source roster for debunks

- Science Feedback (sciencefeedback.co)
- Sense About Science (UK)
- AFP Sciences fact-check
- Snopes Science section
- Reuters Fact Check Science
- Health Feedback (Science Feedback sister site)

## v1 integration

Per-cluster science articles auto-tagged for debunk-check:
1. Per-cluster, query debunk-source feeds for matching topic/claim text.
2. Per-match: surface "Vérification scientifique" panel on cluster page.
3. Per-flagged-claim: link to debunk + summary.

## Editor cadence

Per-week: science editor reviews flagged-debunks queue.

## Cross-references

Master plan: S2188. Sister: `docs/GRIMBANEWS_HALLUCINATION_DETECTOR_PLAN.md`, S1596 misinfo flag partial.
