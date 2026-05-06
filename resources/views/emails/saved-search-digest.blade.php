@php
    $firstName = trim((string) ($member->first_name ?? ''));
@endphp

<div style="font-family: Arial, sans-serif; color:#1a1713; background:#f6f1e8; padding:28px;">
    <div style="max-width:640px; margin:0 auto; background:#fffaf1; border:1px solid #ded4c2; border-radius:8px; padding:28px;">
        <p style="font-size:13px; letter-spacing:.08em; text-transform:uppercase; color:#8d3025; margin:0 0 10px;">
            GrimbaNews
        </p>
        <h1 style="font-family: Georgia, serif; font-size:28px; line-height:1.15; margin:0 0 12px; color:#1a1713;">
            Vos alertes recherche
        </h1>
        <p style="font-size:15px; line-height:1.6; color:#4a4540; margin:0 0 22px;">
            @if($firstName !== '')
                Bonjour {{ $firstName }},
            @else
                Bonjour,
            @endif
            voici les nouveaux articles qui correspondent aux recherches que vous suivez.
        </p>

        @foreach($digests as $digest)
            <div style="border-top:1px solid #e8dfcf; padding:18px 0 6px;">
                <p style="font-size:12px; letter-spacing:.08em; text-transform:uppercase; color:#8d3025; margin:0 0 6px;">
                    Recherche suivie
                </p>
                <h2 style="font-family: Georgia, serif; font-size:20px; line-height:1.25; margin:0 0 10px;">
                    <a href="{{ $digest['url'] }}" style="color:#1a1713; text-decoration:none;">
                        {{ $digest['label'] }}
                    </a>
                </h2>

                @foreach($digest['posts'] as $post)
                    <div style="border-top:1px solid #efe7da; padding:14px 0;">
                        <p style="font-size:12px; color:#6b6459; margin:0 0 6px;">
                            {{ $post->source_name ?: 'GrimbaNews' }} · {{ $post->created_at?->format('d/m/Y') }}
                        </p>
                        <h3 style="font-family: Georgia, serif; font-size:18px; line-height:1.25; margin:0 0 8px;">
                            <a href="{{ $post->url }}" style="color:#1a1713; text-decoration:none;">
                                {{ $post->name }}
                            </a>
                        </h3>
                        @if($post->description)
                            <p style="font-size:14px; line-height:1.55; color:#4a4540; margin:0;">
                                {{ \Illuminate\Support\Str::limit(strip_tags((string) $post->description), 170) }}
                            </p>
                        @endif
                    </div>
                @endforeach
            </div>
        @endforeach

        <p style="font-size:13px; line-height:1.5; color:#6b6459; margin:22px 0 0;">
            Vous pouvez retirer une alerte depuis Mon compte.
        </p>
    </div>
</div>
