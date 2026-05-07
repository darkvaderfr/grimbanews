@php
    /**
     * S173 — "Sauvegarder" button. Toggles the post id in the
     * grimba_vault cookie (CSV of last-saved-first ids, capped at 50).
     * Pure client-side — no auth, no DB writes.
     *
     * Required props:
     *   $post — Post|stdClass with ->id
     * Optional:
     *   $variant — 'icon' (just bookmark glyph, default for cards)
     *            | 'pill' (full button with label, for hero)
     */
    $variant = $variant ?? 'icon';
    $postId = (int) ($post->id ?? 0);
    $saveLabel = __('Sauvegarder');
    $saveForLaterLabel = __('Sauvegarder pour plus tard');
@endphp

@if($variant === 'pill')
    <button type="button"
            class="grimba-save-btn grimba-save-btn--pill"
            data-grimba-save="{{ $postId }}"
            aria-pressed="false">
        <span class="grimba-save-btn__icon" aria-hidden="true">☆</span>
        <span class="grimba-save-btn__label">{{ $saveLabel }}</span>
    </button>
@else
    <button type="button"
            class="grimba-save-btn grimba-save-btn--icon"
            data-grimba-save="{{ $postId }}"
            aria-pressed="false"
            aria-label="{{ $saveForLaterLabel }}"
            title="{{ $saveForLaterLabel }}">
        <span aria-hidden="true">☆</span>
    </button>
@endif
