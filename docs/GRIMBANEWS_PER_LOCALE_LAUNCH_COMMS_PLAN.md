# GrimbaNews — Per-Locale Launch Comms Plan

**Status:** plan v0 (gates on per-locale catalog launches first)
**Owner:** Henry Walker (Content & Outreach) on press list + Maria Lopez (Growth & Community) on social + Olivia Davis (Marketing Strategist) on SEO/PR positioning
**Walks:** Mythos S1150 (per-locale launch comms) deferred → partial
**Gating dependency:** S1110/S1120/S1130/S1140 catalog launches first. Per-locale press contacts + native-locale comms writer.

## Why this exists

S1150 honest-deferred as "gates on S1110/S1120/S1130/S1140 catalog launches first." Real dep. The **comms playbook template** is operator-side.

## Comms phase template (per locale)

### T-7 (one week pre-launch)

- Embargoed press release drafted in target locale + EN canonical
- Press list compiled (~15-30 outlets per locale; see per-locale launch checklist for per-locale specifics)
- Social posts queued (LinkedIn, X/Twitter, Mastodon, Threads, Bluesky)
- Newsletter announcement drafted (canonical FR + EN + new locale)
- Internal comms (Lucy + executive team briefed)

### T-1 (one day pre-launch)

- Embargoed press release sent to outlets
- Social posts scheduled to fire at T-0
- Newsletter scheduled to send at T+0:30 (after launch confirmation)
- Webhook to Maria + Henry on success/failure

### T-0 (launch day)

- /:loc/ goes live
- Social posts fire
- Newsletter sends
- Henry monitors press response inbox for embargo-respect / coverage
- Maria monitors social mentions

### T+1 to T+7 (week after)

- Daily press-coverage summary
- Reply to coverage; thank journalists
- Social engagement tracking
- Per-locale Plausible / GA traffic split report
- Editorial reviewer feedback loop

### T+30 retro

- Press coverage tally
- Social impression + engagement metrics
- Per-locale conversion vs FR/EN baseline
- Cost-per-acquisition per locale
- Lessons for next-locale launch

## Press list construction (per locale)

Per-locale press list draws from per-locale launch checklist:
- ES: El País, El Mundo, La Vanguardia, ABC, El Confidencial, eldiario.es, El Diario Vasco
- PT-BR: Folha de S.Paulo, Estadão, O Globo, BBC Brasil, DW Brasil, UOL, El País Brasil, Agência Pública
- DE: Süddeutsche, FAZ, Zeit, NZZ, Standard, DW, Tagesspiegel, Spiegel, taz, Krautreporter
- IT: Corriere della Sera, La Repubblica, La Stampa, Il Sole 24 Ore, Il Post
- AR: Al Jazeera Arabic, Al Arabiya, BBC Arabic, France 24 Arabic, Al Quds Al Arabi
- JA: Asahi, Yomiuri, Nikkei, BBC Japan, Nippon.com
- ZH: BBC Chinese, RFA, RFI Chinese, DW Chinese, Initium Media, The Reporter (TW)
- KO: Chosun, Hankyoreh, Joongang, BBC Korea
- RU: Meduza, Novaya Gazeta, BBC Russian, DW Russian, Echo of Moscow successor
- HE: Haaretz, Times of Israel, Yedioth Ahronoth, Calcalist, +972 Magazine
- HI: The Hindu, Hindustan Times, Indian Express, Scroll, The Wire, BBC Hindi
- SW: Daily Nation, Citizen, Monitor, New Vision, BBC Swahili, East African

## Social channels per locale

- LinkedIn: all locales (international corporate audience)
- X / Twitter: all locales
- Mastodon: all locales (multi-instance per-locale strategy)
- Threads: all locales except ZH (Meta unavailable on mainland; ZH diaspora can be reached via Instagram)
- Bluesky: emerging audience; bilingual posts
- Per-locale platforms: VK (RU diaspora outside RU), WeChat (ZH diaspora), KakaoTalk (KO)

## Cross-references

- Master plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1150 row)
- Sister docs: `docs/GRIMBANEWS_MULTI_LANGUAGE_LAUNCH_OPS.md`, `docs/GRIMBANEWS_ES_LAUNCH_READINESS_CHECKLIST.md`, `docs/GRIMBANEWS_PT_BR_LAUNCH_READINESS_CHECKLIST.md`, `docs/GRIMBANEWS_DE_LAUNCH_READINESS_CHECKLIST.md`
- Existing infrastructure: `App\Support\GrimbaNewsletterDispatch`, social posting via per-channel API (operator-side scheduling, not in repo today)
