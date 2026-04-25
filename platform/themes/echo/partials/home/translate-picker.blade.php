@php
    /**
     * S160 — binary NobuAI translation toggle.
     *
     * Replaces the previous 3-mode dropdown ("VO / Auto / VO + Auto").
     * Vader's call: keep it as simple as the FR/EN language toggle —
     * one pill, two states. When NobuAI is on, every article renders
     * in the site's currently-selected locale (set by the lang-switch
     * just to its left). When off, articles render in their original
     * language.
     *
     * Cookie values:
     *   grimba_translate=original  → show post.name (default)
     *   grimba_translate=auto      → show post.translated_name when
     *                                 translated_to matches the
     *                                 reader's grimba_lang cookie
     *
     * The legacy 'both' value is migrated to 'auto' on read — readers
     * who had it set get the cleaner experience automatically.
     */
    $raw = (string) (request()->cookie('grimba_translate') ?? 'original');
    if ($raw === 'both') $raw = 'auto';
    $mode = in_array($raw, ['original', 'auto'], true) ? $raw : 'original';
@endphp

{{-- S164 — visible label + globe icon. Vader: "Maintenant aucun
     utilisateur ne saura a quoi ca sert sauf apres click." Add an
     in-context label so the toggle's purpose is read at a glance. --}}
<div class="grimba-translate-toggle" style="display:inline-flex; align-items:center; gap:8px;">
    <span aria-hidden="true" style="font-size:14px; opacity:0.65;" title="{{ __('Traduction NobuAI') }}">🌐</span>
    <span class="small fw-semibold" style="opacity:0.75; letter-spacing:0.2px;">
        {{ __('Traduction') }}&nbsp;:
    </span>
    <div class="grimba-theme-switch grimba-nobuai-switch" role="radiogroup" aria-label="{{ __('Traduction NobuAI') }}">
        <button type="button"
                data-grimba-translate="original"
                aria-pressed="{{ $mode === 'original' ? 'true' : 'false' }}"
                title="{{ __('Lire dans la langue d\'origine') }}">
            VO
        </button>
        <button type="button"
                data-grimba-translate="auto"
                aria-pressed="{{ $mode === 'auto' ? 'true' : 'false' }}"
                title="{{ __('Traduit par NobuAI dans la langue du site') }}">
            NobuAI
        </button>
    </div>
</div>

<script>
    (function () {
        const buttons = document.querySelectorAll('[data-grimba-translate]');
        if (! buttons.length) return;
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';

        buttons.forEach(btn => btn.addEventListener('click', async () => {
            const mode = btn.dataset.grimbaTranslate;
            const res = await fetch(@json(route('public.translate.set')), {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                body: JSON.stringify({ mode })
            }).then(r => r.json()).catch(() => null);
            if (res && res.ok) window.location.reload();
        }));
    })();
</script>
