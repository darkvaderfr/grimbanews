@php
    /**
     * S145 — site-wide cookie consent overlay.
     *
     * Renders a sticky bottom-right banner asking the visitor to
     * accept / reject non-essential cookies. Hidden once a choice
     * is recorded (cookie `grimba_cookie_consent` = accepted|rejected).
     * Hidden globally when admin sets `grimba_cookie_active` = false.
     *
     * Settings (admin at /admin/grimba/cookies):
     *   grimba_cookie_active        — bool, default true
     *   grimba_cookie_title         — string
     *   grimba_cookie_body          — text (line breaks ok)
     *   grimba_cookie_accept_label  — string
     *   grimba_cookie_reject_label  — string
     *   grimba_cookie_more_label    — string
     *   grimba_cookie_more_url      — string (link target, defaults to /confidentialite)
     *
     * Endpoint (POST, CSRF-protected):
     *   /cookie-consent/{accept|reject}  → sets cookie, returns 204
     *
     * The choice cookie is unencrypted (`grimba_cookie_consent` is in
     * the EncryptCookies::except list) so JS can read/clear it.
     */

    $active = (bool) setting('grimba_cookie_active', true);

    if (! $active) return;

    $existing = (string) request()->cookie('grimba_cookie_consent', '');
    if (in_array($existing, ['accepted', 'rejected', 'necessary', 'essential'], true)) {
        return;
    }

    $title  = (string) setting('grimba_cookie_title',  'Cookies');
    $body   = (string) setting('grimba_cookie_body',
        "GrimbaNews utilise des cookies essentiels pour le fonctionnement du site (préférences linguistiques, mode de lecture, historique de lecture local) et, avec votre accord, des cookies de mesure d'audience anonymisée. Vous pouvez changer d'avis à tout moment depuis la page de confidentialité."
    );
    $accept = (string) setting('grimba_cookie_accept_label', 'Accepter');
    $reject = (string) setting('grimba_cookie_reject_label', 'Refuser les non-essentiels');
    $more   = (string) setting('grimba_cookie_more_label',   'En savoir plus');
    $moreUrl = (string) setting('grimba_cookie_more_url', '/confidentialite');
@endphp

<aside class="grimba-cookie-consent"
       id="grimba-cookie-consent"
       role="dialog"
       aria-labelledby="grimba-cookie-title"
       aria-describedby="grimba-cookie-body"
       aria-modal="false">
    <div class="grimba-cookie-consent__inner">
        <h3 id="grimba-cookie-title" class="grimba-cookie-consent__title">{{ $title }}</h3>
        <p id="grimba-cookie-body" class="grimba-cookie-consent__body">{!! nl2br(e($body)) !!}</p>
        <div class="grimba-cookie-consent__actions">
            <button type="button" data-grimba-cookie="accept" class="grimba-cookie-consent__btn grimba-cookie-consent__btn--accept">
                {{ $accept }}
            </button>
            <button type="button" data-grimba-cookie="reject" class="grimba-cookie-consent__btn grimba-cookie-consent__btn--reject">
                {{ $reject }}
            </button>
            <a href="{{ url($moreUrl) }}" class="grimba-cookie-consent__more">{{ $more }} →</a>
        </div>
    </div>
</aside>

