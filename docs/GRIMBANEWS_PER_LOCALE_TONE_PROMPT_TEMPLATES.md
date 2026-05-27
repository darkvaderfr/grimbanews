# GrimbaNews — Per-Locale NobuAI Tone Prompt Templates

**Status:** plan v0 (post.summary_nobuai_locale field is locale-aware; per-locale prompt-template deferred)
**Owner:** Lisa Nguyen (data) + Lucy Leai (Strategy) + per-locale editor
**Walks:** Mythos S1083 (per-language NobuAI tone) deferred → partial
**Gating dependency:** Per-locale native-speaker editor sign-off on tone templates.

## Why this exists

A French-language summary written with the English-defaults prompt sounds wrong. Native readers in each locale expect specific stylistic patterns:

- **FR:** formal you (vous), Académie register, declarative sentences, no exclamations unless quoting source.
- **EN:** plain language, contractions OK, active voice preferred.
- **DE:** capital nouns (Pflicht), formal Sie, clarity over brevity.
- **ES:** formal usted in news context, neutral Castilian by default (LatAm variants for Latin sources).
- **PT-BR:** Brazilian register, polite formal você, no Portugal-specific spellings.
- **JA:** desu/masu polite register, no abbreviations.
- **AR:** Modern Standard Arabic (MSA) for news, RTL handled in render.

## Per-locale prompt template

`lang/<locale>/nobuai-prompts.json`:

```json
{
  "summary": "Résumez cet article en 80 mots maximum, registre journalistique formel français. Évitez le tutoiement et les exclamations. Reformulez sans ajouter de fait nouveau.",
  "insight": "...",
  "translate": "..."
}
```

## Wiring

`GrimbaNobuAi::renderPrompt('summary', $context)` reads:
1. Try `lang/<locale>/nobuai-prompts.json[summary]`
2. Fallback to `lang/fr/nobuai-prompts.json[summary]` (source-language)
3. Fallback to PHP-coded default

Per-locale editor signs off on the template before going live.

## Editorial QA

- Per-locale editor reviews 10 random summaries weekly.
- If tone is off, edits the template; next round of summaries uses the updated version.

## Cross-references

Master plan: S1083. Sister: `docs/GRIMBANEWS_PER_TOPIC_EDITORIAL_BRIEF_TEMPLATE.md`, `docs/GRIMBANEWS_DE_EDITORIAL_PAGES_SCOPE.md`.
Code: `app/Services/GrimbaNobuAi.php`.
