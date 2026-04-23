<?php

namespace Database\Seeders\Themes\Photography;

use Database\Seeders\Themes\Main\BlogSeeder as BaseBlogSeeder;

class BlogSeeder extends BaseBlogSeeder
{
    public function getData(): array
    {
        $this->uploadFiles('news');

        return  [
            ['name' => 'The Majestic Tiger: King of the Jungle', 'description' => 'Explore the world of the majestic tiger, the largest cat species and the undisputed king of the jungle. Learn about its habitat, behavior, and conservation status, and discover what makes this iconic predator so awe-inspiring.'],
            ['name' => 'The Great Migration: Africa\'s Spectacular Wildlife Event', 'description' => 'Witness the spectacle of the great migration, one of the most incredible wildlife events on the planet. Follow millions of wildebeest, zebras, and other animals as they journey across the plains of East Africa in search of greener pastures, braving predators and treacherous river crossings along the way.'],
            ['name' => 'The Enigmatic Giant Squid: Deep Sea Mystery', 'description' => 'Dive into the depths of the ocean and discover the enigmatic giant squid, one of the most elusive and mysterious creatures of the deep sea. Learn about its anatomy, behavior, and the ongoing quest to unlock the secrets of this elusive cephalopod.'],
            ['name' => 'The Arctic\'s Polar Bears: Lords of the Frozen North', 'description' => 'Venture into the frozen wilderness of the Arctic and encounter the polar bear, the world\'s largest land carnivore and the undisputed lord of the frozen north. Learn about its adaptations to life in the extreme cold, its hunting techniques, and the challenges it faces due to climate change.'],
            ['name' => 'The Graceful Humpback Whale: Gentle Giants of the Ocean', 'description' => 'Set sail on a journey to encounter the graceful humpback whale, one of the largest and most iconic cetacean species. Learn about its migratory patterns, acrobatic displays, and the efforts to protect these gentle giants from threats such as whaling and habitat destruction.'],
            ['name' => 'The Mighty Elephant: Icons of the African Savanna', 'description' => 'Embark on a safari adventure to witness the mighty elephant, the largest land animal on Earth and a symbol of Africa\'s rich biodiversity. Learn about its social structure, communication methods, and the conservation efforts underway to protect these iconic pachyderms from poaching and habitat loss.'],
            ['name' => 'The Mysterious Axolotl: The Mexican Walking Fish', 'description' => 'Discover the mysterious axolotl, a unique amphibian species native to Mexico and known for its regenerative abilities and neotenic features. Learn about its unusual biology, its cultural significance in Aztec mythology, and the conservation efforts aimed at saving this endangered species from extinction.'],
            ['name' => 'The Cunning Arctic Fox: Master of Adaptation', 'description' => 'Explore the Arctic tundra and encounter the cunning Arctic fox, a resourceful predator adapted to life in one of the harshest environments on Earth. Learn about its unique hunting strategies, its ability to survive in freezing temperatures, and the ecological role it plays in the Arctic ecosystem.'],
            ['name' => 'The Colorful Coral Reef: Biodiversity Hotspot', 'description' => 'Dive into the vibrant world of the coral reef, one of the most biodiverse ecosystems on the planet and home to a dazzling array of marine life. Learn about the coral polyps that build these underwater cities, the fish and invertebrates that inhabit them, and the threats facing coral reefs due to climate change and human activity.'],
            ['name' => 'The Agile Cheetah: Speed Demon of the Savannah', 'description' => 'Experience the thrill of the chase with the agile cheetah, the fastest land animal on Earth and a masterful predator of the African savannah. Learn about its incredible speed, its hunting techniques, and the conservation efforts aimed at preserving this iconic big cat species.'],
            ['name' => 'The Stealthy Snow Leopard: Ghost of the Himalayas', 'description' => 'Embark on a journey to the rugged mountains of Central Asia and encounter the elusive snow leopard, a stealthy predator adapted to life in some of the highest altitudes on Earth. Learn about its solitary nature, its hunting prowess, and the conservation challenges it faces due to habitat loss and poaching.'],
            ['name' => 'The Playful Dolphin: Acrobats of the Sea', 'description' => 'Set sail on a marine adventure and encounter the playful dolphin, one of the most intelligent and charismatic marine mammals. Learn about its social behavior, its acrobatic displays, and the threats facing dolphins in the wild, from pollution to habitat degradation.'],
            ['name' => 'The Mischievous Red Panda: Guardian of the Bamboo Forest', 'description' => 'Venture into the lush forests of the Eastern Himalayas and meet the mischievous red panda, a charismatic arboreal mammal known for its distinctive appearance and playful demeanor. Learn about its diet of bamboo, its unique adaptations, and the conservation efforts aimed at protecting this endangered species from extinction.'],
            ['name' => 'The Regal Lion: King of the African Savannah', 'description' => 'Embark on a safari expedition to encounter the regal lion, the iconic apex predator of the African savannah and a symbol of strength and majesty. Learn about its social structure, its hunting techniques, and the conservation efforts underway to protect this iconic big cat species from extinction.'],
            ['name' => 'The Fascinating Octopus: Master of Camouflage', 'description' => 'Dive into the ocean depths and discover the fascinating world of the octopus, a highly intelligent and adaptable cephalopod known for its remarkable abilities and intricate behavior. Learn about its camouflage tactics, its problem-solving skills, and the ongoing research aimed at unlocking the secrets of its mysterious intelligence.'],
            ['name' => 'The Agile Orangutan: Guardians of the Rainforest', 'description' => 'Journey into the rainforests of Borneo and Sumatra and encounter the agile orangutan, one of our closest relatives in the animal kingdom and a symbol of conservation. Learn about its arboreal lifestyle, its complex social structure, and the threats facing orangutans due to deforestation and the illegal wildlife trade.'],
            ['name' => 'The Dazzling Hummingbird: Jewel of the Sky', 'description' => 'Witness the dazzling beauty of the hummingbird, one of the smallest and most colorful birds in the world and a masterful aerial acrobat. Learn about its unique adaptations for hovering flight, its iridescent plumage, and the vital role it plays in pollination and ecosystem health.'],
            ['name' => 'The Endearing Giant Panda: Icon of Conservation', 'description' => 'Meet the endearing giant panda, a beloved symbol of conservation and biodiversity. Learn about its bamboo diet, its gentle demeanor, and the conservation efforts that have helped save this iconic bear species from the brink of extinction.'],
            ['name' => 'The Stealthy Jaguar: Lord of the Amazon Rainforest', 'description' => 'Embark on a jungle adventure and encounter the stealthy jaguar, the largest cat species in the Americas and a top predator of the Amazon rainforest. Learn about its solitary lifestyle, its powerful hunting techniques, and the conservation challenges it faces due to habitat loss and human encroachment.'],
            ['name' => 'The Elegant Swan: Graceful Symbol of Beauty', 'description' => 'Admire the elegance of the swan, a graceful waterfowl known for its beauty and poise. Learn about its courtship rituals, its migratory patterns, and the cultural significance of this iconic bird species in mythology and folklore.'],
        ];
    }

    protected function getBlogCategoryData(): array
    {
        return [
            ['name' => 'Big Cats'],
            ['name' => 'Marine Life'],
            ['name' => 'Birds'],
            ['name' => 'Primates'],
            ['name' => 'Reptiles'],
            ['name' => 'Amphibians'],
            ['name' => 'Insects'],
            ['name' => 'Endangered Species'],
            ['name' => 'Wildlife Conservation'],
            ['name' => 'Animal Behavior'],
        ];
    }

    protected function getBlogTagData(): array
    {
        return [
            ['name' => 'Animals'],
            ['name' => 'Wildlife'],
            ['name' => 'Nature'],
            ['name' => 'Biodiversity'],
            ['name' => 'Conservation'],
            ['name' => 'Zoology'],
            ['name' => 'Ecology'],
            ['name' => 'Endangered'],
            ['name' => 'Habitat'],
            ['name' => 'Animal Kingdom'],
        ];
    }

    protected function getFilePathImageForm(int $index): string
    {
        return $this->filePath('news/' . ($index + 1) . '.jpg', 'photography');
    }
}