<style>
    .grimba-cookie-consent {
        position: fixed;
        bottom: 18px;
        right: 18px;
        z-index: 60;
        max-width: 420px;
        width: calc(100vw - 36px);
        background: var(--gn-paper, #f6f1e8);
        color: var(--gn-ink, #1a1713);
        border: 1px solid rgba(26, 23, 19, 0.12);
        border-radius: 14px;
        box-shadow: 0 18px 40px rgba(0, 0, 0, 0.18);
        font-family: 'Public Sans', system-ui, sans-serif;
        animation: grimbaCookieIn .35s ease-out both;
    }
    @keyframes grimbaCookieIn {
        from { transform: translateY(20px); opacity: 0; }
        to   { transform: translateY(0); opacity: 1; }
    }
    .grimba-cookie-consent__inner { padding: 18px 20px 16px; }
    .grimba-cookie-consent__title {
        font-family: 'Fraunces','Playfair Display',Georgia,serif;
        font-weight: 600;
        font-size: 22px;
        letter-spacing: -0.3px;
        margin: 0 0 8px;
        color: var(--gn-ink, #1a1713);
    }
    .grimba-cookie-consent__body {
        font-size: 13.5px;
        line-height: 1.5;
        margin: 0 0 14px;
        color: var(--gn-ink, #1a1713);
        opacity: 0.85;
    }
    .grimba-cookie-consent__actions {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        align-items: center;
    }
    .grimba-cookie-consent__btn {
        padding: 9px 16px;
        border-radius: 9999px;
        font-family: 'Public Sans', system-ui, sans-serif;
        font-weight: 700;
        font-size: 13px;
        letter-spacing: 0.3px;
        border: none;
        cursor: pointer;
        transition: opacity .15s ease;
    }
    .grimba-cookie-consent__btn:hover { opacity: 0.9; }
    .grimba-cookie-consent__btn--accept {
        background: var(--gn-ink, #1a1713);
        color: var(--gn-paper, #f6f1e8);
    }
    .grimba-cookie-consent__btn--reject {
        background: transparent;
        color: var(--gn-ink, #1a1713);
        border: 1px solid rgba(26,23,19,0.25);
    }
    .grimba-cookie-consent__more {
        margin-left: auto;
        font-size: 12.5px;
        color: var(--gn-ink, #1a1713);
        opacity: 0.65;
        text-decoration: underline;
    }
    .grimba-cookie-consent--hidden { display: none; }
    @media (max-width: 540px) {
        .grimba-cookie-consent {
            right: 12px; left: 12px; bottom: calc(92px + env(safe-area-inset-bottom));
            max-width: none; width: auto;
            max-height: min(420px, calc(100vh - 150px - env(safe-area-inset-bottom)));
            display: flex;
            overflow: hidden;
            z-index: 1047;
        }
        .grimba-cookie-consent__inner {
            display: flex;
            flex: 1 1 auto;
            flex-direction: column;
            min-height: 0;
            padding: 14px;
        }
        .grimba-cookie-consent__title {
            flex: 0 0 auto;
            font-size: 19px;
            margin-bottom: 7px;
        }
        .grimba-cookie-consent__body {
            flex: 1 1 auto;
            max-height: 8.5rem;
            overflow-y: auto;
            padding-right: 4px;
            font-size: 13px;
            line-height: 1.45;
        }
        .grimba-cookie-consent__actions {
            display: grid;
            flex: 0 0 auto;
            grid-template-columns: 1fr;
            gap: 8px;
        }
        .grimba-cookie-consent__btn {
            min-height: 44px;
            width: 100%;
        }
        .grimba-cookie-consent__more { margin-left: 0; }
    }
</style>

<script>
    (function () {
        const root = document.getElementById('grimba-cookie-consent');
        if (! root) return;

        const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';

        function persist(choice) {
            const yearSec = 60 * 60 * 24 * 365;
            // Cookie is in EncryptCookies::except so the plain value
            // round-trips. Lax SameSite — the same-origin POST on
            // submit is the only state-changing usage.
            document.cookie = 'grimba_cookie_consent=' + encodeURIComponent(choice)
                + '; path=/; max-age=' + yearSec + '; SameSite=Lax';
        }

        function dismiss(choice) {
            persist(choice);
            root.classList.add('grimba-cookie-consent--hidden');
            // Best-effort server log — fire-and-forget. Gracefully
            // tolerated when the network is offline.
            try {
                fetch('/cookie-consent/' + encodeURIComponent(choice), {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json',
                    },
                    keepalive: true,
                }).catch(() => {});
            } catch (e) { /* noop */ }
        }

        root.querySelectorAll('[data-grimba-cookie]').forEach(btn => {
            btn.addEventListener('click', () => {
                const choice = btn.dataset.grimbaCookie === 'accept' ? 'accepted' : 'rejected';
                dismiss(choice);
            });
        });
    })();
</script>
