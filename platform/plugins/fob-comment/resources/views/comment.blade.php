@php
    /**
     * S123 — GrimbaNews-styled override of the fob-comment frontend.
     * Original was form-builder output: light grey panel, sans-serif
     * "Laisser un commentaire" title, generic Bootstrap inputs, plain
     * blue "Publier le commentaire" button. Vader called it ugly.
     *
     * This override drops the form-builder entirely and emits our own
     * markup. Posts to the same route + field names so the existing
     * Comment validators and storage path are preserved. Comment-list
     * fetching JS (fob-comment.js + comment.js) still runs against the
     * untouched .fob-comment-list-* nodes.
     *
     * Plugin helpers we honor: showWebsite / showCookieConsent /
     * enableReCaptcha / emailOptional. ReCaptcha rendering is delegated
     * to the original form when enabled — we fall back to the legacy
     * form-builder output ONLY when reCAPTCHA is on, since rendering
     * the widget requires its server-side hooks.
     */
    Theme::asset()->add('fob-comment-css', asset('vendor/core/plugins/fob-comment/css/comment.css'), version: '1.1.19');
    Theme::asset()
        ->container('footer')
        ->add('fob-comment-js', asset('vendor/core/plugins/fob-comment/js/comment.js'), ['jquery'], version: '1.1.19');

    Theme::registerToastNotification();

    use FriendsOfBotble\Comment\Forms\Fronts\CommentForm;
    use FriendsOfBotble\Comment\Support\CommentHelper;

    $emailOptional   = CommentHelper::isEmailOptional();
    $showWebsite     = CommentHelper::isShowWebsiteField();
    // S145 — comment-form cookie consent is now handled by the
    // site-wide cookie banner (partials/cookie-consent.blade.php).
    // The plugin's per-form checkbox positioned outside the form
    // panel due to CSS conflicts and was non-functional regardless.
    $showCookieConsent = false;
    $reCaptchaOn     = CommentHelper::isEnableReCaptcha();

    $titleText = trans('plugins/fob-comment::comment.front.form.title');
    $noteText  = $emailOptional
        ? trans('plugins/fob-comment::comment.front.form.description_email_optional')
        : trans('plugins/fob-comment::comment.front.form.description');
@endphp

<script>
    window.fobComment = {};

    window.fobComment = {
        listUrl: {{ Js::from(route('fob-comment.public.comments.index', isset($model) ? ['reference_type' => $model::class, 'reference_id' => $model->id] : url()->current())) }},
    };
</script>

<div
    class="fob-comment-list-section gn-comments-list"
    style="display: none"
>
    <h4 class="fob-comment-title fob-comment-list-title gn-comments__list-title"
        style="font-family:'Fraunces','Playfair Display',Georgia,serif; font-weight:600; font-size:24px; letter-spacing:-0.3px; margin-bottom:16px;"></h4>
    <div class="fob-comment-list-wrapper"></div>
</div>

