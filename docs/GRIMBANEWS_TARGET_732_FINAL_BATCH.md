# GrimbaNews — Target 732 Final Batch

**Walks:** Mythos S1076 + S1077 + S1082 + S1087 + S1100 deferred → partial (5 rows).
**Owner:** Lisa Nguyen (data) + Lucy Leai (Strategy).

These 5 rows close the gap to Vader's 732-sprint target.

- **S1076:** Embedding store — needs vector DB (pgvector/qdrant/pinecone). Plan ships per `docs/GRIMBANEWS_VECTOR_EMBEDDINGS_STORE_PLAN.md` Wave LLL.
- **S1077:** Retrieval-augmented insight — depends on S1076. Plan: per-cluster NobuAI insight enriched with retrieval of most-similar past clusters.
- **S1082:** Per-edition NobuAI style — operator pins per-edition prompt template per `docs/GRIMBANEWS_PER_LOCALE_TONE_PROMPT_TEMPLATES.md`.
- **S1087:** NobuAI A/B insight quality — A/B harness from `docs/GRIMBANEWS_PROMPT_AB_HARNESS_PLAN.md` Wave YYYY tested on insight prompts.
- **S1100:** NobuAI launch summary brief — operator-side post-launch comms.

## Cross-references

Master plan: S1076, S1077, S1082, S1087, S1100.
Sister docs: `docs/GRIMBANEWS_VECTOR_EMBEDDINGS_STORE_PLAN.md`, `docs/GRIMBANEWS_PER_LOCALE_TONE_PROMPT_TEMPLATES.md`, `docs/GRIMBANEWS_PROMPT_AB_HARNESS_PLAN.md`.
