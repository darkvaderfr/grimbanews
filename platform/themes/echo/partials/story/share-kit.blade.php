@php
    /**
     * Share kit — single unified icon row.
     *
     * Vader 2026-05-16: prefer icons for less space. Replaces both
     * the old text-button share kit AND the legacy details-share
     * icon row from the Botble Echo single-post chrome — one
     * surface, no duplication.
     *
     * @var string $title  (Optional) post title fed into the share text.
     */

    $shareUrl = url()->current();
    $shareTitle = trim((string) ($title ?? __('Cette histoire sur GrimbaNews')));
    $shareText = \Illuminate\Support\Str::limit($shareTitle, 110, '…');

    $urlEncoded = rawurlencode($shareUrl);
    $textEncoded = rawurlencode($shareText);
    $combined = rawurlencode($shareText . ' ' . $shareUrl);

    $channels = [
        [
            'name' => 'X',
            'label' => __('Partager sur X'),
            'url' => 'https://twitter.com/intent/tweet?url=' . $urlEncoded . '&text=' . $textEncoded,
            'svg' => '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" width="16" height="16"><path d="M18.244 2H21l-6.51 7.44L22 22h-6.18l-4.83-6.32L5.5 22H2.74l6.94-7.94L2 2h6.32l4.36 5.76L18.244 2Zm-1.084 18h1.84L7.96 4H6.04l11.12 16Z"/></svg>',
        ],
        [
            'name' => 'Bluesky',
            'label' => __('Partager sur Bluesky'),
            'url' => 'https://bsky.app/intent/compose?text=' . $combined,
            'svg' => '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" width="16" height="16"><path d="M6.5 3C3.6 3 2 5.2 2 7.8c0 4.4 4.6 8.7 8 11.6 1.1.9 2 1.6 2 1.6s.9-.7 2-1.6c3.4-2.9 8-7.2 8-11.6C22 5.2 20.4 3 17.5 3 14.5 3 12.5 6 12 7.4c-.5-1.4-2.5-4.4-5.5-4.4Z"/></svg>',
        ],
        [
            'name' => 'Facebook',
            'label' => __('Partager sur Facebook'),
            'url' => 'https://www.facebook.com/sharer/sharer.php?u=' . $urlEncoded,
            'svg' => '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" width="16" height="16"><path d="M22 12c0-5.52-4.48-10-10-10S2 6.48 2 12c0 5 3.66 9.13 8.44 9.88V14.9H7.9V12h2.54V9.8c0-2.51 1.5-3.9 3.78-3.9 1.1 0 2.24.2 2.24.2v2.46h-1.26c-1.24 0-1.63.77-1.63 1.56V12h2.77l-.44 2.9h-2.33v6.98C18.34 21.13 22 17 22 12Z"/></svg>',
        ],
        [
            'name' => 'WhatsApp',
            'label' => __('Partager sur WhatsApp'),
            'url' => 'https://wa.me/?text=' . $combined,
            'svg' => '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" width="16" height="16"><path d="M12.04 2C6.58 2 2.15 6.43 2.15 11.89c0 1.95.51 3.84 1.49 5.52L2 22l4.69-1.6c1.61.88 3.43 1.36 5.31 1.36h.04c5.46 0 9.89-4.43 9.9-9.89 0-2.65-1.04-5.13-2.92-7-1.88-1.88-4.36-2.87-7-2.87Zm0 18.2h-.04c-1.7 0-3.36-.45-4.81-1.32l-.35-.2-2.78.95.95-2.7-.23-.36a8.21 8.21 0 0 1-1.27-4.38c0-4.52 3.69-8.21 8.21-8.21 2.19 0 4.25.85 5.8 2.41a8.16 8.16 0 0 1 2.4 5.81c-.01 4.51-3.7 8.2-8.2 8.2Zm4.5-6.16c-.25-.13-1.47-.72-1.7-.8-.23-.08-.4-.13-.57.13-.16.25-.66.8-.81.97-.15.16-.3.18-.55.05-.25-.13-1.05-.39-2-1.23-.74-.66-1.24-1.47-1.38-1.72-.14-.25-.02-.39.11-.51.11-.11.25-.3.38-.45.13-.15.17-.25.25-.42.08-.16.04-.31-.02-.43-.06-.13-.57-1.37-.78-1.88-.21-.5-.42-.43-.57-.44h-.49c-.16 0-.43.06-.66.31-.23.25-.86.85-.86 2.07 0 1.22.89 2.4 1.01 2.57.12.16 1.75 2.67 4.24 3.74.59.25 1.06.4 1.42.52.6.19 1.14.16 1.57.1.48-.07 1.47-.6 1.68-1.18.21-.58.21-1.07.14-1.18-.06-.11-.23-.18-.49-.31Z"/></svg>',
        ],
        [
            'name' => 'LinkedIn',
            'label' => __('Partager sur LinkedIn'),
            'url' => 'https://www.linkedin.com/sharing/share-offsite/?url=' . $urlEncoded,
            'svg' => '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" width="16" height="16"><path d="M19 3a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h14ZM8.34 17.34V10H6v7.34h2.34Zm-1.17-8.36a1.36 1.36 0 1 0 0-2.72 1.36 1.36 0 0 0 0 2.72ZM18 17.34v-4.02c0-2.16-1.16-3.17-2.7-3.17-1.26 0-1.83.7-2.14 1.18V10H10.8c.03.66 0 7.34 0 7.34h2.34v-4.1c0-.2.01-.41.07-.55.16-.41.54-.84 1.18-.84.83 0 1.16.63 1.16 1.55v3.94H18Z"/></svg>',
        ],
        [
            'name' => 'Email',
            'label' => __('Partager par e-mail'),
            'url' => 'mailto:?subject=' . $textEncoded . '&body=' . $combined,
            'svg' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" width="16" height="16"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>',
        ],
    ];
