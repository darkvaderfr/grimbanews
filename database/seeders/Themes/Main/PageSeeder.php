<?php

namespace Database\Seeders\Themes\Main;

use Botble\Base\Facades\Html;
use Botble\Page\Database\Traits\HasPageSeeder;
use Botble\Theme\Database\Seeders\ThemeSeeder;

class PageSeeder extends ThemeSeeder
{
    use HasPageSeeder;

    public function run(): void
    {
        $this->truncatePages();

        $policyContent = Html::tag('h3', 'EU Cookie Consent') .
            Html::tag(
                'p',
                'To use this website we are using Cookies and collecting some Data. To be compliant with the EU GDPR we give you to choose if you allow us to use certain Cookies and to collect some Data.'
            ) .
            Html::tag('h4', 'Essential Data') .
            Html::tag(
                'p',
                'The Essential Data is needed to run the Site you are visiting technically. You can not deactivate them.'
            ) .
            Html::tag(
                'p',
                '- Session Cookie: PHP uses a Cookie to identify user sessions. Without this Cookie the Website is not working.'
            ) .
            Html::tag(
                'p',
                '- XSRF-Token Cookie: Laravel automatically generates a CSRF "token" for each active user session managed by the application. This token is used to verify that the authenticated user is the one actually making the requests to the application.'
            );

        $randomContent = Html::tag('h3', 'Random Content') .
            Html::tag(
                'p',
                'This is a random content that will be displayed on the page.'
            );

        $pages = [
            [
                'name' => 'Homepage',
                'content' =>
                    Html::tag('div', '[blog-posts style="style-1" category_ids="3,6" title="Top Story" title_align="start" text_color="transparent" background_color="transparent" limit="4"][/blog-posts]') .
                    Html::tag('div', '[blog-posts enable_lazy_loading="yes" style="style-2" category_ids="4,6" title="Latest News" limit="4" title_align="start" text_color="transparent" background_color="rgb(249, 249, 249)"][/blog-posts]') .
                    Html::tag('div', '[blog-posts enable_lazy_loading="yes" style="style-3" title="Trending" category_ids="2,4,6,5" limit="6"][/blog-posts]') .
                    Html::tag('div', '[blog-posts enable_lazy_loading="yes" style="style-4" category_ids="1,4,7" title="Video" title_align="center" limit="6"][/blog-posts]') .
                    Html::tag('div', '[blog-categories enable_lazy_loading="yes" style="style-1" category_ids="2,3,4" title="Discover Categories"][/blog-categories]') .
                    Html::tag('div', '[blog-posts enable_lazy_loading="yes" style="style-5" category_ids="2,3,4,5,6" title="Featured Posts" limit="8" title_align="start" text_color="transparent" background_color="rgb(249, 249, 249)" sidebar="custom_sidebar_1"][/blog-posts]') .
                    Html::tag('div', '[blog-posts enable_lazy_loading="yes" style="style-6" category_ids="5,6,7" title="Popular Of The Weeks" limit="3" title_align="center" text_color="transparent" background_color="transparent"][/blog-posts]') .
                    Html::tag('div', '[app-downloads title="Download Our Apps For Stay Update" subtitle="Easy to update latest news, aily podcast and everything in your hand" platform_google_label="Play Store" platform_google_icon="ti ti-brand-google-play" platform_google_url="https://play.google.com/" platform_apple_label="Apple Store" platform_apple_icon="ti ti-brand-apple" platform_apple_url="https://www.apple.com/app-store/" image="' . $this->filePath('general/app-downloads.png', 'main') . '" background_color="#537FE7"][/app-downloads]'),
                'template' => 'homepage',
            ],
            [
                'name' => 'About us',
                'content' => Html::tag(
                    'div',
                    '[about-us title="Know our Publication Media & Vision." subtitle="Bring To The Table Win-Win Survival Strategies To Ensure Proactive Domination. At The End Of The Day Going." description="Leverage agile frameworks to provide a robust synopsis for high level overviews. Iterative approaches to corporate strategy foster collaborative thinking to further the overall value proposition. Organically grow the over holistic world view of disruptive innovation via workplace diversity and Bring to the table win-win survival strategies to ensure proactive fin domination. At the end of the day, going forward, a new normal." staff_name="Raihawly Williamson" staff_description="CEO, Echo Publishing Inc." staff_signature="' . $this->filePath('general/signature.png', 'main') . '" first_image="' . $this->filePath('general/about-1.png', 'main') . '" second_image="' . $this->filePath('general/about-2.png', 'main') . '"][/about-us]'
                ) .
                    Html::tag(
                        'div',
                        '[intro-video image="' . $this->filePath('general/about-video-1.png', 'main') . '" video_url="https://www.youtube.com/watch?v=Y1t6rjWYNro" play_icon="ti ti-player-play-filled"][/intro-video]'
                    ),
                'template' => 'full-width',
            ],
            [
                'name' => 'Contact',
                'content' => Html::tag(
                    'div',
                    '[contact-form title="General Customer Care & Technical Support" description="We’re here to assist you with any questions or technical issues you may have. Please fill out the form below, and our dedicated team will respond promptly to ensure you have a seamless experience with us." button_label="Send Message" button_icon="ti ti-arrow-right" quantity="3" title_1="Location" description_1="The Business Centre132, New York 12401. United States" icon_1="ti ti-map-pin" url_1="" open_in_new_tab_1="yes" title_2="Email Address" description_2="info@yourmail.com" icon_2="ti ti-mail" url_2="mailto:info@yourmail.com" open_in_new_tab_2="yes" title_3="Phone" description_3="(00) 123 456 789 99" icon_3="ti ti-phone" url_3="tel:(00) 123 456 789 99" open_in_new_tab_3="yes"  address="Fortis Downtown Resort"][/contact-form]'
                ),
                'template' => 'full-width',
            ],
            [
                'name' => 'Blog',
                'content' => '---',
                'template' => 'full-width',
            ],
            [
                'name' => 'Team',
                'content' => Html::tag('div', '[members title="Meet Our Professional Team" member_ids="1,2,3,4,5,6,7,8"][/members]'),
                'template' => 'full-width',
            ],
            [
                'name' => 'Cookie Policy',
                'content' => $policyContent,
                'template' => 'default',
            ],
            [
                'name' => 'Terms Of Use',
                'content' => $policyContent,
                'template' => 'default',
            ],
            [
                'name' => 'Privacy Policy',
                'content' => $policyContent,
                'template' => 'default',
            ],
            [
                'name' => 'Advertise',
                'content' => $randomContent,
                'template' => 'default',
            ],
            [
                'name' => 'FAQ',
                'content' => $randomContent,
                'template' => 'default',
            ],
        ];

        $this->createPages($pages);
    }
}
