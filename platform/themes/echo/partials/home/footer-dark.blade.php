@php
    use Botble\Blog\Models\Category;

    $footerTopics = Category::query()
        ->where('status', 'published')
        ->orderBy('order')
        ->limit(6)
        ->get();
@endphp

<footer class="grimba-footer">
    <div class="container-xxl py-5">
        <div class="row g-4">
            <div class="col-lg-3 col-md-6 col-12">
                <a href="{{ url('/') }}" class="grimba-wordmark grimba-wordmark--light" aria-label="{{ __('Grimba News — accueil') }}">
                    <span class="grimba-wordmark__mark">Grimba</span>
                    <span class="grimba-wordmark__tag">News</span>
                </a>
                <p class="small opacity-75 mt-3 mb-0">
                    {{ __('Voyez chaque angle de chaque histoire. GrimbaNews classe les biais, détecte les angles morts et compare les sources.') }}
                </p>
            </div>

            <div class="col-lg-2 col-md-6 col-6">
                <h4 class="grimba-footer__heading">{{ __('Lecteur') }}</h4>
                <ul>
                    <li><a href="{{ url('/') }}">{{ __('Accueil') }}</a></li>
                    <li><a href="{{ url('/breaking') }}">{{ __('Breaking news') }}</a></li>
                    <li><a href="{{ url('/latest') }}">{{ __('Latest news') }}</a></li>
                    <li><a href="{{ url('/pour-vous') }}">{{ __('Pour vous') }}</a></li>
                    <li><a href="{{ url('/local') }}">{{ __('Local') }}</a></li>
                    <li><a href="{{ url('/coffre') }}">{{ __('Mon coffre') }}</a></li>
                    <li><a href="{{ url('/angles-morts') }}">{{ __('Angles morts') }}</a></li>
                    <li><a href="{{ url('/juste-milieu') }}">{{ __('Juste milieu') }}</a></li>
                    <li><a href="{{ url('/comparatif') }}">{{ __('Comparatif') }}</a></li>
                </ul>
            </div>

            <div class="col-lg-3 col-md-6 col-6">
                <h4 class="grimba-footer__heading">{{ __('Sujets') }}</h4>
                <ul>
                    @foreach($footerTopics as $t)
                        <li><a href="{{ $t->url }}">{{ __($t->name) }}</a></li>
                    @endforeach
                </ul>
            </div>

            <div class="col-lg-2 col-md-6 col-6">
                <h4 class="grimba-footer__heading">{{ __('Sources') }}</h4>
                <ul>
                    <li><a href="{{ url('/sources') }}">{{ __('Tous les médias') }}</a></li>
                    <li><a href="{{ url('/proprietaires') }}">{{ __('Carte des propriétaires') }}</a></li>
                    <li><a href="{{ url('/methodologie') }}">{{ __('Méthodologie') }}</a></li>
                    <li><a href="{{ url('/comprendre-le-barometre') }}">{{ __('Comprendre le baromètre') }}</a></li>
                    <li><a href="{{ url('/feed.xml') }}">{{ __('Flux RSS') }}</a></li>
                    <li><a href="{{ url('/feed.breaking.xml') }}">{{ __('RSS · Breaking') }}</a></li>
                    <li><a href="{{ url('/feed.latest.xml') }}">{{ __('RSS · Latest') }}</a></li>
                    <li><a href="#newsletter" data-grimba-newsletter-open>{{ __('Infolettre') }}</a></li>
                </ul>
            </div>

            <div class="col-lg-2 col-md-6 col-6">
                <h4 class="grimba-footer__heading">GrimbaNews</h4>
                <ul>
                    <li><a href="{{ url('/a-propos') }}">{{ __('À propos') }}</a></li>
                    <li><a href="{{ url('/faq') }}">{{ __('FAQ') }}</a></li>
                    <li><a href="{{ url('/contact') }}">{{ __('Contact') }}</a></li>
                    <li><a href="{{ url('/carrieres') }}">{{ __('Carrières') }}</a></li>
                    @auth('member')
                        <li><a href="{{ url('/account') }}">{{ __('Mon compte') }}</a></li>
                    @else
                        <li><a href="{{ route('public.member.login') }}">{{ __('Connexion') }}</a></li>
                        <li><a href="{{ route('public.member.register') }}">{{ __('Inscription') }}</a></li>
                    @endauth
                </ul>
            </div>
        </div>

        <hr class="grimba-footer__rule">

        <div class="d-flex flex-wrap justify-content-between align-items-center small opacity-75">
            <span>© {{ date('Y') }} GrimbaNews · Iboga Ventures</span>
            <span class="d-flex gap-3">
                <a href="{{ url('/confidentialite') }}">{{ __('Confidentialité') }}</a>
                <a href="{{ url('/conditions') }}">{{ __('Conditions') }}</a>
            </span>
        </div>
    </div>
</footer>
