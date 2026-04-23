<?php

namespace Database\Seeders\Themes\Gaming;

use Database\Seeders\Themes\Main\BlogSeeder as BaseBlogSeeder;

class BlogSeeder extends BaseBlogSeeder
{
    public function getData(): array
    {
        $this->uploadFiles('news');

        return [
            [
                'name' => 'Next-Gen Marvel: Exciting Game Announcements and Trailers Unveiled!',
                'description' => 'Dive into the future of gaming with thrilling Marvel titles and watch jaw-dropping trailers that promise an immersive gaming experience.',
            ],
            [
                'name' => 'Epic Showdowns: Top eSports Moments That Left Us Breathless',
                'description' => 'Relive the intensity and excitement of recent eSports events as we highlight the most epic showdowns that had gamers and fans on the edge of their seats.',
            ],
            [
                'name' => 'Cyberpunk 2077 Update: Patch 1.5 Brings Major Enhancements',
                'description' => 'Explore the latest Cyberpunk 2077 update, featuring significant improvements, new features, and enhanced gameplay that breathe fresh life into the futuristic open-world adventure.',
            ],
            [
                'name' => "Gaming Trends 2024: What's Hot in the World of Interactive Entertainment",
                'description' => 'Stay ahead of the curve as we explore the latest gaming trends, from innovative technologies to emerging genres, shaping the landscape of interactive entertainment in 2024.',
            ],
            [
                'name' => 'Rumors Confirmed: Highly Anticipated Sequel Finally in Development!',
                'description' => 'Get ready for the gaming sequel fans have been clamoring for as we confirm the development of a highly anticipated follow-up, complete with tantalizing details about the upcoming adventure.',
            ],
            [
                'name' => 'Virtual Reality Revolution: Unveiling the Next Level of Gaming Immersion',
                'description' => 'Immerse yourself in the virtual realm with the latest advancements in VR technology, offering an unparalleled level of gaming immersion that blurs the lines between reality and fantasy.',
            ],
            [
                'name' => 'Game Changer: How AI is Reshaping the Gaming Industry',
                'description' => 'Explore the impact of Artificial Intelligence on gaming, from smart NPCs and dynamic storytelling to AI-driven game design, revolutionizing the way we play and experience video games.',
            ],
            [
                'name' => "Nintendo's Surprise: Unveiling Exclusive Titles for the Switch Pro",
                'description' => 'Discover the exciting exclusive titles coming to the rumored Nintendo Switch Pro, promising enhanced graphics and gameplay for an unparalleled gaming experience on the popular console.',
            ],
            [
                'name' => 'Indie Gems: Must-Play Games from Up-and-Coming Developers',
                'description' => 'Shine a spotlight on the indie gaming scene with a roundup of must-play titles from talented up-and-coming developers, showcasing unique gameplay and innovative storytelling.',
            ],
            [
                'name' => 'Gaming Community Spotlight: Celebrating the Passionate World of Speedrunning',
                'description' => 'Delve into the world of speedrunning as we highlight the dedicated gaming community breaking records and pushing the boundaries of their favorite titles in thrilling, high-speed fashion.',
            ],
            [
                'name' => "The Witcher 4 Teaser: What to Expect from Geralt's Next Adventure",
                'description' => "Get a sneak peek into the highly anticipated Witcher 4, exploring the rumors, teasers, and speculations surrounding Geralt of Rivia's next epic journey.",
            ],
            [
                'name' => 'Console Wars Escalate: Latest Developments in the Battle for Supremacy',
                'description' => 'Witness the ongoing battle between gaming consoles as companies unveil new strategies and exclusive titles to claim the throne in the fiercely competitive gaming industry.',
            ],
            [
                'name' => 'Gaming Nostalgia: Classic Titles Getting Modern Remakes',
                'description' => 'Take a trip down memory lane as beloved classic games receive modern makeovers, blending nostalgia with cutting-edge technology for a new generation of players.',
            ],
            [
                'name' => 'Star Wars Gaming Universe Expands: New Titles and Storylines Revealed',
                'description' => 'Explore the expanding Star Wars gaming universe with the latest announcements, revealing new titles, compelling storylines, and exciting adventures set in a galaxy far, far away.',
            ],
            [
                'name' => 'Monster Hunter Rise Expansion: New Monsters and Challenges Await',
                'description' => 'Prepare for the upcoming expansion of Monster Hunter Rise, featuring new monsters, challenging quests, and additional content that will test the skills of seasoned hunters.',
            ],
            [
                'name' => 'The Future of Cloud Gaming: Revolutionizing How We Play',
                'description' => 'Dive into the evolving landscape of cloud gaming as technology advancements promise seamless experiences across devices, eliminating the need for high-end hardware and expanding accessibility.',
            ],
            [
                'name' => 'Game Developers Conference 2024 Highlights: Innovations and Insights',
                'description' => 'Catch up on the latest from the Game Developers Conference, where industry professionals share insights, showcase innovations, and discuss the future of game development.',
            ],
            [
                'name' => "Marvel's Avengers: New DLC Expands the Superhero Universe",
                'description' => "Uncover the details of the latest downloadable content (DLC) for Marvel's Avengers, introducing new characters, missions, and story arcs to keep players immersed in the superhero universe.",
            ],
            [
                'name' => 'Fortnite Season 10: Map Changes, Events, and Exciting Rewards',
                'description' => 'Stay updated on the ever-evolving world of Fortnite as Season 10 unfolds, bringing map changes, in-game events, and a slew of exciting rewards for players to unlock and enjoy.',
            ],
            [
                'name' => 'Esports Scholarship Programs: Paving the Way for Future Pro Gamers',
                'description' => 'Explore the rise of esports scholarship programs, providing opportunities for talented gamers to pursue education while honing their skills, with a potential pathway to professional gaming.',
            ],
        ];
    }

    protected function getBlogCategoryData(): array
    {
        return [
            [
                'name' => 'Next-Gen Gaming',
            ],
            [
                'name' => 'eSports and Competitive Gaming',
            ],
            [
                'name' => 'Game Releases and Reviews',
            ],
            [
                'name' => 'Gaming Industry Business News',
            ],
            [
                'name' => 'Virtual Reality (VR) and Augmented Reality (AR) Gaming',
            ],
            [
                'name' => 'Indie Game Spotlight:',
            ],
            [
                'name' => 'Gaming Technology and Innovations',
            ],
            [
                'name' => 'Gaming Culture and Community Events',
            ],
            [
                'name' => 'Mobile and Casual Gaming',
            ],
            [
                'name' => 'Retro Gaming and Nostalgia',
            ],
        ];
    }

    protected function getFilePathImageForm(int $index): string
    {
        return $this->filePath('news/' . ($index + 1) . '.jpg', 'gaming');
    }
}
