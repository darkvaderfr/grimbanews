# GrimbaNews — Info-Pill Rollout Plan

**Owner:** Architect (MYTHOS sprint planner)
**Trigger:** Vader directive 2026-05-16 — "Add an i-pill button that animates and expands to reveal contextual info. Use this process throughout the web app to limit information from the homepage to the blog page where it makes sense."

**Component contract (assumed):**
```blade
@include('platform.themes.echo::partials.info-pill', [
    'id'         => 'unique-anchor',          // for aria-controls
    'placement'  => 'inline'|'corner',         // visual mode
    'aria_label' => __('À propos de cette barre'),
    'body'       => 'Plain text or escaped HTML',
])
```
The pill renders a circular "i" button + a chevron arrow that rotates on expand and reveals a body slot inline beneath the host element. Body should be plain prose, ~1–2 sentences (no nested cards).

**Design intent:** Keep the homepage and high-density landing surfaces visually clean. The L/C/R bar, NobuAI provenance, balance score, blind-spot scoping etc. are *editorial concepts* — most users don't need them explained on first paint, but curious readers should always be one tap away from the why.

**Rule of thumb — do NOT add an info-pill when:**
- The surface already carries a Steve-styled inline lede paragraph that's load-bearing for first impression (e.g. `.grimba-methodology__lede` on the methodology hero — that copy IS the page).
- The element is itself an interactive control whose meaning is obvious from its label (e.g. follow/unfollow `+` button on chips).
- The element is purely decorative (e.g. `.grimba-daily-briefing__veil`, gradient ribbons, aurora layers).
- The host page is already an "explainer" page (`methodology.blade.php` — the whole page is the explanation).

---

## Phase 1 — Home (P0 launch surfaces)

`platform/themes/echo/partials/home/` — these are the home rails where the directive originated. The pill goes next to the header/title of each rail, OR on the visual editorial signal itself (the bias bar, the balance score).

| #    | Surface                  | Element / partial                                                                   | Explanation copy (FR)                                                                                                                                       | Priority |
|------|--------------------------|-------------------------------------------------------------------------------------|-------------------------------------------------------------------------------------------------------------------------------------------------------------|----------|
| 1.1  | Daily briefing bias bar  | `home/daily-briefing.blade.php` → `.grimba-daily-briefing__bar`                     | "Cette barre montre la répartition Gauche / Centre / Droite des sources qui couvrent ce dossier."                                                           | P0       |
| 1.2  | Daily briefing badge     | `home/daily-briefing.blade.php` → `.grimba-daily-briefing__badge` ("Briefing du jour") | "Le briefing du jour, c'est le dossier qui réunit le plus de sources et de camps autour d'une même histoire ce matin."                                       | P0       |
| 1.3  | All-sides rail header    | `home/all-sides-rail.blade.php` → `.grimba-all-sides__title`                        | "Histoires que la gauche, le centre et la droite couvrent en même temps. Quand les trois camps se croisent, le sujet sort vraiment du bruit éditorial."     | P0       |
| 1.4  | All-sides card spectrum  | `home/all-sides-rail.blade.php` → `.grimba-all-sides__spectrum`                     | "Le ruban coloré au-dessus de chaque carte représente la part de couverture par camp politique pour ce dossier."                                            | P0       |
| 1.5  | Hero bias strip          | `home/hero-grid.blade.php` → `.grimba-hero__bias-strip`                             | "Le mince ruban L / C / D sous l'image résume d'un coup d'œil quels camps ont déjà publié sur cette histoire phare."                                        | P0       |
| 1.6  | Hero coverage block      | `home/hero-grid.blade.php` → `.grimba-hero__coverage`                               | "Nombre total de sources et leur appartenance politique sur cette histoire. Tap pour voir le dossier complet."                                              | P0       |
| 1.7  | Briefing rail title      | `home/hero-grid.blade.php` → `.grimba-briefing__title` ("Briefing du jour")          | "La file de gauche, c'est les 6 histoires les plus partagées entre camps ce matin. Triées par diversité de sources, pas par viralité."                       | P0       |
| 1.8  | Regional mix block       | `home/regional-mix.blade.php` → `.grimba-regional-mix__title` ("Les régions, en bref")| "Trois histoires phares par région. C'est l'édition Internationale — choisissez une édition régionale en haut pour zoomer sur Afrique, Europe ou Amériques."| P0       |
| 1.9  | Most-read-by-bias panels | `home/most-read-by-bias.blade.php` → `.grimba-most-read__title`                     | "Ce que lisent en ce moment les visiteurs côté gauche, centre et droite. Un instantané de l'attention publique, pas un classement éditorial GrimbaNews."    | P0       |
| 1.10 | Topic chips rail         | `home/topic-chips.blade.php` → `.grimba-chips` (rail-level pill anchored to the rail container) | "Suivez un sujet en tapant +. Les sujets suivis remontent automatiquement dans votre fil. Pas de compte requis — c'est stocké dans un cookie local."         | P0       |
| 1.11 | Section blocks eyebrow   | `home/section-blocks.blade.php` → `.grimba-section__head--editorial`                | "Chaque rubrique mélange dernières dépêches + angles morts détectés. L'angle mort, c'est une histoire couverte par un seul camp."                            | P1       |
| 1.12 | Latest+topics rail       | `home/latest-plus-topics.blade.php` → `.grimba-latest__title` ("Dernières histoires")| "Flux chronologique des dernières publications, tous camps confondus. Pour filtrer par camp, ouvrez un dossier."                                            | P1       |
| 1.13 | Urgency banner           | `home/urgency-banner.blade.php` → `.grimba-breaking__lede` (eyebrow side, corner placement) | "Le bandeau urgence agrège les dépêches publiées dans les 60 dernières minutes. Il défile en marquee pour montrer l'activité en direct."                     | P1       |
| 1.14 | Feed balance widget      | `home/front-body-hooks.blade.php` (feed-balance entry) → `.grimba-feed-balance__title` | "Votre fil est-il équilibré ? Ce score mesure la diversité des camps représentés dans ce que vous venez de lire."                                            | P1       |

