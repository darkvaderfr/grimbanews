# GrimbaNews — Syndication Agreement Template (Draft)

**Status:** template v0 (operator-side draft awaiting counsel review)
**Owner:** Lucy Leai (Strategy) + retained counsel
**Walks:** Mythos S1322 (syndication agreement template) deferred → partial
**Gating dependency:** Retained press counsel review (jurisdiction-specific clauses). Draft itself is operator-side.

## Why this exists

S1322 was honest-deferred as "operator-side legal pickup." Drafting the template is the operator-side prep step before counsel can iterate. This document is the v0 syndication agreement template — a counterpart to `docs/GRIMBANEWS_NEWSROOM_PARTNERSHIP_TEMPLATE.md` which covers the broader partnership program. Syndication is the **simplest** partnership tier (content republication, attribution, link-back) so it gets its own short-form template.

## Use case

Use this template when a publisher wants to **republish GrimbaNews aggregated content** under our name on their property, OR when GrimbaNews wants to **republish a partner's content** in our aggregation feed.

Most common direction: inbound (partner publisher syndicates from GrimbaNews) for our white-label digest tier per `docs/GRIMBANEWS_NEWSROOM_PARTNERSHIP_TEMPLATE.md` tier table.

## Template

```
SYNDICATION AGREEMENT

Between: GrimbaNews ("Licensor"), an Iboga Ventures property
         [registered address, contact email]

And:     [Partner Newsroom] ("Licensee")
         [registered address, contact email]

Effective: [date]
Term:      [12 months] renewable

1. GRANT OF LICENSE

   Licensor grants Licensee a non-exclusive, worldwide license to
   republish the Content (defined below) on the Licensee's
   properties listed in Schedule A, subject to the terms herein.

2. CONTENT

   "Content" means articles, story clusters, bias-bar visualizations,
   and curated digests published by Licensor at grimbanews.com
   and delivered via the syndication feed described in Schedule B.

3. ATTRIBUTION (REQUIRED)

   3.1 Each republished item must carry visible attribution in the
       form: "Originally published in GrimbaNews — [original date]"
       with hyperlink to the canonical URL.

   3.2 The Licensee may NOT remove or obscure the byline of the
       original author / aggregator credit.

   3.3 The Licensee MUST preserve source-citations as published.

4. INTEGRITY OF CONTENT

   4.1 The Licensee may format Content to match house style but
       may NOT materially alter meaning.

   4.2 If a correction is issued upstream, Licensee must publish the
       correction on its property within 48 hours of notification.

   4.3 Licensor may withdraw Content for legal / accuracy / editorial
       reasons on 24h notice; Licensee must depublish within 24h.

5. NO SUBLICENSE

   Licensee may NOT sublicense, sell, or transfer the Content rights
   to any third party without written consent.

6. BRAND USAGE

   6.1 Licensee may use the "GrimbaNews" name solely for attribution.
   6.2 Licensee may NOT imply partnership beyond the scope of this
       Agreement.
   6.3 Licensor brand assets are at storage/app/brand/.

7. ADVERTISING + REVENUE

   7.1 Licensee may sell advertising adjacent to syndicated Content,
       subject to brand-safety standards in Schedule C.
   7.2 Per S1326 (per-partner revenue share — deferred), no revenue
       share applies until that scope ships. This Agreement may be
       amended to add a revenue-share schedule when that ships.

8. PROHIBITED USES

   Licensee may NOT use Content:
     - To train any machine-learning model not authorized by
       Licensor.
     - For political campaign messaging.
     - In any context that would violate the editorial-style guide
       (docs/GRIMBANEWS_EDITORIAL_STYLE_GUIDE.md), including
       clickbait framing or out-of-context excerpting.
     - Behind a paywall without prior written consent.

9. TERM + TERMINATION

   9.1 Initial term: 12 months from Effective Date.
   9.2 Auto-renew for successive 12-month terms unless either party
       gives 30 days written notice.
   9.3 On termination: depublish within 7 days; archive rights for
       previously-published items continue subject to attribution.
   9.4 Surviving clauses: 3 (attribution), 4 (integrity / correction),
       6.3 (brand assets), 8 (prohibited uses), 11 (dispute).

10. LIABILITY + INDEMNITY

    10.1 Licensor warrants right to license the Content per Schedule B.
    10.2 Licensee indemnifies Licensor for any claim arising from
         alteration, mis-attribution, or prohibited use.
    10.3 Maximum liability capped at fees paid in prior 12 months
         (or zero for free syndication tiers).

11. DISPUTE RESOLUTION

    11.1 Editorial disputes: routed to GrimbaNews Ombudsman per
         docs/GRIMBANEWS_OMBUDSMAN_CHARTER_DRAFT.md.
    11.2 Commercial disputes: counsel-to-counsel for 30 days;
         then arbitration in [Iboga venue TBD] under [governing law TBD].

12. CONFIDENTIALITY

    Terms of any fees + commercial arrangements are confidential.
    Existence of syndication relationship may be publicly attributed.

13. ENTIRE AGREEMENT

    This document plus Schedules A-C is the full agreement.
    Amendments require written consent of both parties.

SIGNED:

For Licensor (GrimbaNews / Iboga Ventures):
___________________________________  Date: ___________
[Name, Title]

For Licensee ([Partner]):
___________________________________  Date: ___________
[Name, Title]
```

## Schedules (operator-side fill-in)

- **Schedule A** — Licensee's properties + reach figures + audience description.
- **Schedule B** — Syndication-feed endpoint + content slice (all / per-category / per-region / digest-only).
- **Schedule C** — Brand-safety floor (no adult content, no firearms ads adjacent, no political-campaign ads on partner content; mirror editorial-style guide anti-pattern list).

## Pre-counsel-review checklist

Before sending to Lucy → counsel:

- [ ] Confirm Iboga Ventures legal entity for "Licensor" line.
- [ ] Confirm governing-law jurisdiction (likely France — needs CFO/counsel input).
- [ ] Confirm arbitration venue (likely Paris if FR jurisdiction).
- [ ] Pre-fill Schedule C brand-safety floor from editorial-style guide.
- [ ] Confirm per-partner-stream tagging works (gates on S1323 partial).

## Cross-references

- Master plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1322; sister rows S1321, S1325, S1326, S1327)
- Sister docs: `docs/GRIMBANEWS_NEWSROOM_PARTNERSHIP_TEMPLATE.md`, `docs/GRIMBANEWS_EDITORIAL_STYLE_GUIDE.md`, `docs/GRIMBANEWS_OMBUDSMAN_CHARTER_DRAFT.md`, `docs/GRIMBANEWS_VENDOR_REGISTER.md`
- Source seeder: `database/seeders/RssFeedsSeeder.php`
- Iboga roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