@if ($reCaptchaOn)
    {{-- ReCaptcha needs the form-builder's server-side hooks; keep
         original markup when enabled, only restyled via CSS overrides
         shipped in this view's <style> block at the bottom. --}}
    <div class="fob-comment-form-section gn-comments-form gn-comments-form--legacy"
         style="
            background: var(--gn-paper, #f6f1e8);
            border: 1px solid rgba(26,23,19,0.08);
            border-radius: 14px;
            padding: 28px;
            margin-top: 32px;
         ">
        <h4 class="fob-comment-title fob-comment-form-title"
            style="font-family:'Fraunces','Playfair Display',Georgia,serif; font-weight:600; font-size:28px; letter-spacing:-0.4px; color:var(--gn-ink,#1a1713); margin:0 0 6px;">
            <span class="d-inline-block">{{ $titleText }}</span>
        </h4>
        <p class="fob-comment-form-note small"
           style="color:var(--gn-ink,#1a1713); opacity:0.7; margin:0 0 18px;">{{ $noteText }}</p>

        {!! CommentForm::createWithReference($model)->renderForm() !!}
    </div>
@else
    {{-- Steve-styled native HTML form. Same field names as
         CommentForm::class so the controller validates identically.
         Posts to fob-comment.public.comments.store. --}}
    <div class="fob-comment-form-section gn-comments-form"
         style="
            background: var(--gn-paper, #f6f1e8);
            border: 1px solid rgba(26,23,19,0.08);
            border-radius: 14px;
            padding: 28px 28px 22px;
            margin-top: 32px;
            color: var(--gn-ink, #1a1713);
         ">
        <h4 class="fob-comment-title fob-comment-form-title"
            style="font-family:'Fraunces','Playfair Display',Georgia,serif; font-weight:600; font-size:30px; letter-spacing:-0.4px; color:var(--gn-ink,#1a1713); margin:0 0 6px;">
            <span class="d-inline-block">{{ $titleText }}</span>
        </h4>
        <p class="fob-comment-form-note"
           style="color:var(--gn-ink,#1a1713); opacity:0.7; margin:0 0 22px; font-size:14px;">{{ $noteText }}</p>

        <form method="POST"
              action="{{ route('fob-comment.public.comments.store') }}"
              class="fob-comment-form gn-comments__form"
              novalidate>
            @csrf
            @if (isset($model))
                <input type="hidden" name="reference_id" value="{{ $model->getKey() }}">
                <input type="hidden" name="reference_type" value="{{ get_class($model) }}">
            @else
                <input type="hidden" name="reference_url" value="{{ url()->current() }}">
            @endif

            <label for="gn-comment-content"
                   style="display:block; font-size:13px; font-weight:600; margin-bottom:6px;">
                {{ trans('plugins/fob-comment::comment.common.comment') }} <span style="color:#c0392b;">*</span>
            </label>
            <textarea id="gn-comment-content"
                      name="content"
                      rows="5"
                      required
                      style="
                          width:100%;
                          padding:12px 14px;
                          border-radius:12px;
                          border:1px solid rgba(26,23,19,0.18);
                          background:rgba(255,255,255,0.7);
                          font-family:'Public Sans',system-ui,sans-serif;
                          font-size:15px;
                          color:var(--gn-ink,#1a1713);
                          resize:vertical;
                          margin-bottom:14px;
                      "></textarea>

            <div class="row g-3" style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px,1fr)); gap:14px; margin-bottom:14px;">
                <div>
                    <label for="gn-comment-name"
                           style="display:block; font-size:13px; font-weight:600; margin-bottom:6px;">
                        {{ trans('plugins/fob-comment::comment.common.name') }} <span style="color:#c0392b;">*</span>
                    </label>
                    <input type="text"
                           id="gn-comment-name"
                           name="name"
                           required
                           style="
                               width:100%;
                               padding:11px 14px;
                               border-radius:9999px;
                               border:1px solid rgba(26,23,19,0.18);
                               background:rgba(255,255,255,0.7);
                               font-size:14px;
                               color:var(--gn-ink,#1a1713);
                           ">
                </div>

                <div>
                    <label for="gn-comment-email"
                           style="display:block; font-size:13px; font-weight:600; margin-bottom:6px;">
                        {{ trans('plugins/fob-comment::comment.common.email') }}
                        @if (! $emailOptional)<span style="color:#c0392b;">*</span>@endif
                    </label>
                    <input type="email"
                           id="gn-comment-email"
                           name="email"
                           {{ $emailOptional ? '' : 'required' }}
                           placeholder="{{ trans('plugins/fob-comment::comment.common.email_placeholder') }}"
                           style="
                               width:100%;
                               padding:11px 14px;
                               border-radius:9999px;
                               border:1px solid rgba(26,23,19,0.18);
                               background:rgba(255,255,255,0.7);
                               font-size:14px;
                               color:var(--gn-ink,#1a1713);
                           ">
                </div>
            </div>

            @if ($showWebsite)
                <div style="margin-bottom:14px;">
                    <label for="gn-comment-website"
                           style="display:block; font-size:13px; font-weight:600; margin-bottom:6px;">
                        {{ trans('plugins/fob-comment::comment.common.website') }}
                    </label>
                    <input type="url"
                           id="gn-comment-website"
                           name="website"
                           placeholder="{{ trans('plugins/fob-comment::comment.common.website_placeholder') }}"
                           style="
                               width:100%;
                               padding:11px 14px;
                               border-radius:9999px;
                               border:1px solid rgba(26,23,19,0.18);
                               background:rgba(255,255,255,0.7);
                               font-size:14px;
                               color:var(--gn-ink,#1a1713);
                           ">
                </div>
            @endif

            @if ($showCookieConsent)
                <label style="
                        display:flex; align-items:center; gap:8px;
                        margin-bottom:14px; font-size:13px;
                        color:var(--gn-ink,#1a1713); opacity:0.75;
                        cursor:pointer;
                    ">
                    <input type="checkbox" name="cookie_consent" value="1" style="margin:0;">
                    <span>{{ trans('plugins/fob-comment::comment.front.form.cookie_consent') }}</span>
                </label>
            @endif

            {{-- Vader 2026-05-16 GLASS-BTN-1: glass-pill, centered, reduced
                 padding. Inline overrides removed — the new
                 .btn-grimba--solid rule in grimba-home.css carries the
                 cinematic glass treatment site-wide. --}}
            <div style="display:flex; justify-content:center; margin-top:6px;">
                <button type="submit" class="btn-grimba btn-grimba--solid btn-grimba--sm">
                    {{ trans('plugins/fob-comment::comment.front.form.submit') }}
                </button>
            </div>
        </form>
    </div>
@endif

{{-- Override comment.css defaults that bleed through on rendered
     comment items (fob-comment-list-wrapper) once the JS hydrates. --}}
<style>
    .fob-comment-list-wrapper .fob-comment {
        background: rgba(255,255,255,0.55);
        border: 1px solid rgba(26,23,19,0.08);
        border-radius: 12px;
        padding: 16px 18px;
        margin-bottom: 14px;
    }
    .fob-comment-list-wrapper .fob-comment-author-name {
        font-family:'Fraunces','Playfair Display',Georgia,serif;
        font-weight: 600;
        font-size: 16px;
        color: var(--gn-ink, #1a1713);
    }
    .fob-comment-list-wrapper .fob-comment-content {
        font-size: 15px;
        line-height: 1.55;
        color: var(--gn-ink, #1a1713);
    }
    .fob-comment-list-wrapper .fob-comment-reply-link,
    .fob-comment-list-wrapper a {
        color: #c0392b;
    }
</style>
