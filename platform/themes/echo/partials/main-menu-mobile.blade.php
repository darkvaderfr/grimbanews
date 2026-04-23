<ul {!! $options !!}>
    @foreach ($menu_nodes->loadMissing('metadata') as $key => $row)
        <li @class([
            'menu-item' => ! $row->has_child,
            'has-droupdown' => $row->has_child,
        ])>
            <a
                @class(['main', 'mobile-menu-link' => ! $row->has_child])
                href="{{ $row->has_child ? '#' : url($row->url) }}"
                title="{{ $row->title }}"
                @if ($row->target !== '_self') target="{{ $row->target }}" @endif
            >
                {{ $row->title }}
            </a>
            @if ($row->has_child)
                {!! Menu::generateMenu([
                    'menu' => $menu,
                    'menu_nodes' => $row->child,
                    'view' => 'main-menu-mobile',
                    'options' => ['class' => 'submenu mm-collapse', 'id' => 'mobile-menu-active'],
                ]) !!}
            @endif
        </li>
    @endforeach
</ul>
