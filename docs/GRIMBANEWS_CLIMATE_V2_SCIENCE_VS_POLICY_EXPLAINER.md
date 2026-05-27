# GrimbaNews — Climate v2 Science-vs-Policy Explainer Methodology

**Status:** plan v0
**Owner:** Lucy Leai (Strategy) + Steve Jobs (CPO) + climate editor TBD
**Walks:** Mythos S2184 (Climate v2 methodology coverage — science vs policy) deferred → partial
**Gating dependency:** Climate-specialist editor.

## Why this exists

Climate stories conflate two distinct claim-types:
- **Science claims:** "1.5°C warming is expected by 2034" — empirical, peer-reviewable.
- **Policy claims:** "France should phase out coal by 2030" — political, value-laden.

Bias classification treats them differently: science claims are factual, policy claims have legitimate left/right framings. The methodology page needs an explicit explainer for readers.

## Methodology page section (proposed)

New `/methodologie#climat` anchor:

```
## Comment GrimbaNews classe les sujets climatiques

Le climat est à la fois science (faits, modèles, mesures) et politique
(quoi faire, à quel coût, qui paye). Nos classifications éditoriales
reflètent cette distinction :

- Articles décrivant des faits scientifiques (IPCC, mesures de température)
  sont classés comme "consensus" — pas L/C/R.
- Articles débattant de politiques climatiques (taxe carbone, fin du
  charbon) reçoivent un classement L/C/R basé sur la position éditoriale
  de la source sur la politique, pas sur le fait scientifique.

Un dossier sur une nouvelle étude scientifique aura souvent un Juste
milieu signal — la gauche et la droite acceptent les faits, même si
elles divergent sur l'action. Un dossier sur une politique aura souvent
un classement plus polarisé.
```

## Editorial guardrails

- Per-article tagging: `claim_type IN ('science', 'policy', 'mixed')`.
- For `claim_type='science'`, bias-classifier deprioritizes L/C/R weight.
- For `claim_type='policy'`, standard bias resolution applies.

## Cross-references

Master plan: S2184. Sister: `docs/GRIMBANEWS_CLIMATE_V2_DEEP_SOURCE_ROSTER.md`, `docs/GRIMBANEWS_CLIMATE_V2_PER_COP_COVERAGE_PROGRAM.md`, methodology blade page.
