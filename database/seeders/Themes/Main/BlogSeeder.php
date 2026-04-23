<?php

namespace Database\Seeders\Themes\Main;

use Botble\Base\Facades\Html;
use Botble\Blog\Database\Traits\HasBlogSeeder;
use Botble\Member\Models\Member;
use Botble\Theme\Database\Seeders\ThemeSeeder;
use Illuminate\Support\Arr;

class BlogSeeder extends ThemeSeeder
{
    use HasBlogSeeder;

    public function run(): void
    {
        $this->uploadFiles('news', 'main');
        $this->uploadFiles('audio', 'main');

        $this->createPosts();
    }

    public function createPosts(): void
    {
        $this->createBlogCategories($this->getBlogCategoryData());

        $this->createBlogTags($this->getBlogTagData());

        $postContentStyle1 = Html::tag(
            'p',
            'Our mission is to assist you in achieving better financial shape, not just for today, but for the long term. We believe that financial security and freedom are within reach for everyone, and we’re dedicated to providing the guidance and support you need to get there.'
        ) .
            Html::tag(
                'div',
                '[content-quote author="Celine Dion" quote="‘‘Life Imposes Things On You That You Can’t Control, But You Still Have The Choice Of How You’re Going To Live Through This.’’"][/content-quote]'
            ) .
            Html::tag(
                'div',
                '[content-image number_of_columns="col-2" quantity="2" image_1="' . $this->filePath('news/6.jpg', 'main') . '" image_2="' . $this->filePath('news/2.jpg', 'main') . '"][/content-image]'
            ) .
            Html::tag(
                'h3',
                'Better financial shape'
            ) .
            Html::tag(
                'p',
                'Our comprehensive range of financial services and expert guidance are designed to empower individuals, families, and businesses to make informed financial decisions. Whether you’re looking to manage debt, build savings, invest wisely, or plan for retirement, our team of seasoned financial professionals is committed to helping you reach your goals. We offer personalized financial assessments, tailored strategies, and cutting-edge tools to maximize your financial well-being. By partnering with us, you gain access to a wealth of knowledge and resources that can transform your financial future.'
            ) .
            Html::tag(
                'div',
                '[content-listing-style style="style-1" quantity="6" item_1="Diverse Solutions Guidance." item_2="Professionals Assisting You." item_3="Tailored Strategies for Success." item_4="Informed Decisions with Technology." item_5="Health Assessments Provided." item_6="Strategies for Building Prosperity."][/content-listing-style]'
            ) .
            Html::tag(
                'p',
                'Achieving financial stability and prosperity is a shared goal we all aspire to. At Echo, we understand the significance of being in better financial shape, and we are here to guide you on your journey.'
            ) .
            Html::tag(
                'br',
                ' '
            ) .
            Html::tag(
                'br',
                ' '
            );

        $postContentStyle2 =
            Html::tag(
                'p',
                'The title conveys a sense of confidence that the economy will weather the banking crisis without suffering severe damage. The term "banking crisis" likely refers to a situation where financial institutions face significant challenges, such as insolvency, liquidity problems, or other issues that could affect the stability of the financial sector.'
            ) .
            Html::tag(
                'div',
                '[content-quote style="style-2" quote="Life Imposes Things On You That You Can’t Control, But You Still Have The Choice Of How You’re Going To Live Through This" author="Celine Dion" description="Acknowledgment of Life’s Challenges: The quote recognizes that life can be unpredictable and present challenges, obstacles, and hardships that are beyond an individual’s control. It emphasizes that adversity is a natural part of the human experience." top_left_image="' . $this->filePath('general/blog-shape-image.png', 'main') . '"][/content-quote]'
            ) .
            Html::tag(
                'h3',
                'I love how relaxed and flowy this dress is and that it has really delicate'
            ) .
            Html::tag(
                'p',
                'Indulge in the epitome of comfort and style with our "Relaxed and Flowy" dress, a masterpiece of delicate design and effortless elegance. This dress offers the perfect blend of comfort and sophistication, allowing you to move gracefully through any occasion. Crafted with meticulous attention to detail, its delicate fabric and relaxed silhouette make it a wardrobe essential for those who appreciate both style and comfort. Whether you’re strolling through a garden party or enjoying a serene evening out, this dress will be your trusted companion. Discover the beauty of ease and elegance with this exquisite garment.'
            ) .
            Html::tag(
                'br',
                ' '
            ) .
            Html::tag(
                'div',
                '[content-image number_of_columns="col-2" quantity="2" image_1="' . $this->filePath('news/3.jpg', 'main') . '" image_2="' . $this->filePath('news/7.jpg', 'main') . '"][/content-image]'
            ) .
            Html::tag(
                'div',
                '[content-capitalize text="Step into the world of tranquility and charm with our Relaxed and Flowy dress. Its effortless design is a testament to the beauty of simplicity. The gentle, delicate fabric cascades with every movement, creating a mesmerizing dance of fabric and form. This dress embraces you in a feeling of relaxation and freedom, making it perfect for those serene, carefree moments you cherish"][/content-capitalize]'
            ) .
            Html::tag(
                'div',
                '[content-capitalize blog_content="This dress has a captivating allure with its relaxed and flowy silhouette that exudes a sense of effortlessness. What truly sets it apart are the exquisite, delicate details that adorn the fabric. The dress strikes a harmonious balance between comfort and sophistication, making it a perfect choice for those who value both the ease of relaxed attire and the subtle, intricate beauty that these delicate features bring to the overall design."][/content-capitalize]'
            ) .
            Html::tag(
                'p',
                'It encourages taking personal responsibility for one’s responses to life’s trials. Even when faced with difficult circumstances, individuals have the autonomy to choose their emotional and behavioral reactions.'
            ) .
            Html::tag(
                'br',
                ' '
            ) .
            Html::tag(
                'p',
                'The subtle details and the relaxed fit come together to create an ensemble that effortlessly combines fashion and comfort. Whether you’re attending a summer soirée or savoring a quiet day by the beach, this dress will be your go-to choice for a look that is both relaxed and elegantly delicate. Elevate your style with this captivating dress that celebrates the beauty of flowy, effortless fashion."'
            ) .
            Html::tag(
                'br',
                ' '
            ) .
            Html::tag(
                'br',
                ' '
            );

        $postContentStyle3 =
            Html::tag(
                'p',
                'Introducing the Asus ROG Ally, your ultimate gaming companion that won’t compromise your budget. Packed with the high-performance AMD Z1 processor, this gaming laptop redefines what’s possible in the sub-$600 price range. Whether you’re a casual gamer or a hardcore enthusiast, the Asus ROG Ally has you covered with its impressive hardware and affordability.'
            ) .
            Html::tag(
                'h3',
                'Data Center Loading & Security'
            ) .
            Html::tag(
                'p',
                'The Asus ROG Ally: Unleash Gaming Power on a Budget! Starting at just $600, this gaming marvel is powered by the formidable AMD Z1 processor. Get ready for an immersive gaming experience that won’t break the bank, as the Asus ROG Ally combines affordability with cutting-edge technology to ensure you conquer virtual worlds like a true champion.'
            ) .
            Html::tag(
                'h3',
                'Advance Features'
            ) .
            Html::tag(
                'div',
                '[content-listing-style number_of_columns="col-2" quantity="6" item_1="Affordable Gaming Power" item_2="AMD Z1 Processor" item_3="Stylish Design" item_4="High-Refresh-Rate Display" item_5="Expandable Storage and Memory" item_6="Gaming Excellence on a Budget"][/content-listing-style]'
            ) .
            Html::tag(
                'p',
                'The Asus ROG Ally also offers ample storage and memory options to keep all your games and files within easy reach. You can expand and customize it to meet your specific needs, ensuring you’re always prepared for the latest gaming challenges.'
            ) .
            Html::tag(
                'br',
                ' '
            ) .
            Html::tag(
                'div',
                '[content-image number_of_columns="col-2" quantity="2" image_1="' . $this->filePath('general/blog-4.png', 'main') . '" image_2="' . $this->filePath('general/blog-3.png', 'main') . '"][/content-image]'
            ) .
            Html::tag(
                'div',
                '[content-image number_of_columns="col-1" quantity="1" image_1="' . $this->filePath('general/blog-5.png', 'main') . '"][/content-image]'
            ) .
            Html::tag(
                'h3',
                'Features & configurations'
            ) .
            Html::tag(
                'div',
                '[content-listing-style number_of_columns="col-1" quantity="6" item_1="350-mile range (the bar is 300 miles)" item_2="Your smartphone can be your key to the vehicle" item_3="Reverse charging, so your car can power your house during a power outage" item_4="An impressive 0-60 time of around 3.6 seconds (I like performance)" item_5="A solar panel roof to increase range and supply emergency power" item_6="Convertible-like mode (which really opens the car up)"][/content-listing-style]'
            ) .
            Html::tag(
                'p',
                'Asus ROG Ally is your ticket to affordable gaming excellence. With a starting price of just $600, it’s a game-changer that will take your gaming adventures to the next level. Whether you’re a student, a professional, or just someone looking for a fantastic gaming experience without breaking the bank, the Asus ROG Ally with AMD Z1 is your ideal companion. Grab yours now and prepare to dominate the gaming world!'
            ) .
            Html::tag(
                'br',
                ' '
            );

        $postContents = [$postContentStyle1, $postContentStyle2, $postContentStyle3];

        $members = Member::query()->pluck('id');

        $faker = $this->fake();
        $data = $this->getData();

        $audioPath = $this->filePath('audio/audio.mp3', 'main');

        foreach ($data as $index => $item) {
            $data[$index] = [
                ...Arr::except($item, ['metadata']),
                'content' => in_array($index, [0, 1, 2]) ? $postContents[$index] : $postContents[rand(0, 2)],
                'author_type' => Member::class,
                'author_id' => $faker->randomElement($members),
                'views' => $faker->numberBetween(100, 2500),
                'is_featured' => $index < 10,
                'image' => $this->getFilePathImageForm($index),
            ];

            if ($index % 3) {
                $data[$index]['metadata'] = [
                    'video_url' => 'https://www.youtube.com/watch?v=yCh9OVLI0SU',
                ];
            } elseif ($index % 4) {
                $data[$index]['metadata'] = [
                    'audio' => $audioPath,
                ];
            }
        }

        $this->createBlogPosts($data);
    }

