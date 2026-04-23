@php
    $tabs = Shortcode::fields()->getTabsData(['title', 'description', 'icon', 'icon_image', 'url', 'open_in_new_tab'], $shortcode);
@endphp

<section class="echo-contact-area">
    <div class="echo-contact-content">
        <div class="container">
            <div class="echo-contact-full-content">
                <div class="row">
                    <div class="col-lg-6 col-md-12">
                        <div class="echo-contact-input-field">
                            @if ($title = $shortcode->title)
                                <div class="echo-contact-title">
                                    <h2 class="text-capitalize">{!! BaseHelper::clean($title) !!}</h2>
                                </div>
                            @endif

                            @if ($description = $shortcode->description)
                                <div class="echo-contact-sub-title">
                                    <p>{!! BaseHelper::clean($description) !!}</p>
                                </div>
                            @endif
                            <div class="echo-main-contact-form">
                                <div id="form-messages"></div>
                                {!! $form
                                    ->setFormInputClass('')
                                    ->renderForm()
                                !!}
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-12">
                        <div class="echo-info">
                            @foreach($tabs as $tab)
                                @continue (! ($title = Arr::get($tab, 'title')))

                                <div class="echo-address">
                                    @if ($iconImage = Arr::get($tab, 'icon_image'))
                                        <span class="echo-address-icon d-flex align-items-center justify-content-center">
                                            {!! RvMedia::image($iconImage, $title) !!}
                                        </span>
                                    @elseif($icon = Arr::get($tab, 'icon'))
                                        <span class="echo-address-icon d-flex align-items-center justify-content-center">
                                            {!! BaseHelper::renderIcon($icon) !!}
                                        </span>
                                    @endif

                                    <h6 class="text-capitalize">{{ $title }}</h6>

                                    @if ($description = BaseHelper::clean(Arr::get($tab, 'description')))
                                        @if ($url = Arr::get($tab, 'url'))
                                            {!! Html::link($url, $description, ['target' => Arr::get($tab, 'open_in_new_tab', 'yes') == 'yes' ? '_blank' : '_self']) !!}
                                        @else
                                            <span>{!! $description !!}</span>
                                        @endif
                                    @endif
                                </div>
                            @endforeach

                            @if ($address = $shortcode->address)
                                <div class="echo-map">
                                    <iframe class="contact-map" src="https://maps.google.com/maps?q={{ addslashes($address) }}&t=&z=13&ie=UTF8&iwloc=&output=embed"></iframe>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
