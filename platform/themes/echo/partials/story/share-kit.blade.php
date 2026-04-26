@php
    $shareUrl = url()->current();
    $shareTitle = trim((string) ($title ?? 'Cette histoire sur GrimbaNews'));
    $tweetText = \Illuminate\Support\Str::limit($shareTitle, 90, '…');
@endphp

<div class="grimba-story-share glass-panel p-3 mb-3">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <div class="small text-uppercase opacity-60 fw-semibold mb-1" style="letter-spacing:0.08em;">Partager cette histoire</div>
            <p class="small mb-0 opacity-75">Envoyez le dossier, pas seulement un titre isolé.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="https://twitter.com/intent/tweet?url={{ urlencode($shareUrl) }}&text={{ urlencode($tweetText) }}"
               target="_blank" rel="noopener"
               class="btn-grimba btn-grimba--ghost btn-grimba--sm">
                Twitter
            </a>
            <a href="https://bsky.app/intent/compose?text={{ urlencode($tweetText . ' ' . $shareUrl) }}"
               target="_blank" rel="noopener"
               class="btn-grimba btn-grimba--ghost btn-grimba--sm">
                Bluesky
            </a>
            <button type="button"
                    class="btn-grimba btn-grimba--solid btn-grimba--sm"
                    data-grimba-copy-link="{{ $shareUrl }}">
                Copier le lien
            </button>
        </div>
    </div>
</div>