---

## Phase 2 — Dossier / Story Page (P0)

`views/post.blade.php` (when the post is a cluster head — i.e. dossier mode) and `partials/story/*`. The dossier is the densest editorial surface — this is where info-pills earn their keep most. The pill should anchor next to the panel's eyebrow / kicker / title, never inside the bar itself (the bar already has hover tooltips).

| #    | Surface                     | Element / partial                                                                | Explanation copy (FR)                                                                                                                                                                | Priority |
|------|-----------------------------|----------------------------------------------------------------------------------|--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|----------|
| 2.1  | Story page bias bar          | `views/post.blade.php` → `.grimba-story-page__bar`                              | "Cette barre montre, pour ce dossier, combien de sources de chaque camp politique couvrent la même histoire."                                                                         | P0       |
| 2.2  | Bias-distribution title      | `story/bias-distribution.blade.php` → `.grimba-story-distribution__title`        | "Pourcentage de sources par camp parmi les sources classées de ce dossier. Les sources non classées sont exclues du calcul pour ne pas fausser la lecture."                            | P0       |
| 2.3  | Balance score                | `story/bias-distribution.blade.php` → `.grimba-story-distribution__score`        | "Le score Signal va de 0 à 100. 0 = un seul camp couvre. 100 = parfait équilibre Gauche / Centre / Droite. Plus le score est haut, plus le dossier est multi-perspectives."          | P0       |
| 2.4  | Spectrum field               | `story/bias-distribution.blade.php` → `.grimba-story-spectrum`                   | "Chaque pastille est une source placée sur l'axe politique (gauche à droite). Tap une pastille pour ne garder que les articles de cette source dans la liste en bas."                | P0       |
| 2.5  | Origins éditoriales bar      | `story/bias-distribution.blade.php` → `.grimba-story-distribution__origin`       | "D'où viennent géographiquement les sources qui couvrent ce dossier. Aide à repérer si l'histoire est rapportée surtout en local, en région, ou internationalement."                  | P0       |
| 2.6  | Coverage details panel       | `story/coverage-details.blade.php` → `.grimba-story-coverage` header             | "Compte exact des sources par camp, plus la date de la dernière mise à jour. C'est la version chiffres-bruts de la barre au-dessus."                                                  | P0       |
| 2.7  | Dossier-voices title         | `story/dossier-voices.blade.php` → `.grimba-voices__title` ("Comment chaque camp cadre cette histoire") | "Un extrait représentatif par camp. Si un camp manque, c'est qu'aucune source de ce bord n'a couvert l'histoire — un signal éditorial en soi."                                       | P0       |
| 2.8  | Voices absent state          | `story/dossier-voices.blade.php` → `.grimba-voices__absent-hint`                 | "Couverture asymétrique : aucune source de ce camp n'a publié sur ce dossier. C'est ce qu'on appelle un angle mort."                                                                  | P0       |
| 2.9  | Contributing sources table   | `story/dossier-voices.blade.php` → `.grimba-voices__table-title`                 | "Toutes les autres sources qui couvrent le dossier, classées par camp. Tap une ligne pour lire l'article original chez la source."                                                    | P1       |
| 2.10 | Story timeline               | `story/timeline.blade.php` → `.grimba-story-timeline` header                     | "Chronologie des publications dans ce dossier. Permet de voir qui a sorti l'info en premier et comment elle s'est propagée d'un camp à l'autre."                                      | P1       |
| 2.11 | Similar topics aside         | `story/similar-topics.blade.php` → `.grimba-story-similar` header                | "Autres dossiers qui partagent du vocabulaire ou des entités avec celui-ci. Souvent une suite ou une histoire connexe."                                                                | P1       |
| 2.12 | Insights panel               | `views/post.blade.php` → `.grimba-insights-panel__title`                         | "Les Insights sont générés par NobuAI à partir du dossier. Mode extractif = phrases tirées des articles tels quels. Mode NobuAI = synthèse rédigée."                                   | P0       |
| 2.13 | Insights NobuAI mode         | `views/post.blade.php` → `.grimba-insights-panel__badge` (NobuAI dot)            | "Cette synthèse a été générée par NobuAI à partir des articles du dossier. Toujours vérifier la source originale pour les chiffres et citations."                                     | P0       |

