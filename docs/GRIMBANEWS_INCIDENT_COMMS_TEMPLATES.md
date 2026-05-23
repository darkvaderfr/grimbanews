# GrimbaNews — Incident Communication Templates

**Status:** template library (FR + EN bilingual)
**Owner:** Lucy Leai (Strategy) + Sara Chen (CISO) for breach-class
**Walks:** Mythos S1020 (comms templates) deferred → partial
**Gating dependency:** None — templates are pure operator artefact. Real incidents will customize per event.

## Why this exists

S1020 was honest-deferred as "operator-side comms playbook — not code." The templates themselves *are* the comms playbook. Shipping them as a doc means the moment an incident happens the on-call doesn't draft from blank — they fill in the variables.

All templates ship FR + EN per the bilingual baseline (per `docs/GRIMBANEWS_LAUNCH_READINESS_CHECKLIST.md`).

## Template 1 — Status page incident (initial)

**Trigger:** P0 declared.

**FR:**
> **Nous enquêtons sur un incident affectant GrimbaNews.**
> Symptômes : {courte description, ex: "le site est inaccessible" ou "les flux RSS ne se mettent pas à jour"}.
> Début : {HH:MM UTC}.
> Composants touchés : {liste depuis docs/GRIMBANEWS_STATUS_PAGE_PLAN.md}.
> Nous publierons une mise à jour dans 30 minutes.

**EN:**
> **We're investigating an incident affecting GrimbaNews.**
> Symptoms: {short description}.
> Started: {HH:MM UTC}.
> Affected components: {list from status page plan}.
> Next update in 30 minutes.

## Template 2 — Status page incident (update)

**FR:**
> **Mise à jour {N}** — {HH:MM UTC} : {ce que nous savons / ce que nous faisons}. Prochaine mise à jour dans {15/30/60} minutes.

**EN:**
> **Update {N}** — {HH:MM UTC}: {what we know / what we're doing}. Next update in {15/30/60} minutes.

## Template 3 — Status page incident (resolved)

**FR:**
> **Résolu.** L'incident est résolu depuis {HH:MM UTC}. Cause racine : {1-phrase summary}. Un post-mortem sera publié sous 7 jours.

**EN:**
> **Resolved.** The incident has been resolved as of {HH:MM UTC}. Root cause: {1-sentence summary}. A post-mortem will be published within 7 days.

## Template 4 — Internal Iboga ops Slack/channel (P0)

```
P0 INCIDENT — {timestamp}
Symptom: {one line}
Trigger: {which signal — /health, grimba_automation_runs row, Sentry alert}
On-call: @{Jacob or Hannah}
Secondary: @{the other}
Tier 3: @{Sara for security, Larry for DB, Lucy+Steve for content}
Status page: posting initial within 5 min
Ticket: GH issue link or "to-be-filed"
War room: Iboga ops voice channel
```

## Template 5 — Stakeholder email (P0)

**Subject:** [GrimbaNews] P0 incident in progress — {short symptom}

**Body:**
> Vader / Zenkai,
>
> A P0 incident is in progress on GrimbaNews. Brief summary:
>
> - **Symptom:** {one line}
> - **Started:** {HH:MM UTC}
> - **Affected components:** {list}
> - **On-call response:** Tier 1 {name} ack'd at {HH:MM}, Tier 2 {name} co-investigating, Tier 3 {Sara/Larry/Lucy} engaged.
> - **Status page:** {status.grimbanews.com URL}
>
> Next stakeholder update in 60 min or at resolution, whichever first.
>
> — {On-call name}, GrimbaNews on-call

## Template 6 — Customer email (post-resolution, only if customer-impacting >30 min)

**FR:**
> Bonjour,
>
> Le {date} entre {HH:MM} et {HH:MM} UTC, GrimbaNews a connu un incident qui a {affecté la disponibilité du site / interrompu la mise à jour des flux / ralenti les recherches}. Aucune donnée n'a été perdue ni exposée.
>
> Cause : {1-paragraph plain-language explanation}.
> Mesures : {1-paragraph corrective actions}.
>
> Merci de votre patience. Pour toute question, contactez {contact@grimbanews.com}.
>
> — L'équipe GrimbaNews

**EN:**
> Hi,
>
> On {date} between {HH:MM} and {HH:MM} UTC, GrimbaNews experienced an incident that {affected site availability / interrupted feed updates / slowed search}. No data was lost or exposed.
>
> Cause: {1-paragraph plain-language explanation}.
> Actions: {1-paragraph corrective actions}.
>
> Thank you for your patience. For any questions, contact {contact@grimbanews.com}.
>
> — The GrimbaNews team

## Template 7 — X / social post (P0 >30 min only)

**FR:** "Incident en cours sur GrimbaNews depuis {HH:MM} UTC. Suivi en direct → status.grimbanews.com"

**EN:** "Ongoing incident on GrimbaNews since {HH:MM} UTC. Live updates → status.grimbanews.com"

## Template 8 — Breach notification (GDPR Article 33/34 if applicable)

**Subject:** Notification de violation de données personnelles — GrimbaNews

This template is intentionally a stub — actual breach notification requires Sara Chen (CISO) + retained counsel (per S1851 / `docs/GRIMBANEWS_GDPR_ROPA.md`). The shell here is:

> Conformément à l'article 33 du RGPD, nous vous notifions une violation de données personnelles survenue le {date}. Nature : {description}. Catégories de données concernées : {list}. Conséquences probables : {list}. Mesures prises : {list}. Contact : Sara Chen (CISO), {email}.

Real instance would route through Sara Chen + counsel + the CNIL (FR data-protection authority) within 72 hours per RGPD Article 33.

## Activation checklist

1. Pre-load templates 1-3 into the status-page vendor as canned responses (`docs/GRIMBANEWS_STATUS_PAGE_PLAN.md` activation).
2. Add template 4 as a slash command in the Iboga ops channel (`/incident-p0`).
3. Add template 5 as an email template in operator email client.
4. Translate any future locale catalog (S1101+) into templates 1-3 and 6 as catalogs ship.
5. Counsel review template 8 before first launch (Sara Chen + retained press counsel).

## Cross-references

- Master plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1020 row)
- Sister docs: `docs/GRIMBANEWS_ESCALATION_TIERS.md`, `docs/GRIMBANEWS_STATUS_PAGE_PLAN.md`, `docs/GRIMBANEWS_INCIDENT_RESPONSE_RUNBOOK.md`, `docs/GRIMBANEWS_GDPR_ROPA.md`
- Bilingual baseline: `docs/GRIMBANEWS_LAUNCH_READINESS_CHECKLIST.md`
