@php
    /**
     * S119 — GrimbaNews-styled override of the Botble newsletter
     * popup. Original was a generic Bootstrap modal with a 268×84
     * placeholder image, plain blue button, and an EN string baked
     * in by the form-builder ("Do not worry we don't spam!").
     *
     * This override drops the form-builder dependency and posts
     * straight to public.newsletter.subscribe with our own field
     * markup, GrimbaNews chrome (paper bg + Fraunces serif title +
     * red accent + glass-panel feel), and the editorial wordmark
     * on the side panel instead of the placeholder.
     *
     * Admin-set theme_option('newsletter_popup_*') values still win:
     * if Vader configures a custom title / subtitle / desktop image
     * via /admin/theme/options, those override our defaults below.
     */
    $title       = theme_option('newsletter_popup_title') ?: 'Chaque matin, chaque angle.';
    $subtitle    = theme_option('newsletter_popup_subtitle') ?: 'Briefing GrimbaNews';
    $description = theme_option('newsletter_popup_description')
        ?: 'Les histoires clés du jour, classées par biais, avec les angles morts que les autres médias ignorent. En français, livrées à 7h.';
    $desktopImage = theme_option('newsletter_popup_image');
    $tabletImage  = theme_option('newsletter_popup_tablet_image') ?: $desktopImage;
    $mobileImage  = theme_option('newsletter_popup_mobile_image')  ?: $tabletImage;
    $hasCustomImage = (bool) ($desktopImage || $tabletImage || $mobileImage);
@endphp

