# GrimbaNews — project instructions

These project-level notes extend the global rules in `/Users/vb/.claude/CLAUDE.md`. They do not override.

## NobuAI branding — no provider names on reader surfaces

Every user-facing surface on GrimbaNews must only ever say **NobuAI** — never Anthropic, OpenAI, Google, Gemini, Claude, GPT, Mistral, DeepL, Groq, OpenRouter, or LibreTranslate.

**Reader surfaces audited clean (2026-04-24):** the translation disclaimer in `platform/themes/echo/partials/blog/post/partials/source-attribution.blade.php` says "traduit par une machine" — no provider name. The reader-mode picker at `platform/themes/echo/partials/home/translate-picker.blade.php` exposes modes (`original` / `auto` / `both`) without naming any vendor. Keep it that way.

**Admin page `/admin/grimba/translation` is exempt** — that screen is how Vader manages the 8 provider keys and is gated behind the admin guard. Provider names there stay as-is.

**When building new reader-facing features that surface AI activity (summaries, auto-translations, bias classifications, story-cluster explanations, etc.):**
- Chip / badge: "Powered by NobuAI" — not "via DeepL" / "by Claude".
- Generated copy attribution: "Résumé NobuAI" / "Drafted by NobuAI".
- Error messages: "NobuAI n'a pas pu contacter son modèle" — not "Anthropic API unreachable".
- No model picker on the reader side. If we need a speed/quality toggle, label it "NobuAI Rapide" / "NobuAI Précis" (or EN equivalents), never vendor names.

**When the fine-tuned NobuAI model ships:**
- The 8-provider fallback chain stays on the server (admin page keeps the keys).
- The reader sees no change because no reader-facing copy ever named a vendor in the first place.

## Git cadence (inherited from global)

1. Edit locally in `/Users/vb/GrimbaNews/`
2. Commit + push to `darkvaderfr/grimbanews` main
3. THEN deploy to `209.74.88.135`

No direct-on-VPS edits. Co-author trailer on every commit. Stage specific files by name — never `git add -A`.

## Resume

Say **"continue work on grimbanews"** → Claude loads `project_grimbanews_next_prompt.md` from memory first.

Current state snapshot (2026-04-24): 65+ sprints shipped, prod live at 209.74.88.135, TLS blocked on DNS (Vader's registrar step). Latest sprint: S84 image backfill (186/215 posts enriched, 86.5%).
