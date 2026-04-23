@php
    Theme::addBodyAttributes([
        'class' => ltrim(Theme::getBodyAttribute('class') . ' inner-page'),
    ]);
@endphp

<body {!! Theme::bodyAttributes() !!}>
    {!! Theme::partial('body.content') !!}
</body>
