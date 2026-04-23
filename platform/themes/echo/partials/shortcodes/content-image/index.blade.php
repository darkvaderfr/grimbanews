@php
    $cols = max((int)(substr($shortcode->number_of_columns, -1)), 1);
    $cols = min($cols, 6);
    $rows = array_chunk($tabs, $cols);
@endphp

@foreach ($rows as $row)
    @if ($row[0]['image'])
        <div class="row mb-3">
            @foreach ($row as $item)
                @if ($image = $item['image'])
                    <div class="col">
                        <figure class="figure w-100">
                            {{ RvMedia::image($image, $item['caption'] ?: __('image'), attributes: ['class' => 'figure-img post-style-1-new-img']) }}
                            @if ($caption = $item['caption'])
                                <figcaption class="figure-caption text-center">{!! BaseHelper::clean($caption) !!}</figcaption>
                            @endif
                        </figure>
                    </div>
                @endif
            @endforeach
        </div>
    @endif
@endforeach
