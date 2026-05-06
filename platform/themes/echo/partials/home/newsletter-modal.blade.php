@php
    $flash = session('newsletter_flash');
@endphp

<div id="grimba-newsletter-modal" class="grimba-newsletter-modal" aria-hidden="true" aria-modal="true" role="dialog" aria-labelledby="grimba-newsletter-title">
    <div class="grimba-newsletter-modal__backdrop" data-grimba-newsletter-close></div>
    <div class="grimba-newsletter-modal__panel glass-panel" role="document">
        <button type="button" class="grimba-newsletter-modal__close" aria-label="{{ __('Fermer') }}" data-grimba-newsletter-close>×</button>

        <span class="grimba-methodology__kicker">{{ __('Infolettre') }}</span>
        <h2 id="grimba-newsletter-title" class="grimba-methodology__title mt-2 mb-2">
            {{ __('Chaque matin, chaque angle.') }}
        </h2>
        <p class="mb-3 opacity-85">
            {{ __('Les histoires clés du jour, classées par biais, avec les angles morts que les autres médias ignorent. En français, livrées à 7h.') }}
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
                <button type="submit" class="btn-grimba btn-grimba--solid">{{ __("S'abonner") }}</button>
            </div>
            <p class="small opacity-75 mt-2 mb-0">
                {{ __('Gratuit, désabonnement en un clic. Voir la') }}
                <a href="{{ url('/confidentialite') }}" class="text-decoration-underline">{{ __('politique de confidentialité') }}</a>.
            </p>
        </form>
    </div>
</div>

<script>
    (function () {
        const modal = document.getElementById('grimba-newsletter-modal');
        const closers = modal.querySelectorAll('[data-grimba-newsletter-close]');
        const trap = window.GrimbaFocus?.trap(modal, {
            initialFocus: 'input[name="email"]',
            onEscape: close
        });

        function open(trigger) {
            modal.classList.add('is-open');
            modal.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
            trap?.activate(trigger);
        }

        function close() {
            modal.classList.remove('is-open');
            modal.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
            trap?.deactivate();
        }

        document.addEventListener('click', (e) => {
            const opener = e.target?.closest?.('[data-grimba-newsletter-open]');
            if (!opener) return;
            e.preventDefault();
            open(opener);
        });
        closers.forEach(b => b.addEventListener('click', close));

        @if($flash)
            open();
        @endif
    })();
</script>
