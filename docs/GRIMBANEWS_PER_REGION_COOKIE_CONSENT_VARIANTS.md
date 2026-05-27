# GrimbaNews — Per-Region Cookie Consent Variants

**Status:** plan v0
**Owner:** Sara Chen (CISO) + counsel + per-locale editor
**Walks:** Mythos S1612 (per-region cookie consent banner variant) deferred → partial
**Gating dependency:** Per-jurisdiction counsel review of consent text + behavior.

## Why this exists

Cookie consent rules vary by jurisdiction:
- **EU (GDPR + ePrivacy):** Explicit opt-in required for non-essential cookies. Granular per-purpose consent.
- **UK (UK-GDPR + PECR):** Same as EU post-Brexit.
- **US (state-by-state):** California CCPA, Virginia VCDPA, Colorado CPA — opt-out frameworks.
- **Brazil (LGPD):** Similar to GDPR; explicit consent.
- **Canada (PIPEDA):** Implied consent for non-sensitive.
- **Other:** varies.

## Per-region variant

| Region | Banner type | Default | Opt-in required for |
|---|---|---|---|
| EU + UK | full opt-in banner | nothing accepted | analytics, ads, personalization |
| US CA + VA + CO | opt-out link in footer | analytics + non-targeted ads OK | targeted ads, data sale |
| Brazil | full opt-in banner | nothing | analytics, ads, personalization |
| Canada | minimal banner | analytics OK | targeted ads |
| Other | full opt-in banner (conservative default) | nothing | all non-essential |

## Per-purpose consent (EU + Brazil)

Granular toggles:
- Cookies strictement nécessaires (always on, can't decline)
- Analyse de l'audience (optional)
- Personnalisation des contenus (optional, gates ML feed Wave AAFF)
- Publicité comportementale (optional, applies to programmatic ads)

## Cookie consent log

Per `docs/GRIMBANEWS_CONSENT_LOG_DESIGN.md` (Wave LLL): every accept/decline event logged with timestamp + IP-anonymized + user-agent.

## Cross-references

Master plan: S1612. Sister: `docs/GRIMBANEWS_PER_LOCALE_AD_CONSENT_RULES.md` (Wave WWW), `docs/GRIMBANEWS_CONSENT_LOG_DESIGN.md` (Wave LLL), `docs/GRIMBANEWS_GDPR_ROPA.md`.
