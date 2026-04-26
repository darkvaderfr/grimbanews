@php
    $lang = (string) (request()->cookie('grimba_lang') ?? app()->getLocale() ?? 'fr');
@endphp

@if($lang === 'fr')
    <div class="container-xxl mt-2">
        <div class="grimba-translation-note" role="note">
            {!! Theme::partial('nobuai-chip', ['size' => 'sm']) !!}
            <span>Les articles non francophones sont affichés en français quand une traduction est disponible.</span>
        </div>
    </div>
@endif
