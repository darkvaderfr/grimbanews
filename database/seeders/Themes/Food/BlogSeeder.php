<?php

namespace Database\Seeders\Themes\Food;

use Database\Seeders\Themes\Main\BlogSeeder as BaseBlogSeeder;

class BlogSeeder extends BaseBlogSeeder
{
    public function getData(): array
    {
        $this->uploadFiles('news');

        return  [
            ['name' => 'Exploring Global Cuisine: A Culinary Journey', 'description' => 'Embark on a culinary adventure around the world and discover the diverse flavors of global cuisine. From spicy curries in India to savory sushi in Japan, explore the rich culinary traditions that define different cultures.'],
            ['name' => 'Farm-to-Table Dining: Celebrating Local Ingredients', 'description' => 'Experience the farm-to-table movement and savor the flavors of locally sourced ingredients. From organic vegetables to grass-fed meats, discover the freshness and quality of farm-fresh dining at restaurants committed to sustainability.'],
            ['name' => 'The Art of Food Presentation: Elevating Dining Experiences', 'description' => 'Explore the art of food presentation and its impact on dining experiences. From intricate plating techniques to creative garnishes, discover how chefs transform dishes into works of art that delight the senses and tantalize the palate.'],
            ['name' => 'Healthy Eating Habits: Nourishing the Body and Mind', 'description' => 'Learn about the importance of healthy eating habits and their impact on overall well-being. From balanced meals to mindful eating practices, discover how nutrition plays a crucial role in promoting physical and mental health.'],
            ['name' => 'Street Food Delights: Exploring Culinary Gems', 'description' => 'Embark on a street food adventure and explore the vibrant flavors of local culinary gems. From bustling food markets to roadside stalls, discover the authentic dishes and unique flavors that define street food culture around the world.'],
            ['name' => 'Artisanal Food and Craftsmanship: Honoring Tradition', 'description' => 'Celebrate artisanal food and craftsmanship and savor the quality and authenticity of handcrafted products. From artisanal cheeses to small-batch chocolates, explore the dedication and passion behind artisanal food production.'],
            ['name' => 'Wine Tasting and Pairing: An Introduction to Oenology', 'description' => 'Embark on a journey into the world of wine tasting and pairing and discover the art and science of oenology. From the basics of wine appreciation to the principles of food and wine pairing, explore the sensory delights of the vineyard.'],
            ['name' => 'Cocktail Culture: Crafting the Perfect Drink', 'description' => 'Explore the world of cocktail culture and learn how to craft the perfect drink. From classic cocktails to modern mixology, discover the techniques and ingredients that go into creating signature drinks that delight the palate and enliven the senses.'],
            ['name' => 'The Rise of Plant-Based Cuisine: Embracing Veganism', 'description' => 'Discover the rise of plant-based cuisine and the growing popularity of veganism. From plant-based burgers to dairy-free desserts, explore the creative and delicious ways chefs are reimagining classic dishes with plant-based ingredients.'],
            ['name' => 'Dining Etiquette: Navigating Social Conventions', 'description' => 'Learn the art of dining etiquette and navigate social conventions with grace and confidence. From table manners to formal dining protocols, discover the rules and traditions that govern dining etiquette in different cultures around the world.'],
            ['name' => 'The Art of Baking: Mastering the Pastry Kitchen', 'description' => 'Embark on a journey into the art of baking and master the pastry kitchen. From flaky croissants to decadent cakes, explore the techniques and recipes that elevate baking from a hobby to a culinary art form.'],
            ['name' => 'Sustainable Dining Practices: Promoting Environmental Responsibility', 'description' => 'Discover the importance of sustainable dining practices and their role in promoting environmental responsibility. From reducing food waste to supporting local farmers, explore the ways restaurants and consumers can make a positive impact on the planet through sustainable food choices.'],
            ['name' => 'The Joy of Home Cooking: Creating Memorable Meals', 'description' => 'Celebrate the joy of home cooking and discover the satisfaction of creating memorable meals from scratch. From family recipes to experimental dishes, explore the creativity and comfort of cooking at home with love and care.'],
            ['name' => 'Gourmet Getaways: Culinary Travel Adventures', 'description' => 'Embark on a gourmet getaway and indulge in culinary travel adventures around the world. From food and wine tours to cooking classes, discover the delicious delights and cultural experiences that await in destinations known for their gastronomic offerings.'],
            ['name' => 'Food Photography: Capturing Culinary Moments', 'description' => 'Explore the art of food photography and learn how to capture culinary moments with style and flair. From lighting techniques to composition tips, discover the secrets to creating stunning images that showcase the beauty and deliciousness of food.'],
            ['name' => 'The Art of Tea: Exploring Tea Culture', 'description' => 'Discover the art of tea and explore the rich traditions of tea culture around the world. From Chinese tea ceremonies to Japanese matcha rituals, explore the history, rituals, and health benefits of tea drinking.'],
            ['name' => 'Gastronomy and Culture: Exploring Culinary Traditions', 'description' => 'Explore the intersection of gastronomy and culture and discover the culinary traditions that define different societies. From traditional recipes to festive celebrations, explore how food reflects the cultural identity and heritage of communities around the world.'],
            ['name' => 'The Craft Beer Revolution: Exploring Breweries', 'description' => 'Experience the craft beer revolution and explore the vibrant world of microbreweries and brewpubs. From hoppy IPAs to rich stouts, discover the diversity and creativity of craft beer and the passionate brewers behind each brew.'],
            ['name' => 'The Art of Hospitality: Creating Memorable Dining Experiences', 'description' => 'Learn the art of hospitality and discover the secrets to creating memorable dining experiences. From attentive service to personalized touches, explore the principles and practices that elevate restaurants from ordinary to extraordinary.'],
            ['name' => 'Food Trends and Innovations: Exploring Culinary Creativity', 'description' => 'Stay up-to-date with the latest food trends and innovations shaping the culinary world. From fusion cuisine to food trucks, explore the creativity and diversity of the modern food scene and the chefs pushing the boundaries of culinary convention.'],
        ];
    }

    protected function getBlogCategoryData(): array
    {
        return [
            ['name' => 'Global Cuisine'],
            ['name' => 'Farm-to-Table Dining'],
            ['name' => 'Food Presentation'],
            ['name' => 'Healthy Eating'],
            ['name' => 'Street Food'],
            ['name' => 'Artisanal Food'],
            ['name' => 'Wine Tasting'],
            ['name' => 'Cocktail Culture'],
            ['name' => 'Plant-Based Cuisine'],
            ['name' => 'Dining Etiquette'],
        ];
    }

    protected function getBlogTagData(): array
    {
        return [
            ['name' => 'Food'],
            ['name' => 'Cooking'],
            ['name' => 'Cuisine'],
            ['name' => 'Restaurant'],
            ['name' => 'Gastronomy'],
            ['name' => 'Wine'],
            ['name' => 'Cocktails'],
            ['name' => 'Healthy Eating'],
            ['name' => 'Vegan'],
            ['name' => 'Baking'],
        ];
    }

    protected function getFilePathImageForm(int $index): string
    {
        return $this->filePath('news/' . ($index + 1) . '.jpg', 'food');
    }
}
