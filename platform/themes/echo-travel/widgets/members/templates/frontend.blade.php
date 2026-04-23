@if (is_plugin_active('member') && $members->isNotEmpty())
    @php $memberCount = $members->count() @endphp
    <div @class(['echo-feature-area-right-site-author' => $memberCount > 1])>
        @if ($title = Arr::get($config, 'title'))
            <h5>{!! BaseHelper::clean($title) !!}</h5>
        @endif

        @if ($memberCount > 1)
            <ul class="author-wrapper">
                @foreach($members as $member)
                    <li class="wrapper">
                        <div class="image-area">
                            {{ RvMedia::image($member->avatar_url, $member->name) }}
                        </div>
                        <div class="content">
                            <h6><a href="{{ $member->url }}" class="title-hover">{{ $member->name }}</a></h6>

                            @if ($memberTitle = $member->getMetaData('title', true))
                                <a href="{{ $member->url }}" class="pe-none">{!! BaseHelper::clean($memberTitle) !!}</a>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>
        @else
            @php $member = $members->first(); @endphp
            <div class="echo-author-area">
                <div class="d-flex justify-content-center">
                    <div class="image-area">
                        <a href="{{ $member->url }}">{{ RvMedia::image($member->avatar_url, $member->name, 'thumb') }}</a>
                    </div>
                </div>

                <div class="content">
                    <h4 class="heading-title">
                        <a href="{{ $member->url }}">{{ $member->name }}</a>
                    </h4>
                    @if ($description = $member->description)
                        <p class="desc"> {!! BaseHelper::clean($description) !!}</p>
                    @endif
                </div>
            </div>
        @endif
    </div>
@endif