---

## Phase 3 — Article single-post & cross-cutting chips (P1)

`views/post.blade.php` (when post is a standalone article, not a cluster head) and the chip partials reused across the site (bias-chip, factuality-chip, ownership-chip, nobuai-chip, country-pill). For chips, the info-pill should be a *sibling* pill — not embedded inside the chip — because chips are dense and used in lists.

| #    | Surface                          | Element / partial                                                       | Explanation copy (FR)                                                                                                                                              | Priority |
|------|----------------------------------|-------------------------------------------------------------------------|--------------------------------------------------------------------------------------------------------------------------------------------------------------------|----------|
| 3.1  | Article header coverage          | `views/post.blade.php` → `.grimba-story-page__header` (when not cluster head) | "Cet article fait partie d'un dossier suivi par GrimbaNews. Pour voir comment les autres camps couvrent la même histoire, ouvrez le dossier complet."                | P1       |
| 3.2  | Bias chip — explainer            | `partials/bias-chip.blade.php` (sibling pill, only in chip legend contexts) | "Le biais éditorial classe une source comme Gauche, Centre ou Droite. Basé sur les positions historiques des éditoriaux, pas sur les articles individuels."          | P1       |
| 3.3  | Factuality chip — explainer      | `partials/factuality-chip.blade.php` (sibling pill in legend contexts)   | "Le score de fiabilité (0–100) mesure la précision factuelle historique d'une source. Calculé à partir de fact-checks indépendants et de corrections publiées."     | P1       |
| 3.4  | Ownership chip — explainer       | `partials/ownership-chip.blade.php` (sibling pill in legend contexts)    | "Qui possède la source : indépendant, groupe média, État, milliardaire, ou non-profit. La propriété influe sur la ligne éditoriale même quand elle ne dicte rien."   | P1       |
| 3.5  | NobuAI chip — explainer          | `partials/nobuai-chip.blade.php` (sibling pill, optional `explainer` flag)| "Ce contenu a été traduit ou résumé par NobuAI, notre moteur d'IA maison. L'article original reste accessible chez la source."                                       | P1       |
| 3.6  | Country pill — explainer         | `partials/country-pill.blade.php` (rail-level pill, not per-pill)        | "Pays d'origine éditoriale de la source. Tap pour filtrer le dossier par pays."                                                                                      | P2       |
| 3.7  | Source diversity meter           | `partials/source-diversity-meter.blade.php` → root container header     | "L'indicateur de diversité estime la variété des camps, pays et propriétaires représentés dans le fil que vous lisez en ce moment."                                  | P1       |
| 3.8  | Reading-time / view counters     | `partials/count-time-to-read.blade.php`, `count-view.blade.php` (skip)  | (Skip — these counters are universally understood; an info-pill here is noise.)                                                                                     | —        |

