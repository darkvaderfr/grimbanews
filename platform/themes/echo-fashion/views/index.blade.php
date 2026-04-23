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
                <div>[blog-posts-with-banner-slider style="style-1" type="featured" limit="5"][/blog-posts-with-banner-slider]</div>
                <div>[featured-posts title="Featured Post" type="featured" limit="3"][/featured-posts]</div>
                <div>[blog-category-tabs title="Most Popular" category_ids="2,3,4,5,6" number_of_post_per_category="3" sidebar="custom_sidebar_1"][/blog-category-tabs]</div>
                <div>[single-post post_id="1" button_label="Read more" background_color="rgb(241, 241, 241)"][/single-post]</div>
                <div>[blog-category style="style-1" title="Featured Post" category_ids="2,4,5,6" title_align="center" limit="4" text_color="rgb(255, 255, 255)" background_color="rgb(24, 24, 35)"][/blog-category]</div>
                <div>[blog-posts-with-banner-slider title="The Archive" style="style-2" type="featured" limit="5"][/blog-posts-with-banner-slider]</div>
                <div>[posts-slider quantity="3" image_1="fashion/sliders/1.png" post_1="1" video_url_1="https://www.youtube.com/watch?v=z37zOqFygbw" image_2="fashion/sliders/2.png" post_2="3" video_url_2="https://www.youtube.com/watch?v=z37zOqFygbw" image_3="fashion/sliders/3.png" post_3="5" video_url_3="https://www.youtube.com/watch?v=z37zOqFygbw" post_4="1" post_5="1" post_6="1"][/posts-slider]</div>
                <div>[gallery title="Follow Instagram" limit="6" text_color="#fff" background_color="#181823"][/gallery]</div>
            </div>
            <br>
            <div>- Template: <strong>Homepage</strong>.</div>
        </div>

        <p><strong>3. Then go to Admin -> Appearance -> Theme options -> Page to set your homepage.</strong></p>
    </div>
</div>
