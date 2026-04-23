<div class="echo-financial-area">
    <div class="content">
        @if (! empty($tabs))
            <ul class="content-wrapper wrapper-2">
                @foreach ($tabs as $tab)
                    @if ($content = $tab['item'])
                        <li
                            @class(['wrapper-item', 'w-100' => $shortcode->number_of_columns == 'col-1'])
                            class="wrapper-item">{{ $content }}</li>
                    @endif
                @endforeach
            </ul>
        @endif
    </div>
</div>
