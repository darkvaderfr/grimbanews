@if (is_plugin_active('member'))
    @php
        $auth = auth('member');
    @endphp

    <div class="sign-in-area">
        @if (! $auth->check())
            <a href="{{ route('public.member.login') }}" class="sign-in-btn">
                <i class="fa-regular fa-user"></i>
                {{ __('Login') }}
            </a>
        @else
            @php $user = $auth->user(); @endphp
            <a href="{{ route('public.member.dashboard') }}" class="sign-in-btn">
                <div class="wrapper-avatar">
                    <img
                        class="avatar"
                        src="{{ $user->avatar_url }}"
                        alt="{{ $name = $user->name }}"
                    >
                </div>
                <span class="user-name d-none d-sm-inline text-truncate">{{ $name }}</span>
            </a>
        @endif
    </div>
@endif
