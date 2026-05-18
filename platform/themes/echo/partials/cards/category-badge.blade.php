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

    $iconCol = is_array($topic) ? null : (data_get($topic, 'icon') ?: null);
@endphp

<span class="{{ $classes }}" data-grimba-cat-badge>
    @if($iconCol)
        <i class="{{ $iconCol }}" aria-hidden="true"></i>
    @endif
    <span>{{ __($topic->name) }}</span>
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
    </style>
@endonce
