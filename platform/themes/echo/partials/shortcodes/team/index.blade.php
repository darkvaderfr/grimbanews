<section class="echo-team-area team">
    <div class="container">
        <div class="echo-team-area-inner">
            @if ($title = $shortcode->title)
                <h3 class="section-title">{!! BaseHelper::clean($title) !!}</h3>
            @endif
            <div class="row">
                @foreach($members as $member)
                    <div class="col-lg-3 col-md-4 col-sm-6">
                        <div class="team-wrapper">
                            @if ($avatar = $member->avatar_url)
                                <div class="image-area">
                                    {{ RvMedia::image($avatar, $member->name) }}
                                </div>
                            @endif

                            <div class="content-area">
                                @php
                                    $socialData = $member->getMetaData('social_links', true);
                                @endphp

                                @if (is_array($socialData) && $socials = Theme::convertSocialLinksToArray($socialData))
                                    <ul class="social-icon">
                                        @foreach (array_slice($socials, 0, 4) as $social)
                                            @continue((! $social->getIcon() && ! $social->getImage()) || ! $social->getUrl())
                                            <li>
                                                <a href="{{ $social->getUrl() }}" target="_blank" rel="noopener noreferrer">
                                                    @if ($social->getImage())
                                                        {!! $social->getImageHtml() !!}
                                                    @else
                                                        {!! $social->getIconHtml() !!}
                                                    @endif
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif

                                <div class="content">
                                    <h5 class="title">{{ $member->name }}</h5>
                                    @if ($title = $member->getMetaData('title', true))
                                        <p class="desc">{!! BaseHelper::clean($title) !!}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>
