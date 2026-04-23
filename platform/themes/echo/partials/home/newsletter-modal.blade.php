@php
    $flash = session('newsletter_flash');
@endphp

<div id="grimba-newsletter-modal" class="grimba-newsletter-modal" aria-hidden="true" role="dialog">
    <div class="grimba-newsletter-modal__backdrop" data-grimba-newsletter-close></div>
    <div class="grimba-newsletter-modal__panel glass-panel" role="document">
        <button type="button" class="grimba-newsletter-modal__close" aria-label="Fermer" data-grimba-newsletter-close>×</button>

        <span class="grimba-methodology__kicker">Infolettre</span>
        <h2 class="grimba-methodology__title mt-2 mb-2">
            Chaque matin, chaque angle.
        </h2>
        <p class="mb-3 opacity-85">
            Les histoires clés du jour, classées par biais, avec les angles morts que
            les autres médias ignorent. En français, livrées à 7h.
        </p>

        @if($flash)
            <div class="grimba-newsletter-modal__flash mb-3" role="status">
                {{ $flash }}
            </div>
        @endif

        <form method="POST" action="{{ route('public.newsletter.subscribe') }}" id="grimba-newsletter-form">
            @csrf
            <input type="hidden" name="source_key" value="header_modal">
            <div class="d-flex gap-2 flex-wrap">
                <input type="email" name="email" required placeholder="votre@email.fr"
                       class="flex-grow-1" style="min-width:220px;padding:0.6rem 0.9rem;border-radius:9999px;border:1px solid rgba(0,0,0,0.12);">
                <button type="submit" class="btn-grimba btn-grimba--solid">S'abonner</button>
            </div>
            <p class="small opacity-75 mt-2 mb-0">
                Gratuit, désabonnement en un clic. Voir la
                <a href="{{ url('/confidentialite') }}" class="text-decoration-underline">politique de confidentialité</a>.
            </p>
        </form>
    </div>
</div>

<script>
    (function () {
        const modal = document.getElementById('grimba-newsletter-modal');
        const openers = document.querySelectorAll('[data-grimba-newsletter-open]');
        const closers = modal.querySelectorAll('[data-grimba-newsletter-close]');

        function open()  { modal.classList.add('is-open'); modal.setAttribute('aria-hidden','false'); }
        function close() { modal.classList.remove('is-open'); modal.setAttribute('aria-hidden','true'); }

        openers.forEach(b => b.addEventListener('click', (e) => { e.preventDefault(); open(); }));
        closers.forEach(b => b.addEventListener('click', close));
        document.addEventListener('keydown', (e) => { if (e.key === 'Escape') close(); });

        @if($flash)
            open();
        @endif
    })();
</script>
