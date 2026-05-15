# GrimbaNews Source Classification Methodology

Last updated: 2026-05-15

## Goal

GrimbaNews classifies sources so article pages, dossiers, and source breakdowns can show bias, country of origin, ownership, and factuality without leaving active publishers in an `unknown` state.

## What The Classifier Updates

The automated classifier writes only source-level metadata:

- `bias_rating`: `left`, `center`, `right`, or unchanged when confidence is not high enough.
- `bias_score`: `-1.0`, `0.0`, `1.0` when the column exists.
- `country`: ISO-3166 alpha-2 country of source origin.
- `language`: default publication language when known.
- `ownership_type`: one of the app ownership buckets such as `conglomerate`, `government`, `independent`, `corporation`, `individual`, `telecom`, or `private_equity`.
- `owner_name`: public-record parent owner or broadcaster structure when known.
- `credibility_score`: 0-100 source-level factuality proxy.
- `classification_confidence`, `classification_method`, `classified_at`: audit fields for automated updates.

## Rules

1. The classifier uses a conservative public-record map in `App\Support\GrimbaSourceClassifier`.
2. Domain matches beat source-name matches.
3. Country-only inference can fill country/language, but it does not invent political bias.
4. Existing editor classifications are preserved by default. Use `--overwrite` only for a deliberate refresh.
5. Any source note containing `source-classifier:manual-lock` is skipped.
6. Dry-run is the default. `--apply` is required to write to the database.
7. The scheduled run uses `--apply --sync-posts --min-confidence=80` once per day.

## Commands

Dry run:

```sh
php artisan grimba:classify-sources
```

Apply and sync article metadata that is still missing or unknown:

```sh
php artisan grimba:classify-sources --apply --sync-posts
```

Review every source, including already complete rows:

```sh
php artisan grimba:classify-sources --include-classified
```

Intentional full refresh:

```sh
php artisan grimba:classify-sources --apply --sync-posts --overwrite
```

## Maintenance

When a new recurring unclassified source appears in `grimba:health`, add a profile to `GrimbaSourceClassifier::DOMAIN_PROFILES` and a name alias to `NAME_TO_DOMAIN` when needed. Prefer a domain-keyed entry with public ownership, country, language, bias, and factuality fields.
