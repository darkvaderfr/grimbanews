@php
    $lang = (string) (request()->cookie('grimba_lang') ?? app()->getLocale() ?? 'fr');
    $copy = match ($lang) {
        'en' => 'French articles are shown in English when a NobuAI translation is available.',
        'fr' => 'Les articles non francophones sont affichés en français quand une traduction NobuAI est disponible.',
        default => null,
    };
    $shortCopy = match ($lang) {
        'en' => 'NobuAI translations available.',
        'fr' => 'Traductions NobuAI disponibles.',
        default => null,
    };
@endphp

@if($copy)
    <div class="container-xxl mt-2 grimba-translation-note-wrap">
        <div class="grimba-translation-note" role="note">
            {!! Theme::partial('nobuai-chip', ['size' => 'sm']) !!}
            <span class="grimba-translation-note__copy grimba-translation-note__copy--full">{{ $copy }}</span>
            <span class="grimba-translation-note__copy grimba-translation-note__copy--short">{{ $shortCopy }}</span>
        </div>
    </div>
@endif
