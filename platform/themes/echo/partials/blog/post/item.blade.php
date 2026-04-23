@switch($postStyle)
    @case('card')
        {!! Theme::partial('blog.post.partials.items.card', compact('post')) !!}
    @break

    @case('list')
        {!! Theme::partial('blog.post.partials.items.list', compact('post')) !!}
    @break

    @case('grid')
        {!! Theme::partial('blog.post.partials.items.grid', compact('post')) !!}
    @break
@endswitch
