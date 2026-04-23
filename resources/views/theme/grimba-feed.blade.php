<?php echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n"; ?>
<rss version="2.0"
     xmlns:atom="http://www.w3.org/2005/Atom"
     xmlns:dc="http://purl.org/dc/elements/1.1/"
     xmlns:content="http://purl.org/rss/1.0/modules/content/"
     xmlns:grimba="https://grimbanews.com/xmlns/1.0/">
<channel>
    <title>{{ $siteTitle }}</title>
    <link>{{ $siteUrl }}</link>
    <atom:link href="{{ $feedUrl }}" rel="self" type="application/rss+xml" />
    <description>{{ $siteDesc }}</description>
    <language>fr</language>
    <lastBuildDate>{{ $builtAt }}</lastBuildDate>
    <generator>GrimbaNews</generator>

    @foreach($posts as $post)
        @php
            $url   = $post->url;
            $pub   = ($post->created_at ?? now())->toRssString();
            $desc  = strip_tags((string) ($post->description ?? ''));
            $bias  = $post->bias_rating ?? 'unknown';
        @endphp
        <item>
            <title><![CDATA[ {{ $post->name }} ]]></title>
            <link>{{ $url }}</link>
            <guid isPermaLink="true">{{ $url }}</guid>
            <pubDate>{{ $pub }}</pubDate>
            <description><![CDATA[ {{ $desc }} ]]></description>
            @if($post->source_name)
                <dc:creator><![CDATA[ {{ $post->source_name }} ]]></dc:creator>
            @endif
            <grimba:bias>{{ $bias }}</grimba:bias>
            @if($post->credibility_score)
                <grimba:credibility>{{ (int) $post->credibility_score }}</grimba:credibility>
            @endif
            @if($post->is_blindspot)
                <grimba:blindspot>true</grimba:blindspot>
            @endif
            @if($post->story_cluster_id)
                <grimba:cluster>{{ (int) $post->story_cluster_id }}</grimba:cluster>
            @endif
        </item>
    @endforeach
</channel>
</rss>
