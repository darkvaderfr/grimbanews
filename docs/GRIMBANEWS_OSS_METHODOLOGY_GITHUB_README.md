# GrimbaNews — OSS Methodology GitHub README

**Status:** plan v0
**Owner:** Lucy Leai (Strategy) + Michael O'Connor (Technical Writer) + counsel
**Walks:** Mythos S1710 (OSS methodology README) deferred → partial
**Gating dependency:** OSS-license decision (per `docs/GRIMBANEWS_OSS_METHODOLOGY_SCOPE.md` Wave LLL).

## Why this exists

GrimbaNews's cluster engine + bias resolver + signal classifier (Middle Ground / Blindspot) are unique IP that benefits from open-source review. Public GitHub repo with README explaining methodology lets researchers + journalists audit our approach.

## Repo scope

- `app/Support/GrimbaClusterBias.php` (resolver)
- `app/Console/Commands/GrimbaReclassifyClusters.php` (nightly classifier)
- `app/Support/GrimbaSourceBreakdown.php` (cluster bias aggregation)
- Tests covering above (~50 assertions)
- Documentation

## README template

```
# GrimbaNews Bias Classifier (OSS)

Open-source code for the Middle Ground / Blindspot editorial signal
used at grimbanews.com.

## What it does
- Cluster bias resolution: given LCR article counts, classify cluster
  as left / center / right / middle_ground.
- Middle Ground tag: L=R (or differs by 1) AND neither side exceeds
  center → "Juste milieu".
- Blindspot tag: ≥ 80% one camp coverage → blindspot.

## Methodology
See full methodology at grimbanews.com/methodologie#juste-milieu

## License
{AGPL-3.0 / Apache-2.0 — TBD per counsel}

## Contributing
See CONTRIBUTING.md.
```

## License choice

- AGPL-3.0: copyleft, requires open-sourcing derivative works (purist).
- Apache-2.0: permissive, allows commercial-closed-source derivatives.
- Decision: counsel + Vader call.

## Cross-references

Master plan: S1710. Sister: `docs/GRIMBANEWS_OSS_METHODOLOGY_SCOPE.md` (Wave LLL).
