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
                <a href="{{ url('/') }}" class="grimba-wordmark grimba-wordmark--light" aria-label="Grimba News — accueil">
                    <span class="grimba-wordmark__mark">Grimba</span>
                    <span class="grimba-wordmark__tag">News</span>
                </a>
                <p class="small opacity-75 mt-3 mb-0">
                    Voyez chaque angle de chaque histoire. GrimbaNews classe les biais,
                    détecte les angles morts et compare les sources — en français.
                </p>
            </div>

            <div class="col-lg-2 col-md-6 col-6">
                <h4 class="grimba-footer__heading">Lecteur</h4>
                <ul>
                    <li><a href="{{ url('/') }}">Accueil</a></li>
                    <li><a href="{{ url('/pour-vous') }}">Pour vous</a></li>
                    <li><a href="{{ url('/local') }}">Local</a></li>
                    <li><a href="{{ url('/angles-morts') }}">Angles morts</a></li>
                    <li><a href="{{ url('/comparatif') }}">Comparatif</a></li>
                </ul>
            </div>

            <div class="col-lg-3 col-md-6 col-6">
                <h4 class="grimba-footer__heading">Sujets</h4>
                <ul>
                    @foreach($footerTopics as $t)
                        <li><a href="{{ $t->url }}">{{ $t->name }}</a></li>
                    @endforeach
                </ul>
            </div>

            <div class="col-lg-2 col-md-6 col-6">
                <h4 class="grimba-footer__heading">Sources</h4>
                <ul>
                    <li><a href="{{ url('/sources') }}">Tous les médias</a></li>
                    <li><a href="{{ url('/proprietaires') }}">Carte des propriétaires</a></li>
                    <li><a href="{{ url('/methodologie') }}">Méthodologie</a></li>
                    <li><a href="{{ url('/feed.xml') }}">Flux RSS</a></li>
                    <li><a href="#newsletter" data-grimba-newsletter-open>Infolettre</a></li>
                </ul>
            </div>

            <div class="col-lg-2 col-md-6 col-6">
                <h4 class="grimba-footer__heading">GrimbaNews</h4>
                <ul>
                    <li><a href="{{ url('/a-propos') }}">À propos</a></li>
                    <li><a href="{{ url('/contact') }}">Contact</a></li>
                    <li><a href="{{ url('/carrieres') }}">Carrières</a></li>
                    @auth('member')
                        <li><a href="{{ url('/account') }}">Mon compte</a></li>
                    @else
                        <li><a href="{{ route('public.member.login') }}">Connexion</a></li>
                        <li><a href="{{ route('public.member.register') }}">Inscription</a></li>
                    @endauth
                </ul>
            </div>
        </div>

        <hr class="grimba-footer__rule">

        <div class="d-flex flex-wrap justify-content-between align-items-center small opacity-75">
            <span>© {{ date('Y') }} GrimbaNews · Iboga Ventures</span>
            <span class="d-flex gap-3">
                <a href="{{ url('/confidentialite') }}">Confidentialité</a>
                <a href="{{ url('/conditions') }}">Conditions</a>
            </span>
        </div>
    </div>
</footer>
