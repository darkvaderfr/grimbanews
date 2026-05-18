@php
    /*
     * S-CAT-01 (Vader 2026-05-18) — topic category badge.
     *
     * Renders a small pill showing the post's primary TOPIC
     * category (Politique, Sports, Culture, etc.). Vader's
     * directive: "each article is within its category … even for
     * breaking news, top stories, latest stories".
     *
     * Vars:
     *   $post     (object)        — required. Must have
     *                                ->categories loaded.
     *   $variant  (string|null)   — 'dark' for use over dark
     *                                hero overlays, 'light'
     *                                (default) elsewhere.
     *   $size     (string|null)   — 'sm' for tighter card layouts.
     *
     * No-op when the post has no topic category attached (returns
     * empty, so callers don't have to guard the include).
     */
    $post = $post ?? null;
    if (! $post) { return; }
    $topic = \App\Support\GrimbaEditorialCategories::primaryTopicFor($post);
    if (! $topic) { return; }

    $variant = $variant ?? 'light';
    $size    = $size    ?? null;

    $classes = 'grimba-cat-badge';
    if ($variant === 'dark') $classes .= ' grimba-cat-badge--dark';
    if ($size === 'sm')      $classes .= ' grimba-cat-badge--sm';

    // Direct property access — `data_get()` doesn't invoke
    // Eloquent accessors, so $category->url would have come back
    // null on every render. The icon and url accessors live on
    // Botble's Category model.
    $iconCol = is_array($topic)
        ? ($topic['icon'] ?? null)
        : ((string) ($topic->icon ?? '') !== '' ? $topic->icon : null);

    // S-CAT-07 (Vader 2026-05-18) — clickable badge. The category
    // model carries a `url` accessor (Botble Blog). When present,
    // render as role="link" + tabindex=0 + JS click/Enter/Space
    // handlers so the badge navigates without violating HTML5's
    // "no nested <a>" rule on surfaces where the parent card is
    // already a link (hero, briefing, breaking row, dossier card).
    $catUrl = null;
    try {
        if (is_array($topic)) {
            $catUrl = $topic['url'] ?? null;
        } elseif ($topic instanceof \Botble\Blog\Models\Category) {
            // Ensure slugable is loaded before reading the URL
            // accessor. primaryTopicFor() loads it, but a defensive
            // re-load here covers callers that bypass the helper.
            if (! $topic->relationLoaded('slugable')) {
                try { $topic->load('slugable'); } catch (\Throwable $e) {}
            }
            $rawUrl = (string) ($topic->url ?? '');
            $catUrl = $rawUrl !== '' ? $rawUrl : null;
        } else {
            $rawUrl = (string) ($topic->url ?? '');
            $catUrl = $rawUrl !== '' ? $rawUrl : null;
        }
    } catch (\Throwable $e) {
        // Synthesized topic objects (dossier majority-vote) carry
        // only id + name. Accessor lookup fails — non-clickable.
        $catUrl = null;
    }
    $catName = (string) $topic->name;
@endphp

<span
    class="{{ $classes }}"
    data-grimba-cat-badge
    @if($catUrl)
        data-grimba-cat-badge-href="{{ $catUrl }}"
        role="link"
        tabindex="0"
        aria-label="{{ __('Voir la catégorie :name', ['name' => __($catName)]) }}"
    @endif
>
    @if($iconCol)
        <i class="{{ $iconCol }}" aria-hidden="true"></i>
    @endif
    <span>{{ __($catName) }}</span>
</span>

@once
    <style>
        .grimba-cat-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 9px;
            border-radius: 999px;
            background: rgba(192, 57, 43, 0.10);
            color: #c0392b;
            font-family: 'Public Sans', system-ui, sans-serif;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            line-height: 1;
            border: 1px solid rgba(192, 57, 43, 0.18);
        }
        .grimba-cat-badge--sm {
            font-size: 10px;
            padding: 2px 7px;
        }
        .grimba-cat-badge i {
            font-size: 11px;
        }
        .grimba-cat-badge--dark {
            background: rgba(255, 250, 241, 0.18);
            color: #fffaf1;
            border-color: rgba(255, 250, 241, 0.32);
        }
        [data-bs-theme="dark"] .grimba-cat-badge,
        body[data-theme="dark"] .grimba-cat-badge {
            background: rgba(255, 154, 138, 0.16);
            color: #ff9a8a;
            border-color: rgba(255, 154, 138, 0.30);
        }
        [data-bs-theme="dark"] .grimba-cat-badge--dark,
        body[data-theme="dark"] .grimba-cat-badge--dark {
            background: rgba(255, 250, 241, 0.16);
            color: #fffaf1;
            border-color: rgba(255, 250, 241, 0.32);
        }
        /* S-CAT-07 — clickable badge affordance. */
        .grimba-cat-badge[role="link"] {
            cursor: pointer;
            transition: filter .12s ease-out, background .12s ease-out;
        }
        .grimba-cat-badge[role="link"]:hover {
            filter: brightness(0.95);
        }
        .grimba-cat-badge[role="link"]:focus-visible {
            outline: 2px solid rgba(192, 57, 43, 0.55);
            outline-offset: 2px;
        }
    </style>
    <script>
        // S-CAT-07 (Vader 2026-05-18) — clickable badge handler.
        // Uses event delegation so any future badge render works
        // without re-binding. Stops propagation so the parent card
        // link doesn't ALSO navigate to the article — clicking the
        // badge takes you to the category, clicking elsewhere on
        // the card still opens the article.
        (function () {
            if (window.__grimbaCatBadgeNavReady) return;
            window.__grimbaCatBadgeNavReady = true;

            const navigate = (target) => {
                const url = target.getAttribute('data-grimba-cat-badge-href');
                if (url) {
                    window.location.href = url;
                }
            };

            document.addEventListener('click', (e) => {
                const t = e.target.closest('[data-grimba-cat-badge][data-grimba-cat-badge-href]');
                if (!t) return;
                e.preventDefault();
                e.stopPropagation();
                navigate(t);
            }, true);

            document.addEventListener('keydown', (e) => {
                if (e.key !== 'Enter' && e.key !== ' ') return;
                const t = e.target.closest('[data-grimba-cat-badge][data-grimba-cat-badge-href]');
                if (!t) return;
                e.preventDefault();
                e.stopPropagation();
                navigate(t);
            });
        })();
    </script>
@endonce
