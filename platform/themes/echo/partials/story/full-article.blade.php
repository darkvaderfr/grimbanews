@php
    /**
     * @var \Botble\Blog\Models\Post $post
     * @var string|null $body
     * @var bool $locked
     * @var string|null $loginUrl
     * @var string|null $upstream
     */
    $body = trim((string) ($body ?? ''));
    $locked = (bool) ($locked ?? false);
    $wordCount = $body !== '' ? \Illuminate\Support\Str::wordCount(strip_tags($body)) : 0;
@endphp

@if($locked)
    <section class="grimba-full-article grimba-full-article--locked glass-panel p-3 p-md-4 mb-3" aria-labelledby="grimba-full-article-lock-{{ $post->id }}">
        <div class="grimba-full-article__lock">
            <div>
                <span class="grimba-methodology__kicker">{{ __('Réservé aux abonnés') }}</span>
                <h2 id="grimba-full-article-lock-{{ $post->id }}" class="grimba-full-article__title">{{ __("Lire l'article complet") }}</h2>
                <p class="grimba-full-article__copy">
                    {{ __("Connectez-vous pour lire le texte intégral extrait par GrimbaNews.") }}
                </p>
            </div>
            <a href="{{ $loginUrl ?: url('/login') }}" class="btn-grimba btn-grimba--dark">{{ __('Se connecter') }}</a>
        </div>
    </section>
@elseif($body !== '')
    <section class="grimba-full-article grimba-full-article--reader glass-panel p-3 p-md-4 mb-3" aria-labelledby="grimba-full-article-{{ $post->id }}">
        <header class="grimba-full-article__header">
            <div>
                <span class="grimba-methodology__kicker">{{ __('Texte intégral') }}</span>
                <h2 id="grimba-full-article-{{ $post->id }}" class="grimba-full-article__title">{{ __("Lire l'article complet") }}</h2>
            </div>
            @if($wordCount > 0)
                <span class="grimba-full-article__count">
                    {{ trans_choice(':count mot|:count mots', $wordCount, ['count' => $wordCount]) }}
                </span>
            @endif
        </header>

        <div class="grimba-full-article__body">
            {!! BaseHelper::clean($body) !!}
        </div>

        @if($upstream)
            <p class="grimba-full-article__source">
                {{ __('Source originale') }} :
                <a href="{{ $upstream }}" target="_blank" rel="noopener">
                    {{ $post->source_name ?? __("lire chez l'éditeur") }} ↗
                </a>
            </p>
        @endif
    </section>
@endif
