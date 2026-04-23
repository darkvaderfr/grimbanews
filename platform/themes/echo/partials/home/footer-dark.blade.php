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
                <a href="{{ url('/') }}" class="grimba-wordmark grimba-wordmark--light" aria-label="GrimbaNews — accueil">
                    <span class="grimba-wordmark__mark">GRIMBA</span>
                    <span class="grimba-wordmark__tag">News</span>
                </a>
                <p class="small opacity-75 mt-3 mb-0">
                    Voyez chaque angle de chaque histoire. GrimbaNews classe les biais,
                    détecte les angles morts et compare les sources — en français.
                </p>
            </div>

            <div class="col-lg-2 col-md-6 col-6">
                <h4 class="grimba-footer__heading">Actualités</h4>
                <ul>
                    <li><a href="{{ url('/') }}">Accueil</a></li>
                    <li><a href="{{ url('/blog') }}">Pour vous</a></li>
                    <li><a href="{{ url('/angles-morts') }}">Angles morts</a></li>
                    <li><a href="{{ url('/sources') }}">Sources</a></li>
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
                <h4 class="grimba-footer__heading">Outils</h4>
                <ul>
                    <li><a href="{{ url('/comparatif') }}">Comparer les sources</a></li>
                    <li><a href="{{ url('/sources') }}">Biais des médias</a></li>
                    <li><a href="#newsletter" data-grimba-newsletter-open>Infolettre</a></li>
                    <li><a href="{{ url('/feed.xml') }}">Flux RSS</a></li>
                    <li><a href="#extension">Extension navigateur</a></li>
                </ul>
            </div>

            <div class="col-lg-2 col-md-6 col-6">
                <h4 class="grimba-footer__heading">GrimbaNews</h4>
                <ul>
                    <li><a href="{{ url('/a-propos') }}">À propos</a></li>
                    <li><a href="{{ url('/contact') }}">Contact</a></li>
                    <li><a href="{{ url('/methodologie') }}">Méthodologie</a></li>
                    <li><a href="{{ url('/carrieres') }}">Carrières</a></li>
                </ul>
            </div>
        </div>

        <hr class="grimba-footer__rule">

        <div class="d-flex flex-wrap justify-content-between align-items-center small opacity-75">
            <span>© {{ date('Y') }} GrimbaNews · Iboga Ventures</span>
            <span class="d-flex gap-3">
                <a href="#fr">FR</a>
                <a href="#en">EN</a>
                <a href="{{ url('/confidentialite') }}">Confidentialité</a>
                <a href="{{ url('/conditions') }}">Conditions</a>
            </span>
        </div>
    </div>
</footer>
