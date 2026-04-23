@if (is_plugin_active('language'))
    @php
        $supportedLocales = Language::getSupportedLocales();

        if (empty($options)) {
            $options = [
                'before' => '',
                'lang_flag' => true,
                'lang_name' => true,
                'class' => '',
                'after' => '',
            ];
        }

        $location = $location ?? 'header';
    @endphp

    @if ($supportedLocales && count($supportedLocales) > 1)
        @php
            $languageDisplay = setting('language_display', 'all');
            $showRelated = setting('language_show_default_item_if_current_version_not_existed', true);
        @endphp

        @switch($location)
            @case('header')
                <div class="col lang-switcher d-flex flex-row justify-content-end">
                    <div class="d-lg-block d-none dropdown language-switcher d-inline-flex align-items-center me-3">
                        <a class="dropdown-toggle" type="button" id="language-switcher-dropdown" data-bs-toggle="dropdown" data-toggle="dropdown">
                            @if (Arr::get($options, 'lang_flag', true) && ($languageDisplay == 'all' || $languageDisplay == 'flag'))
                                {!! language_flag(Language::getCurrentLocaleFlag()) !!}
                            @endif

                            @if (Arr::get($options, 'lang_name', true) && ($languageDisplay == 'all' || $languageDisplay == 'name'))
                                &nbsp;<span>{{ Language::getCurrentLocaleName() }}</span>
                            @endif
                        </a>
                        <div aria-labelledby="language-switcher-dropdown"
                             class="dropdown-menu language-switcher-list language-switcher-list-end"
                        >
                            @foreach ($supportedLocales as $localeCode => $properties)
                                @if ($localeCode != Language::getCurrentLocale())
                                    <li>
                                        <a class="language-item" href="{{ $showRelated ? Language::getLocalizedURL($localeCode) : url($localeCode) }}" target="_self">
                                            @if (Arr::get($options, 'lang_flag', true) && ($languageDisplay == 'all' || $languageDisplay == 'flag'))
                                                {!! language_flag($properties['lang_flag']) !!} <span>{{ $properties['lang_name'] }}</span>
                                            @endif
                                            @if (Arr::get($options, 'lang_name', true) &&  ($languageDisplay == 'name'))
                                                &nbsp;{{ $properties['lang_name'] }}
                                            @endif
                                        </a>
                                    </li>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            @break

            @case('sidebar')
                <nav class="lang-switcher">
                    <p class="main">
                        {{ __('Language') }}
                    </p>
                    <ul class="mt-2">
                        @foreach ($supportedLocales as $localeCode => $properties)
                            @if ($localeCode != Language::getCurrentLocale())
                                <li class="menu-item mt-2">
                                    <a class="mobile-menu-lang-switcher" href="{{ $showRelated ? Language::getLocalizedURL($localeCode) : url($localeCode) }}" target="_self">
                                        @if (Arr::get($options, 'lang_flag', true) && ($languageDisplay == 'all' || $languageDisplay == 'flag'))
                                            {!! language_flag($properties['lang_flag']) !!} <span class="ms-3">{{ $properties['lang_name'] }}</span>
                                        @endif
                                        @if (Arr::get($options, 'lang_name', true) &&  ($languageDisplay == 'name'))
                                            &nbsp;{{ $properties['lang_name'] }}
                                        @endif
                                    </a>
                                </li>
                            @endif
                        @endforeach
                    </ul>
                </nav>
            @break

            @case('footer')
                <div class="select-area">
                    <label for="lang-switcher-footer" class="sr-only">{{ __('Language') }}</label>
                    <select name="lang" id="lang-switcher-footer">
                        @foreach ($supportedLocales as $localeCode => $properties)
                            <option @selected($localeCode == Language::getCurrentLocale()) value="{{ $showRelated ? Language::getLocalizedURL($localeCode) : url($localeCode) }}">
                                @if (Arr::get($options, 'lang_flag', true) && ($languageDisplay == 'all' || $languageDisplay == 'flag'))
                                    {{ $properties['lang_name'] }}
                                @endif
                                @if (Arr::get($options, 'lang_name', true) &&  ($languageDisplay == 'name'))
                                    &nbsp;{{ $properties['lang_name'] }}
                                @endif
                            </option>
                        @endforeach
                    </select>
                </div>
            @break
        @endswitch
    @endif
@endif
