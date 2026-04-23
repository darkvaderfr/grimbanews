@php
    Theme::set('pageTemplate', $page->template);
    Theme::set('pageTitle', $page->name);
    Theme::set('breadcrumb_background_image', $page->getMetaData('breadcrumb_background_image', true));
    Theme::set('breadcrumb_background_color', $page->getMetaData('breadcrumb_background_color', true));
    Theme::set('breadcrumb_text_color', $page->getMetaData('breadcrumb_text_color', true));
    Theme::set('isHomepage', $isHomepage = BaseHelper::isHomepage($page->getKey()))
@endphp

<div class="main" style="min-height: 300px">
    {!! apply_filters(PAGE_FILTER_FRONT_PAGE_CONTENT, Html::tag('div',
        BaseHelper::clean($page->content), ['class' => ! $isHomepage ? 'ck-content' : ''])->toHtml(), $page)
    !!}
</div>