<div class="modal-dialog modal-lg gn-newsletter-modal-dialog">
    <div class="modal-content border-0 gn-newsletter-modal" style="
            background: var(--gn-paper, #f6f1e8);
            color: var(--gn-ink, #1a1713);
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 24px 60px rgba(0,0,0,0.18);
        ">

        <button type="button"
                class="btn-close position-absolute"
                data-bs-dismiss="modal"
                data-dismiss="modal"
                aria-label="Fermer"
                style="
                    top: 12px; right: 14px;
                    background: rgba(0,0,0,0.06);
                    border-radius: 999px;
                    width: 32px; height: 32px;
                    border: none;
                    font-size: 18px;
                    line-height: 1;
                    color: var(--gn-ink, #1a1713);
                    z-index: 5;
                ">×</button>

        <div class="d-flex flex-column flex-lg-row">

            {{-- Side panel: editorial mark + tagline. Replaces the
                 generic 268×84 placeholder. Real wordmark on tan. --}}
            <aside class="gn-newsletter-modal__side col-lg-5"
                   style="
                       background: linear-gradient(155deg, #ecdfc7 0%, #f6f1e8 70%);
                       padding: 36px 32px;
                       display: flex;
                       flex-direction: column;
                       justify-content: space-between;
                       min-height: 280px;
                   ">
                @if($hasCustomImage)
                    <picture>
                        @if($desktopImage)
                            <source srcset="{{ RvMedia::getImageUrl($desktopImage, null, false, RvMedia::getDefaultImage()) }}" media="(min-width: 1200px)">
                        @endif
                        @if($tabletImage)
                            <source srcset="{{ RvMedia::getImageUrl($tabletImage, null, false, RvMedia::getDefaultImage()) }}" media="(min-width: 768px)">
                        @endif
                        @if($mobileImage)
                            <source srcset="{{ RvMedia::getImageUrl($mobileImage, null, false, RvMedia::getDefaultImage()) }}" media="(max-width: 767px)">
                        @endif
                        <img src="{{ RvMedia::getImageUrl($mobileImage ?: $tabletImage ?: $desktopImage, null, false, RvMedia::getDefaultImage()) }}"
                             alt="{{ $title }}"
                             loading="eager"
                             style="width:100%; height:auto; border-radius:8px;">
                    </picture>
                @else
                    {{-- Default: editorial wordmark on tan, no placeholder. --}}
                    <div>
                        <img src="{{ asset('storage/main/general/grimba-logo.svg') }}"
                             alt="GrimbaNews"
                             style="height:42px; width:auto;">
                        <p style="
                                margin-top: 24px;
                                font-family:'Fraunces','Playfair Display',Georgia,serif;
                                font-size: 22px;
                                line-height: 1.25;
                                font-weight: 500;
                                color: var(--gn-ink, #1a1713);
                                opacity: 0.85;
                            ">
                            Voyez chaque<br>angle de chaque<br>histoire.
                        </p>
                    </div>
                    <ul style="
                            list-style:none; padding:0; margin:0;
                            font-size: 13px; line-height: 1.7;
                            color: var(--gn-ink, #1a1713);
                            opacity: 0.75;
                        ">
                        <li>● Trois angles par sujet</li>
                        <li>● Angles morts signalés</li>
                        <li>● Gratuit, désabonnement en un clic</li>
                    </ul>
                @endif
            </aside>

            {{-- Form panel --}}
            <div class="gn-newsletter-modal__form col-lg-7"
                 style="padding: 40px 36px 32px; flex: 1;">

                <span style="
                        display:inline-block;
                        font-family:'Public Sans',system-ui,sans-serif;
                        font-size: 12px;
                        font-weight: 700;
                        letter-spacing: 2.5px;
                        text-transform: uppercase;
                        color: #c0392b;
                        margin-bottom: 10px;
                    ">{!! BaseHelper::clean($subtitle) !!}</span>

                <h2 style="
                        font-family:'Fraunces','Playfair Display',Georgia,serif;
                        font-weight: 600;
                        font-size: clamp(28px, 3.4vw, 38px);
                        line-height: 1.05;
                        letter-spacing: -0.5px;
                        margin: 0 0 14px;
                        color: var(--gn-ink, #1a1713);
                    ">{!! BaseHelper::clean($title) !!}</h2>

                <p style="
                        font-size: 15px;
                        line-height: 1.55;
                        color: var(--gn-ink, #1a1713);
                        opacity: 0.8;
                        margin: 0 0 22px;
                    ">{!! BaseHelper::clean($description) !!}</p>

                <form method="POST"
                      action="{{ route('public.newsletter.subscribe') }}"
                      class="bb-newsletter-popup-form"
                      id="gn-newsletter-popup-form">
                    @csrf
                    <input type="hidden" name="source_key" value="popup">

                    <label for="gn-popup-email"
                           style="display:block; font-size:13px; font-weight:600; margin-bottom:6px; color:var(--gn-ink,#1a1713); opacity:0.85;">
                        {{ trans('plugins/newsletter::newsletter.email_address') }}
                    </label>
                    <input type="email"
                           id="gn-popup-email"
                           name="email"
                           required
                           placeholder="{{ trans('plugins/newsletter::newsletter.enter_your_email') }}"
                           style="
                               width: 100%;
                               padding: 12px 16px;
                               border-radius: 9999px;
                               border: 1px solid rgba(26,23,19,0.18);
                               background: rgba(255,255,255,0.7);
                               font-size: 15px;
                               color: var(--gn-ink, #1a1713);
                               margin-bottom: 14px;
                           ">

                    <button type="submit"
                            class="btn-grimba btn-grimba--solid"
                            style="
                                width: 100%;
                                padding: 12px 18px;
                                border-radius: 9999px;
                                background: var(--gn-ink, #1a1713);
                                color: var(--gn-paper, #f6f1e8);
                                font-family:'Public Sans',system-ui,sans-serif;
                                font-weight: 700;
                                letter-spacing: 0.4px;
                                font-size: 14px;
                                border: none;
                                cursor: pointer;
                                transition: transform 0.15s ease, opacity 0.15s ease;
                            "
                            onmouseover="this.style.opacity='0.92'"
                            onmouseout="this.style.opacity='1'">
                        {{ trans('plugins/newsletter::newsletter.subscribe') }}
                    </button>

                    <label style="
                            display:flex; align-items:center; gap:8px;
                            margin-top: 14px; font-size: 13px;
                            color: var(--gn-ink, #1a1713);
                            opacity: 0.7; cursor: pointer;
                        ">
                        <input type="checkbox" name="dont_show_again" value="1" style="margin:0;">
                        <span>{{ trans('plugins/newsletter::newsletter.dont_show_popup_again') }}</span>
                    </label>

                    <p style="
                            font-size: 12px;
                            line-height: 1.45;
                            color: var(--gn-ink, #1a1713);
                            opacity: 0.55;
                            margin: 14px 0 0;
                        ">
                        Gratuit, désabonnement en un clic. Voir la
                        <a href="{{ url('/confidentialite') }}"
                           style="color:inherit; text-decoration:underline;">politique de confidentialité</a>.
                    </p>
                </form>
            </div>

        </div>
    </div>
</div>
