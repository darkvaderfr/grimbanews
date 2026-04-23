@php
    use Botble\Blog\Models\Category;

    $chips = Category::query()
        ->where('status', 'published')
        ->orderBy('order')
        ->limit(14)
        ->get();
@endphp

@if($chips->isNotEmpty())
    <div class="grimba-chips" aria-label="Sujets à suivre">
        <div class="container-xxl">
            <div class="grimba-chips__row">
                @foreach($chips as $chip)
                    <a class="grimba-chip" href="{{ $chip->url }}">
                        <span class="grimba-chip__label">{{ $chip->name }}</span>
                        <span class="grimba-chip__follow" aria-label="Suivre {{ $chip->name }}">+</span>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
@endif
