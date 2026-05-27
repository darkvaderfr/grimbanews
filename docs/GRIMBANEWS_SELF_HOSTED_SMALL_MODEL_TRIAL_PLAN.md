# GrimbaNews — Self-Hosted Small Model Trial Plan

**Status:** plan v0
**Owner:** Rajesh Kumar (backend) + Jacob Lee (DevOps) + Ray Dalio (CFO) on unit economics
**Walks:** Mythos S1072 (self-hosted small-model trial) deferred → partial
**Gating dependency:** GPU box + inference framework (vLLM/TGI/llama.cpp).

## Why this exists

Current NobuAI fleet is fully external (Anthropic, OpenAI, Mistral, OpenRouter). A self-hosted small model for high-volume cheap calls (summary, classification, translation) would cut per-call cost from ~$0.001 to ~$0.0001 while staying within Iboga's privacy posture (no data leaves our infra for these calls).

## Trial scope

- Model: Mistral-7B-Instruct-v0.3 or Llama-3.1-8B-Instruct (open weights).
- Hardware: 1× A10G or L4 GPU box (cloud) for trial. Production gates on Vader call.
- Inference: vLLM (highest throughput) or llama.cpp (lowest cost).
- Tasks targeted: bias-classifier-judge (Wave HHHH-like), summary draft, translation pre-pass.
- Keep external NobuAI for final quality + frontier reasoning.

## Per-task A/B

For each task, run 1000 calls in parallel to external (control) + self-hosted (variant). Compare:
- Quality (editor review on sample of 50 outputs).
- Latency (median + p95).
- Cost per call.
- Failure rate.

## Decision criteria

If self-hosted achieves ≥ 90% quality at ≥ 10× cost reduction with ≤ 2× latency → ship. Otherwise, defer.

## Cost estimate

- A10G cloud: ~$0.50/hour = ~$360/month.
- Throughput at vLLM: ~50 req/sec sustained = ~130M req/month.
- Per-call cost: ~$360/130M = $0.0000028 → ~$0.000003.
- Current external NobuAI on cheap calls: ~$0.0001 → 33× savings.

## Privacy benefit

Sponsored-content + opinion classification calls touch reader-adjacent fields. Self-hosting keeps these in-house.

## Cross-references

Master plan: S1072. Sister: `docs/GRIMBANEWS_BREAKING_NEWS_CLASSIFIER_V2_LLM_JUDGE_PLAN.md`, NobuAI fleet code at `app/Services/GrimbaNobuAi.php`.
