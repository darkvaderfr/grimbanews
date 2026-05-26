# GrimbaNews — Topic Taxonomy v2 Design (40 buckets)

**Status:** plan v0 (current taxonomy has 14 buckets in `GrimbaEditorialCategories`)
**Owner:** Steve Jobs (CPO) + Lucy Leai (Strategy) + Liam Smith (PM)
**Walks:** Mythos S1031 (topic taxonomy v2 — 40 buckets) deferred → partial
**Gating dependency:** Editorial sign-off on the expanded taxonomy + per-bucket UX scope.

## v1 (current) — 14 buckets in GrimbaEditorialCategories

Politics, Économie, International, Société, Justice, Sports, Culture, Sciences, Tech, Santé, Environnement, Éducation, Régional, Divers.

## v2 (proposed) — 40 buckets

### Politics (5 sub-buckets)
1. Élections — campaign, vote, results
2. Législation — bills, parliament, executive orders
3. Diplomatie — bilateral, EU, UN
4. Défense — military, NATO, foreign policy
5. Géopolitique — strategic alignment, alliances

### Économie (4 sub-buckets)
6. Macroéconomie — GDP, inflation, central banks
7. Marchés financiers — equities, FX, commodities
8. Entreprises — corporate news, M&A, IPOs
9. Travail — labor, unions, employment

### International (3 sub-buckets)
10. Conflits — war, peace, refugees
11. Diplomatie internationale — UN-specific
12. Crises humanitaires — famine, displacement, aid

### Société (5 sub-buckets)
13. Société civile — NGOs, activism, protests
14. Migration — refugees, integration, asylum
15. Inégalités — income, race, gender
16. Famille — parenting, marriage, demography
17. Religion — faith, secularism, conflict

### Justice (3 sub-buckets)
18. Justice pénale — crime, prosecution, prisons
19. Justice civile — courts, judiciary independence
20. Droits humains — civil rights, surveillance

### Sports (3 sub-buckets)
21. Sport professionnel — leagues, transfers, results
22. Sport olympique — quadrennial events
23. Sport amateur + jeunesse — community

### Culture (4 sub-buckets)
24. Arts visuels — exhibits, market, museums
25. Spectacle vivant — theater, dance, opera
26. Musique — releases, tours, industry
27. Cinéma + audiovisuel — film, TV, streaming

### Sciences + Tech (5 sub-buckets)
28. Sciences fondamentales — physics, biology, astronomy
29. IA — research, ethics, regulation
30. Cybersécurité — breaches, defense, policy
31. Plateformes — social media, content moderation
32. Bigtech — Meta/Google/Amazon corporate, antitrust

### Santé (3 sub-buckets)
33. Santé publique — pandemics, vaccines, public-health policy
34. Médecine — research, treatments, healthcare access
35. Bien-être — mental health, nutrition, lifestyle

### Environnement (3 sub-buckets)
36. Climat — climate change, COP, IPCC
37. Biodiversité — wildlife, conservation, ecology
38. Pollution + énergie — fossil, renewables, transition

### Régional (1 sub-bucket; expands via per-region)
39. Local — city/department-scoped (handled by `editorial_region` cross-cut, not bucket count)

### Misc (1 sub-bucket)
40. Société + curiosités — long-form, profiles, off-news

## Migration plan

1. Add `categories.parent_id` (already exists in Botble's Blog plugin) — wire v1 buckets as parents of v2 sub-buckets.
2. `php artisan grimba:remap-categories --from-v1-to-v2` — best-effort auto-remap by keyword match; flag low-confidence rows for editor review.
3. Editor reviews ~5% of posts manually.
4. UI gets a v2 sub-bucket filter on category landing pages (additional facet).
5. v1 buckets stay live; v2 sub-buckets are additive.

## Cross-references

Master plan: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1031).
Sister: `docs/GRIMBANEWS_EDITORIAL_STYLE_GUIDE.md`, per-topic editor roles S1035.
Code: `app/Support/GrimbaEditorialCategories.php`, `database/seeders/CategoriesSeeder.php`.
