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
@endphp

@if($variant === 'pill')
    <button type="button"
            class="grimba-save-btn grimba-save-btn--pill"
            data-grimba-save="{{ $postId }}"
            aria-pressed="false"
            style="
                display:inline-flex; align-items:center; gap:6px;
                padding:6px 14px; border-radius:9999px;
                border:1px solid rgba(26,23,19,0.18);
                background:rgba(255,255,255,0.6);
                color:var(--gn-ink,#1a1713);
                font-family:'Public Sans',system-ui,sans-serif;
                font-weight:600; font-size:13px; cursor:pointer;
                transition:background .15s ease, color .15s ease;
            ">
        <span class="grimba-save-btn__icon" aria-hidden="true" style="font-size:14px;">☆</span>
        <span class="grimba-save-btn__label">Sauvegarder</span>
    </button>
@else
    <button type="button"
            class="grimba-save-btn grimba-save-btn--icon"
            data-grimba-save="{{ $postId }}"
            aria-pressed="false"
            aria-label="Sauvegarder pour plus tard"
            title="Sauvegarder pour plus tard"
            style="
                display:inline-flex; align-items:center; justify-content:center;
                width:30px; height:30px; border-radius:50%;
                border:1px solid rgba(26,23,19,0.15);
                background:rgba(255,255,255,0.6);
                color:var(--gn-ink,#1a1713);
                font-size:15px; line-height:1; cursor:pointer;
                transition:background .15s ease, color .15s ease;
            ">
        <span aria-hidden="true">☆</span>
    </button>
@endif
