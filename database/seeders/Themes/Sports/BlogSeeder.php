<?php

namespace Database\Seeders\Themes\Sports;

use Database\Seeders\Themes\Main\BlogSeeder as BaseBlogSeeder;

class BlogSeeder extends BaseBlogSeeder
{
    public function getData(): array
    {
        $this->uploadFiles('news', 'sports');

        return [
            [
                'name' => 'The Thrilling World of Mountain Biking',
                'description' => 'Dive into the adrenaline-fueled realm of mountain biking where riders conquer rugged terrains, challenging trails, and breathtaking landscapes. From downhill descents to technical climbs, every twist and turn promises an exhilarating adventure.',
            ],
            [
                'name' => 'Mastering the Art of Archery: Precision and Focus',
                'description' => 'Explore the ancient sport of archery, where skill, precision, and focus converge to hit the bullseye. From traditional bows to modern recurves, witness the mastery of archers as they draw, aim, and release with unparalleled accuracy.',
            ],
            [
                'name' => 'The Graceful Dance of Figure Skating',
                'description' => 'Step onto the glistening ice and witness the elegance of figure skating. With every glide, jump, and spin, skaters mesmerize audiences with their artistry and athleticism, creating a symphony of movement on the frozen stage.',
            ],
            [
                'name' => 'Sailing: Harnessing the Power of Wind and Water',
                'description' => 'Set sail on a journey across vast oceans and serene lakes, where sailors harness the power of wind and water. From navigating treacherous currents to riding the waves, each voyage offers a thrilling blend of challenge and tranquility.',
            ],
            [
                'name' => 'The Intensity of Rugby: A Collision of Strength and Strategy',
                'description' => 'Experience the raw intensity of rugby, where players collide in a test of strength, speed, and strategy. From bone-crunching tackles to strategic plays, every moment on the field is a battle for dominance and victory.',
            ],
            [
                'name' => 'Unleashing the Fury: Inside the World of Mixed Martial Arts',
                'description' => 'Step into the octagon and witness the raw intensity of mixed martial arts. With disciplines ranging from Brazilian Jiu-Jitsu to Muay Thai, fighters showcase their skill and courage in a relentless pursuit of victory.',
            ],
            [
                'name' => 'Climbing to New Heights: The Sport of Rock Climbing',
                'description' => 'Ascend to breathtaking heights and conquer towering cliffs in the exhilarating sport of rock climbing. With each handhold and foothold, climbers push their limits, defying gravity and embracing the thrill of the climb.',
            ],
            [
                'name' => 'The Artistry of Gymnastics: Grace, Strength, and Precision',
                'description' => 'Marvel at the awe-inspiring athleticism of gymnastics, where grace, strength, and precision unite in a dazzling display of acrobatics. From gravity-defying flips to',
            ],
            [
                'name' => 'The Beauty of Tennis: A Game of Skill and Strategy',
                'description' => 'Enter the world of tennis, where players wield rackets with finesse and strategy. From powerful serves to delicate volleys, each match unfolds with suspense and excitement, showcasing the beauty of this beloved sport.',
            ],
            [
                'name' => 'The Heart-Pounding Action of Boxing: Strength and Stamina',
                'description' => 'Step into the ring and feel the adrenaline rush of boxing, where fighters showcase their strength, speed, and stamina in a relentless pursuit of victory. From lightning-fast jabs to bone-crushing hooks, every punch packs a powerful punch.',
            ],
            [
                'name' => 'The Spirit of Basketball: Skill, Teamwork, and Strategy',
                'description' => 'Experience the fast-paced excitement of basketball, where players dribble, pass, and shoot their way to victory. From buzzer-beating shots to jaw-dropping dunks, each game is a showcase of skill, teamwork, and strategy.',
            ],
            [
                'name' => 'The Precision of Golf: A Test of Skill and Patience',
                'description' => 'Embark on a journey through the serene greens and rolling fairways of golf, where precision and patience are paramount. From long drives to delicate putts, each swing offers a test of skill and a chance for glory on the links.',
            ],
            [
                'name' => 'The Speed of Formula One Racing: Engineering Marvels on the Track',
                'description' => 'Witness the high-octane excitement of Formula One racing, where drivers push the limits of speed and precision in cutting-edge racing machines. From hairpin turns to blistering straightaways, each race is a showcase of engineering marvels and adrenaline-fueled action.',
            ],
            [
                'name' => 'The Majesty of Equestrian Sports: Grace and Power',
                'description' => 'Experience the timeless elegance of equestrian sports, where horse and rider move as one in a display of grace and power. From dressage to show jumping, each event showcases the bond between human and horse in a celebration of athleticism and beauty.',
            ],
            [
                'name' => 'The Excitement of Motorsports: Thrills and Spills',
                'description' => 'Buckle up for the adrenaline-fueled world of motorsports, where drivers race wheel-to-wheel in a quest for victory. From the roar of engines to the squeal of tires, each race is a heart-pounding spectacle of speed, skill, and strategy.',
            ],
            [
                'name' => 'The Grit of Marathon Running: Endurance and Determination',
                'description' => 'Challenge your limits and test your endurance in the grueling sport of marathon running. From the starting line to the finish, every step is a testament to the human spirit, as runners push through pain and fatigue to cross the ultimate finish line.',
            ],
            [
                'name' => 'The Majesty of Swimming: Graceful Strokes in the Water',
                'description' => 'Dive into the serene world of swimming, where graceful strokes propel athletes through the water with effortless ease. From freestyle to butterfly, each stroke is a symphony of motion, as swimmers chase records and glory in the pool.',
            ],
            [
                'name' => 'The Power of Weightlifting: Strength and Determination',
                'description' => 'Enter the world of weightlifting, where athletes push their bodies to the limit in a display of strength and determination. From the snatch to the clean and jerk, each lift is a test of power and technique, as lifters strive for personal bests and Olympic glory.',
            ],
            [
                'name' => 'The Art of Taekwondo: Discipline and Precision',
                'description' => 'Discover the ancient martial art of Taekwondo, where discipline and precision are the keys to success. From powerful kicks to lightning-fast strikes, practitioners master the art of self-defense while cultivating inner strength and confidence.',
            ],
            [
                'name' => 'The Thrill of Surfing: Riding the Waves',
                'description' => 'Catch the wave of excitement in the thrilling sport of surfing, where riders carve through towering waves with skill and style. From longboards to shortboards, each ride is a dance with the ocean, as surfers harness the power of the sea in search of the perfect wave.',
            ],
        ];
    }