@endphp

<aside class="grimba-share-kit grimba-editorial-ribbon" aria-label="{{ __('Partager cette histoire') }}">
    <div class="grimba-share-kit__lede">
        <span class="grimba-share-kit__kicker">{{ __('Partager cette histoire') }}</span>
        <p class="grimba-share-kit__copy">{{ __('Envoyez le dossier, pas seulement un titre isolé.') }}</p>
    </div>
    <div class="grimba-share-kit__row">
        @foreach($channels as $ch)
            <a href="{{ $ch['url'] }}"
               target="_blank"
               rel="noopener noreferrer"
               class="grimba-share-kit__btn"
               data-network="{{ strtolower($ch['name']) }}"
               title="{{ $ch['label'] }}"
               aria-label="{{ $ch['label'] }}">
                {!! $ch['svg'] !!}
            </a>
        @endforeach
        <button type="button"
                class="grimba-share-kit__btn grimba-share-kit__btn--copy"
                data-grimba-copy-link="{{ $shareUrl }}"
                title="{{ __('Copier le lien') }}"
                aria-label="{{ __('Copier le lien') }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" width="16" height="16"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.72-1.71"/></svg>
        </button>
    </div>
</aside>

@once
    <style>
        .grimba-share-kit {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
            padding: 18px 22px;
            margin: 18px 0;
        }
        .grimba-share-kit__lede {
            min-width: 0;
        }
        .grimba-share-kit__kicker {
            display: block;
            font-family: 'JetBrains Mono', ui-monospace, monospace;
            font-size: 10.5px;
            font-weight: 800;
            letter-spacing: .14em;
            text-transform: uppercase;
            color: var(--gn-ink, #1a1713);
            margin-bottom: 4px;
        }
        .grimba-share-kit__copy {
            margin: 0;
            font-size: 13.5px;
            line-height: 1.4;
            color: var(--gn-ink-muted, rgba(26, 23, 19, .62));
        }
        .grimba-share-kit__row {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
        }
        .grimba-share-kit__btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: rgba(255, 255, 255, .82);
            border: 1px solid rgba(26, 23, 19, .10);
            color: var(--gn-ink, #1a1713);
            text-decoration: none;
            transition: transform .18s ease, background .18s ease, color .18s ease;
        }
        .grimba-share-kit__btn:hover,
        .grimba-share-kit__btn:focus-visible {
            transform: translateY(-2px);
            background: #14110d;
            color: #fffaf1;
            border-color: #14110d;
        }
        .grimba-share-kit__btn--copy {
            cursor: pointer;
        }
        .grimba-share-kit__btn--copy.is-copied {
            background: #16a34a;
            color: #fffaf1;
            border-color: #16a34a;
        }
        [data-bs-theme="dark"] .grimba-share-kit__btn,
        body[data-theme="dark"] .grimba-share-kit__btn {
            background: rgba(255, 250, 240, .08);
            border-color: rgba(255, 250, 240, .14);
            color: #fffaf0;
        }
        [data-bs-theme="dark"] .grimba-share-kit__btn:hover,
        body[data-theme="dark"] .grimba-share-kit__btn:hover {
            background: #fffaf1;
            color: #14110d;
        }
    </style>

    <script>
        (function () {
            const onCopy = (btn) => {
                const url = btn.getAttribute('data-grimba-copy-link');
                if (!url) return;
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(url).then(() => {
                        btn.classList.add('is-copied');
                        setTimeout(() => btn.classList.remove('is-copied'), 1500);
                    }).catch(() => {});
                }
            };
            document.querySelectorAll('[data-grimba-copy-link]').forEach((btn) => {
                btn.addEventListener('click', () => onCopy(btn));
            });
        })();
    </script>
@endonce