---

## Phase 4 — Listing pages (P1)

The dedicated landing pages: `breaking`, `latest`, `category`, `blindspot`, `for-you`, `local`, `comparison-index`. Each page already has a Steve-styled lede paragraph (the editorial framing); the info-pill should target the *signal widget* on the page, not duplicate the lede.

| #    | Surface                       | Element / partial                                                                        | Explanation copy (FR)                                                                                                                                              | Priority |
|------|-------------------------------|------------------------------------------------------------------------------------------|--------------------------------------------------------------------------------------------------------------------------------------------------------------------|----------|
| 4.1  | Breaking page kicker          | `views/breaking.blade.php` → `.grimba-breaking-page__kicker--live`                       | "Live = histoires publiées dans la dernière heure. Latest = en cas de creux, on remonte aux 12 dernières heures pour ne jamais afficher une page vide."             | P1       |
| 4.2  | Latest page                   | `views/latest.blade.php` → `.grimba-latest-page__title`                                  | "Flux chronologique strict. Pour filtrer par camp, ouvrez un dossier individuel — chaque dossier porte sa propre répartition L/C/D."                                 | P1       |
| 4.3  | Category page signal bar      | `views/category.blade.php` → `.grimba-category-signal__bar`                              | "La répartition L/C/D agrégée sur l'ensemble des articles publiés dans cette rubrique au cours des 30 derniers jours."                                              | P1       |
| 4.4  | Category page note            | `views/category.blade.php` → `.grimba-category-signal__note` (corner-pill, replaces the inline note copy after rollout) | "Cette ligne note les rubriques où une couverture asymétrique persiste dans le temps. Cliquer ouvre la page Angles morts filtrée sur cette rubrique."               | P1       |
| 4.5  | Category top-sources rail     | `views/category.blade.php` → `.grimba-topic-top-sources__title`                          | "Les sources qui ont le plus contribué à cette rubrique sur 30 jours. Cumul d'articles publiés, pas score d'audience."                                              | P2       |
| 4.6  | Blindspot page filters        | `views/blindspot.blade.php` → tab strip (`role="tablist"` for angles morts filter)       | "Filtrer les angles morts par camp : « Pour la gauche » = histoires que la droite ignore, et vice-versa. Permet de voir ce qu'un camp donné ne raconte pas."        | P1       |
| 4.7  | For-You personal blindspots   | `views/for-you.blade.php` → `#vos-angles-morts` section (`<h2>Vos angles morts personnels`)| "Sujets que votre historique de lecture évite. Calculé localement à partir des chips suivis et des rubriques peu visitées dans votre cookie."                       | P1       |
| 4.8  | Local page form               | `views/local.blade.php` → `.grimba-local__form` (corner-pill on the form)                | "GrimbaNews retient votre ville/pays en cookie local pour vous proposer des sources locales. Aucun compte requis, aucune donnée envoyée à un serveur tiers."         | P1       |
| 4.9  | Comparison index page         | `views/comparison-index.blade.php` → `.grimba-methodology__title`                        | "Tous les dossiers où plusieurs camps couvrent la même histoire. Les sujets « comparables » sont sélectionnés automatiquement par clustering de titres et entités."  | P1       |

---

## Phase 5 — Sources / methodology / advertise / coffre (P2)

