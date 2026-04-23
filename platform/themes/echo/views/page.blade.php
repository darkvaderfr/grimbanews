@php
    Theme::set('pageTemplate', $page->template);
    Theme::set('pageTitle', $page->name);
    Theme::set('breadcrumb_background_image', $page->getMetaData('breadcrumb_background_image', true));
    Theme::set('breadcrumb_background_color', $page->getMetaData('breadcrumb_background_color', true));
    Theme::set('breadcrumb_text_color', $page->getMetaData('breadcrumb_text_color', true));
    Theme::set('isHomepage', $isHomepage = BaseHelper::isHomepage($page->getKey()));
@endphp

@if(! $isHomepage)
    <section class="grimba-page py-5">
        <div class="container">
            <header class="glass-panel p-4 p-md-5 mb-4">
                <span class="grimba-methodology__kicker">{{ $page->name }}</span>
                <h1 class="grimba-methodology__title mt-2 mb-0">{{ $page->name }}</h1>
            </header>

            <article class="grimba-page__body">
                {!! apply_filters(PAGE_FILTER_FRONT_PAGE_CONTENT,
                    Html::tag('div', BaseHelper::clean($page->content), ['class' => 'ck-content'])->toHtml(),
                    $page
                ) !!}
            </article>
        </div>
    </section>
@else
    <div class="main" style="min-height: 300px">
        {!! apply_filters(PAGE_FILTER_FRONT_PAGE_CONTENT,
            Html::tag('div', BaseHelper::clean($page->content), ['class' => ''])->toHtml(),
            $page
        ) !!}
    </div>
@endif