    protected function getBlogCategoryData(): array
    {
        return [
            [
                'name' => 'Major League Madness',
            ],
            [
                'name' => 'Hoops Hysteria',
            ],
            [
                'name' => 'Football Frenzy',
            ],
            [
                'name' => 'Goal Line Glory',
            ],
            [
                'name' => 'Net Gains',
            ],
            [
                'name' => 'On the Ice',
            ],
            [
                'name' => 'Links and Greens',
            ],
            [
                'name' => 'Olympic Watch',
            ],
            [
                'name' => 'Racetrack Rundown',
            ],
            [
                'name' => 'Water World',
            ],
        ];
    }

    protected function getBlogTagData(): array
    {
        return [
            [
                'name' => 'Football News',
            ],
            [
                'name' => 'Basketball Updates',
            ],
            [
                'name' => 'Soccer World',
            ],
            [
                'name' => 'Olympic Insider',
            ],
            [
                'name' => 'Baseball Buzz',
            ],
            [
                'name' => 'Hockey Highlights',
            ],
            [
                'name' => 'Tennis Talk',
            ],
            [
                'name' => 'Golf Glimpses',
            ],
            [
                'name' => 'Sports Recap',
            ],
        ];
    }

    protected function getFilePathImageForm(int $index): string
    {
        return $this->filePath('news/' . ($index + 1) . '.jpg', 'sports');
    }
}