| #    | Surface                        | Element / partial                                                          | Explanation copy (FR)                                                                                                                                              | Priority |
|------|--------------------------------|----------------------------------------------------------------------------|--------------------------------------------------------------------------------------------------------------------------------------------------------------------|----------|
| 5.1  | Sources page bias filter       | `views/sources.blade.php` → `.grimba-sources__bias-filter`                 | "Filtrer la liste des sources par classement éditorial. Une source non classée n'a pas encore assez d'historique de publication chez nous pour être étiquetée."     | P2       |
| 5.2  | Sources page fact filter       | `views/sources.blade.php` → `.grimba-sources__fact-filter`                 | "Filtrer par fiabilité factuelle. Les paliers sont : Très haute, Haute, Mixte, Faible — basés sur le score historique de fact-checking de chaque source."           | P2       |
| 5.3  | Source detail distribution     | `views/source.blade.php` → `<h2>Distribution sur GrimbaNews`               | "Comment les articles de cette source se répartissent dans les rubriques de GrimbaNews. Aide à voir où la source publie le plus."                                   | P2       |
| 5.4  | Source detail similar-sources  | `views/source.blade.php` → `.grimba-similar-sources` header                | "Sources jugées similaires sur le plan éditorial (biais, fiabilité, propriété). Utile pour comparer plusieurs voix d'un même bord."                                 | P2       |
| 5.5  | Owners page intro              | `views/owners.blade.php` → `.grimba-methodology__title` ("Propriété des médias")| "Cartographie des conglomérats qui possèdent les sources que vous lisez. Cliquez un propriétaire pour voir toutes ses publications dans GrimbaNews."                | P2       |
| 5.6  | Advertise page audience stats  | `views/advertise.blade.php` → `.grimba-ads-page__hero-stats`               | "Audience GrimbaNews : 4 éditions régionales, FR+EN, 12+ emplacements sponsors disponibles. Tarifs négociés à l'inventaire — contactez-nous pour un devis."          | P2       |
| 5.7  | Coffre (saved articles) intro  | `views/coffre.blade.php` → `.grimba-coffre__lede` (corner pill, not replacing) | "Vos articles sauvegardés sont stockés en local dans votre navigateur. Ils ne quittent pas votre appareil tant que vous ne créez pas de compte."                    | P2       |
| 5.8  | Methodology page section heads | `views/methodology.blade.php` → each `<h2>` (sections 1–7)                 | **Skip** — méthodologie EST l'explication. Ajouter des info-pills ici dupliquerait l'intent de la page entière.                                                     | —        |
| 5.9  | About page                     | `views/about.blade.php` → editorial sections                               | (Audit before adding — about page is already explanatory. Use corner pills only on stats blocks if any.)                                                            | P2       |

---

## Implementation notes

1. **Don't double-attach.** If a host element already has a `title=""` tooltip (e.g. the coverage bar `title="{{ $tooltipBar }}"` in `home/coverage-bar.blade.php`), keep the title for hover power-users and add the pill for tap-first users.
2. **Reuse a copy registry.** Several surfaces in this plan reuse the *same* explanation (the L/C/R bias bar appears on home, dossier, category, for-you). Create `resources/lang/fr/info_pills.php` keyed by purpose (`bias_bar`, `balance_score`, `nobuai_provenance`, `factuality_score`, `ownership`, `blindspot`, `cluster_dossier`, `cookie_storage`). Saves translation churn and guarantees consistency.
3. **Anchor placement convention.** P0 surfaces with a single dominant title → pill goes inline after the title text. Widgets with a kicker + title pair → pill goes inline after the title, not the kicker. Standalone signal widgets (the bar, the spectrum) → corner placement top-right.
4. **Accessibility:** `aria-controls`, `aria-expanded`, `aria-label="À propos de :concept"`, body element gets `role="region"` and is initially `hidden`. Chevron rotation via CSS transform — never animate `height` from 0 to auto (use `grid-template-rows: 0fr → 1fr` trick or measured max-height).
5. **Mobile:** Pills must remain tappable at 32×32 minimum hit area. Body text wraps to ~70 chars max on phone; pill body should never overflow horizontally.
6. **Analytics hook:** every pill expand should fire `grimba:info-pill-open` with `{ pillId, surface }` so we can measure which concepts users actually want explained (and prune the ones nobody opens).
