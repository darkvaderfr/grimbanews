# Grimba News — Product Brief

**Domain:** grimbanews.com  
**Platform:** Echo News CMS v3.1.1 (Laravel)  
**Source:** CodeCanyon (codecanyon-m0ovtlQG)  
**Git:** `darkvaderfr/grimbanews` (private)  
**Status:** Local dev — Phase 0  
**Date:** 2026-04-23

---

## Mission

GrimbaNews is the **go-to daily news platform for francophone audiences** — delivering Pan-African and global news with AI-powered rewriting, GroundNews-inspired bias transparency, and multi-channel distribution (web, email newsletter, WhatsApp, Telegram).

---

## Distinction: GrimbaNews vs GrimbaTimes

| | GrimbaNews | GrimbaTimes |
|---|---|---|
| **Frequency** | Daily news | Monthly digital magazine |
| **Format** | Rolling feed, breaking news | Curated editorial issues |
| **Model** | GroundNews + Daily Wire | Jeune Afrique (print-style digital) |
| **Platform** | Echo News CMS (this project) | Custom build (separate project) |
| **Content** | RSS aggregation + AI rewriting | Editorial synthesis of weekly news |
| **Distribution** | Web + daily newsletter + social | Monthly email + downloadable PDF |

---

## Core Features (500 Sprints)

### Phase 1: Echo CMS Baseline (Sprints 1-50)
- [ ] Local dev environment (Laravel Sail or native)
- [ ] Database setup (MySQL)
- [ ] Admin customization (URL, branding)
- [ ] Theme customization (Grimba brand)
- [ ] Multi-language support (FR/EN)
- [ ] RSS feed integration
- [ ] User authentication
- [ ] Role management (editor, author, viewer)

### Phase 2: RSS Aggregation Engine (Sprints 51-100)
- [ ] Multi-source RSS parser
- [ ] Auto-categorization (politics, tech, sports, etc.)
- [ ] Deduplication (same story from multiple sources)
- [ ] Priority scoring (breaking news vs routine)
- [ ] Source trust ratings

### Phase 3: AI Rewriting Portal (Sprints 101-200)
- [ ] LLM integration (Anthropic Claude, open-source fallbacks)
- [ ] Article summarization (short/medium/long)
- [ ] Multi-language rewrite (FR ↔ EN ↔ PT-BR)
- [ ] Tone adjustment (neutral, analytical, opinionated)
- [ ] Plagiarism check (ensure uniqueness)
- [ ] SEO optimization (meta tags, keywords)

### Phase 4: GroundNews-Inspired Bias Tagging (Sprints 201-250)
- [ ] Political leaning detection (left/center/right)
- [ ] Source bias metadata (per-article + per-source)
- [ ] Visual bias indicators (UI badges, color coding)
- [ ] "Blindspot" detection (stories covered by only one side)
- [ ] Coverage comparison (same story, different angles)

### Phase 5: Newsletter System (Sprints 251-300)
- [ ] Daily digest generation (AI-curated top stories)
- [ ] Email templates (FR/EN variants)
- [ ] Acelle Mail integration (self-hosted)
- [ ] Subscription management (free vs premium tiers)
- [ ] Analytics (open rates, click-through)

### Phase 6: Multi-Channel Distribution (Sprints 301-350)
- [ ] WhatsApp Business API integration
- [ ] Telegram bot (daily digest)
- [ ] Twitter/X auto-posting (Stackposts integration)
- [ ] Push notifications (web + PWA)
- [ ] RSS-to-social automation

### Phase 7: Francophone Optimization (Sprints 351-400)
- [ ] French-first UI (default, not toggle)
- [ ] African news source partnerships
- [ ] Regional editions (West Africa, Central Africa, diaspora)
- [ ] Currency localization (XAF, XOF, EUR, USD)
- [ ] Mobile-first design (low-bandwidth modes)

### Phase 8: Premium Features (Sprints 401-450)
- [ ] Paywall (metered + subscription)
- [ ] Ad-free tier
- [ ] Exclusive analysis (AI deep-dives)
- [ ] Archive access (historical stories)
- [ ] Custom feeds (user-curated topics)

### Phase 9: Observability & Scale (Sprints 451-500)
- [ ] Analytics dashboard (page views, engagement)
- [ ] Performance optimization (caching, CDN)
- [ ] Security hardening (CSP, rate limiting)
- [ ] Backup automation (DB + assets)
- [ ] CI/CD pipeline (GitHub Actions → VPS)

---

## Team Assignments

| Role | Name | Responsibility |
|---|---|---|
| **UI/UX Design Lead** | Steve Jobs | Grimba brand design language, bias visualization, newsletter templates, mobile UX |
| **Backend/API Lead** | Elon Musk | RSS engine, AI portal, LLM routing, bias detection algorithms, distribution APIs |
| **Editorial Lead** | TBD (Editor-in-Chief) | Content strategy, source partnerships, editorial guidelines |
| **DevOps Lead** | Sara Kim | Local dev setup, VPS deployment, CI/CD, monitoring |

---

## Local Development Setup

```bash
# Navigate to project
cd /Users/vb/GrimbaNews

# Install dependencies
composer install
npm install

# Configure environment
cp .env.example .env
# Edit: DB credentials, APP_URL, ADMIN_DIR

# Generate app key (if needed)
php artisan key:generate

# Run migrations
php artisan migrate --seed

# Start dev server
php artisan serve
# Or use Laravel Sail:
# ./vendor/bin/sail up

# Access admin
# http://localhost:8000/{ADMIN_DIR}
```

---

## Git Hygiene (Standing Orders)

- **Commit cadence:** Every sprint → commit → push to `darkvaderfr/grimbanews`
- **No direct prod edits:** All changes via local dev → git → deploy
- **Commit message format:**
  ```
  Sprint {N}: {feature name}

  Co-Authored-By: Claude Opus 4.7 <noreply@anthropic.com>
  ```
- **Stage specific files:** Never `git add -A` — name files explicitly

---

## Credentials (To Be Added)

| Credential | Purpose | Where |
|---|---|---|
| MySQL database | Local dev | `.env` (`DB_*`) |
| Anthropic API key | AI rewriting | `.env` (`ANTHROPIC_API_KEY`) |
| Acelle Mail | Newsletter | Sprint 251+ |
| WhatsApp Business API | Distribution | Sprint 301+ |
| Telegram Bot Token | Distribution | Sprint 301+ |

---

## Success Metrics (500 Sprints Complete)

- [ ] 500 sprints completed (100% roadmap)
- [ ] 50+ RSS sources integrated
- [ ] <5 min latency (RSS → published article)
- [ ] 95%+ article uniqueness score (AI rewrite)
- [ ] Bias tagging accuracy >90% (validated)
- [ ] 10K+ daily newsletter subscribers
- [ ] <2s page load time (Lighthouse)
- [ ] 99.9% uptime (prod)

---

## Next Actions

1. **Mythos:** Generate 500-sprint detailed plan (this session)
2. **Steve:** Design audit — Echo CMS default theme → Grimba brand
3. **Elon:** Backend audit — RSS engine architecture, LLM integration points
4. **DevOps:** Local environment validation (composer, npm, db migrate)

---

*This is a living document. Updated after each sprint.*