    protected function getData(): array
    {
        $data = [
            [
                'name' => 'Scientists Make Breakthrough in Cancer Research',
                'description' => 'Researchers have discovered a promising new treatment for certain types of cancer, offering hope to patients worldwide.',
            ],
            [
                'name' => 'Global Economy Shows Signs of Recovery',
                'description' => 'Positive economic indicators suggest a gradual recovery from the impact of the recent global downturn, with increased job creation and consumer confidence.',
            ],
            [
                'name' => 'Tech Giants Unveil Cutting-Edge Gadgets at Annual Expo',
                'description' => 'The latest innovations in technology were showcased at the annual tech expo, featuring groundbreaking gadgets and advancements in artificial intelligence.',
            ],
            [
                'name' => 'Climate Change Summit Addresses Urgent Environmental Concerns',
                'description' => 'World leaders gather to discuss and strategize solutions for combating climate change, emphasizing the need for global collaboration to protect the planet.',
            ],
            [
                'name' => 'Celebrity Couple Announces Engagement in Romantic Getaway',
                'description' => 'A well-known celebrity couple has officially announced their engagement, sharing intimate details of the proposal during a romantic getaway.',
            ],
            [
                'name' => 'Space Exploration Mission Discovers New Exoplanets',
                'description' => 'A recent space mission has identified several new exoplanets with potential habitability, opening up possibilities for the search for extraterrestrial life.',
            ],
            [
                'name' => 'Major Sporting Event Breaks Records in Viewership',
                'description' => 'The latest edition of a major sporting event shattered previous viewership records, capturing the attention of fans globally with thrilling competitions.',
            ],
            [
                'name' => 'Health Experts Share Tips for Boosting Immune System',
                'description' => 'Amid health concerns, experts provide valuable insights and tips for maintaining a strong immune system to better protect against illnesses.',
            ],
            [
                'name' => 'Artificial Intelligence in Education: Transforming Learning Experiences',
                'description' => 'Educational institutions worldwide are incorporating artificial intelligence to enhance learning experiences, paving the way for personalized and adaptive education.',
            ],
            [
                'name' => 'Newly Discovered Species Adds to Biodiversity Riches',
                'description' => 'Biologists have identified a previously unknown species, highlighting the importance of conservation efforts in preserving Earth\'s diverse ecosystems.',
            ],
            [
                'name' => 'Fashion Trends for the Upcoming Season Unveiled',
                'description' => 'Fashion designers showcase their latest creations, offering a glimpse into the trends that will dominate the upcoming season and influencing style enthusiasts.',
            ],
            [
                'name' => 'Government Initiatives Aim to Address Affordable Housing Crisis',
                'description' => 'In response to the ongoing housing affordability crisis, governments implement new initiatives to provide affordable housing options for citizens in need.',
            ],
            [
                'name' => 'Breakthrough Treatment Shows Promise for Alzheimer\'s Patients',
                'description' => 'A new medical breakthrough in Alzheimer\'s research brings hope to patients and their families as scientists make progress toward effective treatments.',
            ],
            [
                'name' => 'Social Media Platform Introduces Enhanced Privacy Features',
                'description' => 'In response to user concerns about privacy, a popular social media platform introduces new features to give users more control over their personal information.',
            ],
            [
                'name' => 'Local Community Comes Together for Environmental Cleanup',
                'description' => 'Residents join forces for a community-wide environmental cleanup initiative, demonstrating the power of collective action in preserving local ecosystems.',
            ],
            [
                'name' => 'Innovative Startup Revolutionizes Sustainable Packaging',
                'description' => 'A startup company introduces groundbreaking sustainable packaging solutions, aiming to reduce environmental impact and promote eco-friendly practices in the industry.',
            ],
            [
                'name' => 'New Educational Program Empowers Youth in Tech Skills',
                'description' => 'A youth-focused educational program is launched to empower the next generation with essential technology skills, preparing them for future opportunities.',
            ],
            [
                'name' => 'Historical Landmark Restored to Its Former Glory',
                'description' => 'A significant historical landmark undergoes extensive restoration efforts, preserving its cultural heritage and allowing future generations to appreciate its historical significance.',
            ],
            [
                'name' => 'Renowned Author Releases Highly Anticipated Novel',
                'description' => 'A celebrated author unveils their latest literary masterpiece, generating excitement among readers eager to delve into the pages of this highly anticipated novel.',
            ],
            [
                'name' => 'Global Collaboration Leads to Breakthrough in Disease Prevention',
                'description' => 'International efforts and collaboration result in a major breakthrough in disease prevention, marking a significant step forward in global healthcare.',
            ],
        ];

        return $data;
    }

    protected function getBlogCategoryData(): array
    {
        return [
            [
                'name' => 'Uncategorized',
            ],
            [
                'name' => 'Videos',
            ],
            [
                'name' => 'Business',
            ],
            [
                'name' => 'Entertainment',
            ],
            [
                'name' => 'Travel',
            ],
            [
                'name' => 'Podcasts',
            ],
            [
                'name' => 'Healthy',
            ],
            [
                'name' => 'Sport',
            ],
        ];
    }

    protected function getBlogTagData(): array
    {
        return [
            [
                'name' => 'General',
            ],
            [
                'name' => 'Science',
            ],
            [
                'name' => 'Health',
            ],
            [
                'name' => 'Entertainment',
            ],
            [
                'name' => 'Travel',
            ],
            [
                'name' => 'World News',
            ],
            [
                'name' => 'Environment',
            ],
            [
                'name' => 'Sports',
            ],
        ];
    }

    protected function getFilePathImageForm(int $index): string
    {
        return $this->filePath('news/' . ($index + 1) . '.jpg', 'main');
    }
}
