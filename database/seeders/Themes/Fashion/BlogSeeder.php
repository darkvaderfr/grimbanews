<?php

namespace Database\Seeders\Themes\Fashion;

use Database\Seeders\Themes\Main\BlogSeeder as BaseBlogSeeder;

class BlogSeeder extends BaseBlogSeeder
{
    public function run(): void
    {
        $this->uploadFiles('news');

        parent::run();
    }

    public function getData(): array
    {
        return [
            [
                'name' => 'Top Designers Showcase Avant-Garde Collection at Fashion Week',
                'description' => 'Leading fashion designers present their cutting-edge and avant-garde collections, setting the stage for upcoming fashion trends and inspiring style enthusiasts.',
            ],
            [
                'name' => 'Inclusive Fashion Show Celebrates Diversity on the Runway',
                'description' => 'A groundbreaking fashion show promotes inclusivity and diversity, featuring models of all shapes, sizes, and backgrounds, challenging industry standards.',
            ],
            [
                'name' => 'Sustainable Fashion Brands Gain Momentum in the Industry',
                'description' => 'The fashion industry witnesses a surge in sustainable practices as eco-friendly and ethically sourced fashion brands gain popularity among environmentally conscious consumers.',
            ],
            [
                'name' => 'Iconic Fashion House Launches Limited Edition Capsule Collection',
                'description' => 'A renowned fashion house unveils a limited edition capsule collection, combining timeless elegance with contemporary style, creating a buzz among fashion aficionados.',
            ],
            [
                'name' => 'Fashion Tech: Wearable Technology Redefines Style',
                'description' => 'The intersection of fashion and technology takes center stage as innovative wearable tech products and smart fabrics redefine the way individuals express their personal style.',
            ],
            [
                'name' => 'Fashion Influencers Collaborate with High-End Brands for Exclusive Line',
                'description' => 'Social media fashion influencers team up with prestigious brands to create an exclusive and trendsetting clothing line, reflecting the influence of digital fashion culture.',
            ],
            [
                'name' => 'Retro Revival: Vintage Fashion Makes a Comeback',
                'description' => 'Nostalgia takes over the fashion scene as vintage styles and retro aesthetics make a comeback, influencing contemporary fashion trends and inspiring a sense of nostalgia.',
            ],
            [
                'name' => 'Fashion Capitals Host Star-Studded Gala Celebrating Couture',
                'description' => 'The world\'s fashion capitals host a glamorous gala celebrating haute couture, attracting celebrities, designers, and fashion enthusiasts from around the globe.',
            ],
            [
                'name' => 'Luxury Fashion Brand Unveils Exquisite Diamond-Studded Collection',
                'description' => 'A renowned luxury fashion brand introduces an opulent collection featuring exquisite diamond-studded garments, merging high fashion with unparalleled craftsmanship.',
            ],
            [
                'name' => 'Streetwear Dominates Fashion Scene with Urban Cool Vibes',
                'description' => 'Streetwear continues to influence mainstream fashion with its urban cool vibes, blending comfort and style in a trend that resonates with a diverse and youthful audience.',
            ],
            [
                'name' => 'Fashion Forward: Gender-Fluid Designs Challenge Traditional Norms',
                'description' => 'Designers push the boundaries of gender norms with gender-fluid fashion designs that challenge traditional stereotypes, promoting inclusivity and self-expression.',
            ],
            [
                'name' => 'Fashion Week Features Sustainable and Vegan-Friendly Collections',
                'description' => 'Fashion Week showcases a wave of sustainable and vegan-friendly collections, reflecting the industry\'s commitment to ethical practices and environmental responsibility.',
            ],
            [
                'name' => 'Emerging Designers Spotlighted in Fashion Industry Awards',
                'description' => 'Talented emerging designers receive recognition and accolades in prestigious fashion industry awards, propelling them into the spotlight and shaping the future of fashion.',
            ],
            [
                'name' => 'Cultural Fusion: Fashion Trends Inspired by Global Heritage',
                'description' => 'Fashion designers draw inspiration from diverse global cultures, creating a fusion of styles that celebrate the richness of heritage and promote cross-cultural appreciation.',
            ],
            [
                'name' => 'Fashion Icons Collaborate on Limited Edition Streetwear Collection',
                'description' => 'Legendary fashion icons join forces to create a limited edition streetwear collection, blending iconic styles with contemporary urban fashion for a unique and collectible line.',
            ],
            [
                'name' => 'Fashion Psychology: Exploring the Emotional Impact of Clothing Choices',
                'description' => 'Experts delve into the fascinating world of fashion psychology, exploring how clothing choices can influence emotions, self-perception, and social interactions.',
            ],
            [
                'name' => 'Fashion Tech: Augmented Reality Transforms Online Shopping Experience',
                'description' => 'Augmented reality technology revolutionizes the online shopping experience as fashion brands integrate AR features, allowing customers to virtually try on clothing items before purchase.',
            ],
            [
                'name' => 'Fashion Capitals Collaborate on Cross-Cultural Runway Show',
                'description' => 'Leading fashion capitals collaborate on a cross-cultural runway show, featuring designers from different regions to showcase the global influence and diversity of fashion.',
            ],
            [
                'name' => 'Fashion Sustainability Summit Addresses Industry Environmental Impact',
                'description' => 'Industry leaders, environmentalists, and fashion experts come together at a sustainability summit to discuss and address the environmental impact of the fashion industry.',
            ],
            [
                'name' => 'Fashion Forward: Futuristic Designs Redefine Runway Trends',
                'description' => 'Designers push the boundaries of fashion with futuristic and avant-garde designs that challenge traditional aesthetics, creating a visually stunning and innovative runway experience.',
            ],
        ];
    }

    protected function getFilePathImageForm(int $index): string
    {
        return $this->filePath('news/' . ($index + 1) . '.jpg', 'fashion');
    }
}
