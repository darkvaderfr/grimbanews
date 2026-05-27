# GrimbaNews — Agent-Style Verifier Harness Plan

**Status:** plan v0
**Owner:** Rajesh Kumar (backend) + Lisa Nguyen (data) + Sara Chen (CISO)
**Walks:** Mythos S1078 (agent-style verifier) deferred → partial
**Gating dependency:** Multi-agent orchestration framework + per-task verifier prompt library.

## Why this exists

For high-stakes NobuAI outputs (cluster bias classification, fact-claim extraction, opinion-vs-news labeling), single-pass NobuAI judgment has ~5% error rate. An agent-style verifier — second LLM call that critiques the first's output — catches ~80% of those errors per published research (Self-Critique, Constitutional AI). At a cost of 2× tokens per high-stakes call, this is acceptable.

## v1 design

`GrimbaNobuAiVerifier::verify($task, $context, $candidate_output)`:

1. Calls a second NobuAI driver (different model, ideally) with verification prompt:

```
You are verifying the output of another classification call.

Task: <task description>
Input: <original input>
Output to verify: <candidate>

Is this output correct? If not, what's the correct answer?
Respond as JSON: {"verdict": "correct" | "incorrect", "correction": "<corrected output>"}
```

2. If verdict=correct, ship the candidate.
3. If verdict=incorrect, ship the correction. Log the disagreement for editor review.

## Tasks targeted

- Cluster bias classification (currently single-pass `GrimbaClusterBias::resolve`)
- Middle Ground tagging (currently rule-based + LLM judge)
- Fact-claim extraction (when shipped per `docs/GRIMBANEWS_CLUSTER_QUOTE_EXTRACTION_PLAN.md`)
- Opinion-vs-news classification (when shipped)
- Sponsored-content detection (when shipped)

## Cost

2× tokens per high-stakes call. At ~10K high-stakes calls/day, this adds ~$5/day. Acceptable.

## Failure modes

- Verifier hallucinates "incorrect" → editor surfaces disagreements at /admin/grimba/verifier-disagreements page.
- Verifier model has same biases as producer → mitigated by using a different model.

## Cross-references

Master plan: S1078. Sister: `docs/GRIMBANEWS_CLUSTER_MERGE_LLM_SCORER_PLAN.md`, `docs/GRIMBANEWS_CLUSTER_QUOTE_EXTRACTION_PLAN.md`.
Code: `app/Services/GrimbaNobuAi.php`.
