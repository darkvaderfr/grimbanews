# GrimbaNews — External Audit Firm Shortlist

**Status:** plan v0
**Owner:** Sara Chen (CISO) + Ray Dalio (CFO)
**Walks:** Mythos S1891 (external-audit firm shortlist) deferred → partial
**Gating dependency:** SOC 2 scope decision (S1811) — without scope, RFP cannot be issued.

## Why this exists

When the internal program is operational, we need an external CPA firm (for SOC 2) and/or accredited certification body (for ISO 27001) to issue the third-party attestation customers want. Selection happens once and binds the company for 1-3 years, so the shortlist matters.

## Per-tier candidates (SOC 2)

### Tier A — Top-3 (best brand recognition, highest cost)

- **Deloitte** — strong fintech / media practice.
- **PwC** — heavy enterprise customer recognition.
- **EY** — global presence.

Notes: Brand-name reports impress enterprise procurement. Cost ~$80-150K per Type II year. Long lead times.

### Tier B — Big-second (great quality, more practical pricing)

- **Schellman** — SOC 2 specialist, fastest to engage.
- **A-LIGN** — strong tech-startup track record.
- **KirkpatrickPrice** — boutique, attentive.
- **BARR Advisory** — well-regarded for first-time engagements.

Notes: Cost ~$30-70K Type II year. Right-sized for our stage. Strong recommendation default.

### Tier C — Boutique / regional

Reserved for stage-1 readiness assessments or pre-audit consulting; not for the attestation itself.

## Per-tier candidates (ISO 27001)

### Accredited certification bodies

- **BSI Group** — UK-based, globally recognized.
- **Bureau Veritas** — strong EU presence.
- **Schellman** — also offers ISO 27001 (single-firm efficiency if combining with SOC 2).
- **TUV SUD** — German technical depth.
- **DNV** — Norwegian, maritime / industrial heritage but expanding.

Notes: Accreditation by UKAS / ANAB matters; non-accredited certs have limited customer value.

## Per-RFP criteria

When the RFP issues (gated on S1811 scope):
- Firm experience with media / publisher SaaS.
- Firm experience with EU + US dual-jurisdiction clients (matters for GDPR-adjacent controls).
- Firm willingness to engage with us at our stage (some won't take pre-revenue or small clients).
- Engagement-partner CV.
- Reference checks with two similar-stage clients.
- Pricing (Type I + Type II Year 1 + Type II Year 2 + Year-3 surveillance).
- Timeline (start-to-report).
- Tooling integration (do they use Drata, Vanta, Secureframe, etc., or our own evidence library).

## Per-decision authority

Final selection requires:
- CISO recommendation.
- CFO concurrence on cost.
- CEO sign-off.

## Cross-references

Master plan: S1891. Sister: `docs/GRIMBANEWS_EXTERNAL_AUDIT_FIRM_ENGAGEMENT.md` (S1892 next), SOC 2 scope decision (S1811, planned).
