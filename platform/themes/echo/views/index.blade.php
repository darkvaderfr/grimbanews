@php
    Theme::layout('homepage');
@endphp

<div class="container">
    <div style="margin: 40px 0;">
        <h4 style="color: #f00; margin-bottom: 15px;">You need to setup your homepage!</h4>

        <p><strong>1. Go to Admin -> Plugins then activate all plugins.</strong></p>
        <p><strong>2. Go to Admin -> Pages and create a page:</strong></p>

        <div style="margin: 20px 0;">
            <div>- Content:</div>
            <div style="border: 1px solid rgba(0,0,0,.1); padding: 10px; margin-top: 10px;direction: ltr;">
                <div>[blog-posts style="style-1" category_ids="3,6" title="Top Story" title_align="start" text_color="transparent" background_color="transparent" limit="4"][/blog-posts]</div>
                <div>[blog-posts style="style-2" category_ids="4,6" title="Latest News" limit="4" title_align="start" text_color="transparent" background_color="rgb(249, 249, 249)"][/blog-posts]</div>
                <div>[blog-posts style="style-3" title="Trending" category_ids="2,4,6,5" limit="6"][/blog-posts]</div>
                <div>[blog-posts style="style-4" category_ids="1,4,7" title="Video" title_align="center" limit="6"][/blog-posts]</div>
                <div>[blog-categories style="style-1" category_ids="2,3,4" title="Discover Categories"][/blog-categories]</div>
                <div>[blog-posts style="style-5" category_ids="2,3,4,5,6" title="Featured Posts" limit="8" title_align="start" text_color="transparent" background_color="rgb(249, 249, 249)" sidebar="custom_sidebar_1"][/blog-posts]</div>
                <div>[blog-posts style="style-6" category_ids="5,6,7" title="Popular Of The Weeks" limit="3" title_align="start" text_color="transparent" background_color="transparent"][/blog-posts]</div>
                <div>[app-downloads title="Download Our Apps For Stay Update" subtitle="Easy to update latest news, aily podcast and everything in your hand" platform_google_label="Play Store" platform_google_icon="ti ti-brand-google-play" platform_google_url="https://play.google.com/" platform_apple_label="Apple Store" platform_apple_icon="ti ti-brand-apple" platform_apple_url="https://www.apple.com/app-store/" image="main/general/app-downloads.png" background_color="#537FE7"][/app-downloads]</div>
            </div>
            <br>
            <div>- Template: <strong>Homepage</strong>.</div>
        </div>

        <p><strong>3. Then go to Admin -> Appearance -> Theme options -> Page to set your homepage.</strong></p>
    </div>
</div>
