# GrimbaNews — Asset/Threat/Vulnerability/Impact Mapping

**Status:** plan v0
**Owner:** Sara Chen (CISO) + Larry Ellison (DBA) + Jacob Lee (DevOps)
**Walks:** Mythos S1832 (asset-threat-vulnerability-impact mapping) deferred → partial
**Gating dependency:** Risk assessment methodology (Wave LLL) + asset inventory.

## Per-asset mapping template

For each in-scope asset:

```
## Asset: {name}
**Asset class:** data / system / process / vendor / personnel.
**Owner:** {role / name}.
**Confidentiality:** public / internal / confidential / restricted.

## Threats (per-asset)
- T-1: {threat actor + scenario}
- T-2: ...

## Vulnerabilities (per-asset)
- V-1: {existing weakness}
- V-2: ...

## Impact-if-realized (per threat × vulnerability)
- I-T1V1: {operational + financial + reputational + regulatory}
- ...

## Risk score (Inherent)
- Likelihood × Impact = inherent-risk-score.

## Treatment (per Wave SUB-39 risk-treatment plan)
- ...
```

## Per-asset inventory bands

- **DB tier:** posts, members, news_sources, story_clusters, advertiser_leads, member_subscriptions.
- **System tier:** main VPS, backup VPS, CDN, ingest pipeline, NobuAI fleet.
- **Process tier:** ingest cron, classification cron, newsletter delivery, DR drill.
- **Vendor tier:** AWS/SES, Mailgun, Stripe, Anthropic, OpenAI, Cloudflare.
- **Personnel tier:** Vader, Sara, Lucy, Jacob, Larry, Hannah, plus per-region editors.

## Per-quarter mapping review

- Per-quarter: Sara reviews per-asset entries.
- Per-incident: per-affected-asset re-mapping.

## Cross-references

Master plan: S1832. Sister: `docs/GRIMBANEWS_RISK_ASSESSMENT_METHODOLOGY.md` (Wave LLL), `docs/GRIMBANEWS_ISO27001_RISK_TREATMENT_PLAN.md`.
