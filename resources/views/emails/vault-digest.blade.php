@php
    $firstName = trim((string) ($member->first_name ?? ''));
@endphp

<div style="font-family: Arial, sans-serif; color:#1a1713; background:#f6f1e8; padding:28px;">
    <div style="max-width:640px; margin:0 auto; background:#fffaf1; border:1px solid #ded4c2; border-radius:8px; padding:28px;">
        <p style="font-size:13px; letter-spacing:.08em; text-transform:uppercase; color:#8d3025; margin:0 0 10px;">
            GrimbaNews
        </p>
        {{-- Wave ZZZZZZZZZZ (Vader 2026-05-23) — wrap visible
            strings in __() so when members.preferred_locale lands
            (separate migration ASK), locale-aware mail works
            without view edits. Today's default locale (FR)
            preserves current rendering. --}}
        <h1 style="font-family: Georgia, serif; font-size:28px; line-height:1.15; margin:0 0 12px; color:#1a1713;">
            {{ __('Votre digest coffre') }}
        </h1>
        <p style="font-size:15px; line-height:1.6; color:#4a4540; margin:0 0 22px;">
            @if($firstName !== '')
                {{ __('Bonjour :name,', ['name' => $firstName]) }}
            @else
                {{ __('Bonjour,') }}
            @endif
            {{ __('voici les articles sauvegardés dans votre coffre GrimbaNews.') }}
        </p>

        @foreach($posts as $post)
            <div style="border-top:1px solid #e8dfcf; padding:16px 0;">
                <p style="font-size:12px; color:#6b6459; margin:0 0 6px;">
                    {{ $post->source_name ?: 'GrimbaNews' }} · {{ $post->created_at?->format('d/m/Y') }}
                </p>
                <h2 style="font-family: Georgia, serif; font-size:20px; line-height:1.25; margin:0 0 8px;">
                    <a href="{{ $post->url }}" style="color:#1a1713; text-decoration:none;">
                        {{ $post->name }}
                    </a>
                </h2>
                @if($post->description)
                    <p style="font-size:14px; line-height:1.55; color:#4a4540; margin:0;">
                        {{ \Illuminate\Support\Str::limit(strip_tags((string) $post->description), 180) }}
                    </p>
                @endif
            </div>
        @endforeach

        <p style="font-size:13px; line-height:1.5; color:#6b6459; margin:22px 0 0;">
            {{ __('Vous pouvez désactiver ce digest depuis Mon compte.') }}
        </p>
    </div>
</div>
