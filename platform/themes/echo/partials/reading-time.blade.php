@php
    /**
     * S179 — reading-time chip. Botble's $post->time_reading only
     * computes from `content`, but RSS-imported posts often only carry
     * `description`. We fall back through full_content → content →
     * description with a fixed 200wpm baseline so every post shows
     * an estimate.
     *
     * Required:
     *   $post — Post|stdClass with content/description/full_content
     * Optional:
     *   $variant — 'chip' (default, pill badge) | 'inline' (text only)
     *   $minWords — minimum word count to even render (default 30 —
     *               below this the estimate isn't worth surfacing).
     */
    $variant  = $variant ?? 'chip';
    $minWords = $minWords ?? 30;

    $source = $post->full_content ?? $post->content ?? $post->description ?? '';
    $words  = str_word_count(strip_tags((string) $source));
    $minutes = $words >= $minWords ? max(1, (int) ceil($words / 200)) : 0;
@endphp

@if($minutes > 0)
    @if($variant === 'inline')
        <span class="grimba-reading-time" title="Estimation à 200 mots/minute">
            ⏱ {{ $minutes }} min
        </span>
    @else
        <span class="grimba-reading-time"
              title="{{ $words }} mots · estimation 200 mots/minute"
              style="
                  display:inline-flex; align-items:center; gap:4px;
                  padding:2px 8px; border-radius:9999px;
                  background:rgba(26,23,19,0.06);
                  color:var(--gn-ink,#1a1713);
                  font-size:11px; font-weight:600; letter-spacing:0.3px;
                  font-family:'Public Sans',system-ui,sans-serif;
              ">
            <span aria-hidden="true">⏱</span>
            <span>{{ $minutes }} min</span>
        </span>
    @endif
@endif
