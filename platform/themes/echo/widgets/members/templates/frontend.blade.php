@if (is_plugin_active('member') && $members->isNotEmpty())
    <div class="echo-feature-area-right-site-full-content">
        @if ($title = Arr::get($config, 'title'))
            <div class="echo-feature-area-right-site-audio-title">
                <div class="echo-home-2-title">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="echo-home-2-main-title">
                                <h3>{!! BaseHelper::clean($title) !!}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
        <div class="echo-feature-area-home-2-responsive">
            @foreach($members as $member)
                <div class="echo-feature-area-right-site-audio-news">
                    <div class="echo-feature-area-right-img">
                        <a href="{{ $member->url }}">
                            {{ RvMedia::image($member->avatar_url, $member->name) }}
                        </a>
                    </div>
                    <div class="echo-feature-area-right-audio-text">
                        <h5>
                            <a href="{{ $member->url }}" class="title-hover truncate-custom truncate-1-custom" title="{{ $name = $member->name }}">{{ $name }}</a>
                        </h5>
                        @if ($memberTitle = $member->getMetaData('title', true))
                            <p class="truncate-custom truncate-1-custom desc">{!! BaseHelper::clean($memberTitle) !!}</p>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif
