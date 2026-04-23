<?php

namespace Database\Seeders\Themes\Travel;

use Database\Seeders\Themes\Main\BlogSeeder as BaseBlogSeeder;

class BlogSeeder extends BaseBlogSeeder
{
    public function getData(): array
    {
        $this->uploadFiles('news', 'travel');

        return [
            [
                'name' => 'Exploring the Enchanting Streets of Paris',
                'description' => 'Stroll along the cobbled streets of Paris, where every corner reveals a new enchantment. From the iconic Eiffel Tower to the charming Montmartre, immerse yourself in the romance and beauty of the City of Light.',
            ],
            [
                'name' => 'Discovering the Wonders of Ancient Rome',
                'description' => 'Step back in time and explore the ancient wonders of Rome. From the grandeur of the Colosseum to the awe-inspiring ruins of the Roman Forum, experience the rich history and culture of the Eternal City.',
            ],
            [
                'name' => 'Adventures in the Serengeti: A Safari Experience',
                'description' => 'Embark on a safari adventure in the vast plains of the Serengeti. Witness the majestic herds of wildebeest and zebras, as well as the elusive predators like lions and cheetahs, in their natural habitat.',
            ],
            [
                'name' => 'Journeying Through the Land of Fire and Ice: Iceland',
                'description' => 'Experience the otherworldly landscapes of Iceland, where fire and ice collide in a symphony of natural wonders. From the cascading waterfalls of Gullfoss to the steaming geysers of Geysir, every moment is a revelation.',
            ],
            [
                'name' => 'Island Paradise: Exploring the Maldives',
                'description' => 'Escape to the idyllic paradise of the Maldives, where crystal-clear waters and white sandy beaches await. Dive into the vibrant coral reefs, relax in luxurious overwater bungalows, and soak up the sun in this tropical haven.',
            ],
            [
                'name' => 'The Majesty of Machu Picchu: Ancient Ruins in the Andes',
                'description' => 'Trek through the rugged Andes Mountains to discover the ancient Inca citadel of Machu Picchu. Perched high above the clouds, this UNESCO World Heritage Site offers breathtaking views and a glimpse into a bygone civilization.',
            ],
            [
                'name' => 'Cruising the Fjords of Norway: A Journey Through Nature',
                'description' => 'Set sail on a voyage through the stunning fjords of Norway, where towering cliffs and cascading waterfalls create a dramatic backdrop. From the picturesque villages of Flam to the icy beauty of Sognefjord, each fjord offers a new adventure.',
            ],
            [
                'name' => 'Trekking the Inca Trail to the Lost City of Choquequirao',
                'description' => 'Embark on a challenging trek along the Inca Trail to discover the hidden ruins of Choquequirao. Tucked away in the Peruvian Andes, this ancient city rivals Machu Picchu in grandeur and offers a more remote and rewarding experience.',
            ],
            [
                'name' => 'Sailing the Greek Islands: Sun, Sea, and Ancient Ruins',
                'description' => 'Set sail on a yacht to explore the enchanting Greek Islands, where azure waters and whitewashed villages await. Discover ancient ruins on islands like Delos and Rhodes, and relax on secluded beaches in this Mediterranean paradise.',
            ],
            [
                'name' => 'City of Contrasts: Exploring Tokyo, Japan',
                'description' => 'Experience the vibrant energy and rich culture of Tokyo, where tradition and modernity coexist in perfect harmony. From the bustling streets of Shibuya to the serene gardens of Meiji Shrine, Tokyo offers a captivating blend of old and new.',
            ],
            [
                'name' => 'Safari Adventure in Kruger National Park, South Africa',
                'description' => 'Embark on a safari adventure in Kruger National Park, one of Africa\'s largest game reserves. Encounter the "Big Five" – lions, elephants, buffaloes, leopards, and rhinos – as well as a diverse array of wildlife in their natural habitat.',
            ],
            [
                'name' => 'The Magic of Marrakech: Exploring the Red City',
                'description' => 'Immerse yourself in the exotic sights and sounds of Marrakech, where ancient history meets bustling markets and vibrant street life. Explore the labyrinthine alleyways of the Medina, visit the iconic Koutoubia Mosque, and experience the magic of Djemaa el-Fna square.',
            ],
            [
                'name' => 'Trekking to Everest Base Camp: A Himalayan Adventure',
                'description' => 'Embark on the ultimate trekking adventure to Everest Base Camp, where towering peaks and breathtaking vistas await. Journey through remote mountain villages, cross suspension bridges over rushing rivers, and experience the awe-inspiring beauty of the Himalayas.',
            ],
            [
                'name' => 'Cultural Odyssey in Kyoto, Japan: Temples, Tea, and Tradition',
                'description' => 'Discover the cultural heart of Japan in Kyoto, where ancient temples, traditional tea houses, and serene gardens await. Explore historic sites like Kinkaku-ji and Fushimi Inari Shrine, and immerse yourself in the timeless beauty of this historic city.',
            ],
            [
                'name' => 'Paradise Found: Relaxing in the Seychelles',
                'description' => 'Escape to the pristine beaches and turquoise waters of the Seychelles, where luxury resorts and untouched nature await. Snorkel among colorful coral reefs, hike through lush rainforests, and unwind on some of the world\'s most beautiful beaches.',
            ],
            [
                'name' => 'A Taste of Tuscany: Exploring Italy\'s Wine Country',
                'description' => 'Indulge in the culinary delights and scenic beauty of Tuscany, Italy\'s famed wine country. Explore charming hilltop towns like Montepulciano and San Gimignano, sample world-renowned wines in picturesque vineyards, and savor the flavors of Italian cuisine.',
            ],
            [
                'name' => 'Adventure in the Amazon Rainforest: Exploring the Jungle',
                'description' => 'Embark on a thrilling adventure into the heart of the Amazon rainforest, where exotic wildlife and lush vegetation await. Cruise along winding rivers, trek through dense jungle trails, and immerse yourself in the sights and sounds of one of the world\'s most biodiverse ecosystems.',
            ],
            [
                'name' => 'The Magic of Venice: Exploring Italy\'s Floating City',
                'description' => 'Discover the timeless beauty and romantic allure of Venice, the "City of Canals." Cruise along the Grand Canal in a gondola, wander through narrow alleyways lined with historic palaces, and marvel at iconic landmarks like St. Mark\'s Basilica and the Rialto Bridge.',
            ],
            [
                'name' => 'Chasing the Northern Lights: Aurora Hunting in Iceland',
                'description' => 'Embark on an unforgettable adventure to witness the spectacular Northern Lights in Iceland. Journey to remote locations far from city lights, where the dancing colors of the aurora borealis illuminate the night sky in a breathtaking display.',
            ],
            [
                'name' => 'Savoring the Flavors of Provence: Culinary Delights in France',
                'description' => 'Indulge in the culinary delights of Provence, where fresh ingredients and vibrant flavors reign supreme. Explore bustling markets, sample local specialties like ratatouille and bouillabaisse, and savor the world-renowned wines of this picturesque region.',
            ],
        ];
    }

    protected function getBlogCategoryData(): array
    {
        $this->uploadFiles('blog-categories', 'travel');

        $data = [
            ['name' => 'Adventure Destinations'],
            ['name' => 'Cultural Experiences'],
            ['name' => 'Luxury Retreats'],
            ['name' => 'Off-the-Beaten-Path'],
            ['name' => 'Family-Friendly Getaways'],
            ['name' => 'Solo Travel Adventures'],
            ['name' => 'Budget Travel Tips'],
            ['name' => 'Food and Culinary Tours'],
            ['name' => 'Outdoor Adventures'],
            ['name' => 'Historical Landmarks'],
        ];

        foreach ($data as $index => $item) {
            $data[$index]['metadata']['image'] = $this->filePath('blog-categories/' . $index + 1 . '.jpg', 'travel');
        }

        return $data;
    }

    protected function getBlogTagData(): array
    {
        return [
            ['name' => 'Beach'],
            ['name' => 'Adventure'],
            ['name' => 'Culture'],
            ['name' => 'Food'],
            ['name' => 'Nature'],
            ['name' => 'History'],
            ['name' => 'City'],
            ['name' => 'Hiking'],
            ['name' => 'Sightseeing'],
            ['name' => 'Shopping'],
        ];
    }

    protected function getFilePathImageForm(int $index): string
    {
        return $this->filePath('news/' . ($index + 1) . '.jpg', 'travel');
    }
}
