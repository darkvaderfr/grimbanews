<?php

namespace Database\Seeders\Themes\Main;

use Botble\Base\Facades\MetaBox;
use Botble\Member\Models\Member;
use Botble\Member\Models\MemberActivityLog;
use Botble\Slug\Facades\SlugHelper;
use Botble\Slug\Models\Slug;
use Botble\Theme\Database\Seeders\ThemeSeeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class MemberSeeder extends ThemeSeeder
{
    public function run(): void
    {
        $files = $this->uploadFiles('members', 'main');

        Member::query()->truncate();
        MemberActivityLog::query()->truncate();

        $faker = $this->fake();
        $now = $this->now();

        $socialLinks = [
            [
                [
                    'key' => 'name',
                    'value' => 'Facebook',
                ],
                [
                    'key' => 'icon',
                    'value' => 'ti ti-brand-facebook',
                ],
                [
                    'key' => 'url',
                    'value' => 'https://www.facebook.com/',
                ],
            ],
            [
                [
                    'key' => 'name',
                    'value' => 'Instagram',
                ],
                [
                    'key' => 'icon',
                    'value' => 'ti ti-brand-instagram',
                ],
                [
                    'key' => 'url',
                    'value' => 'https://www.instagram.com/',
                ],
            ],
            [
                [
                    'key' => 'name',
                    'value' => 'Twitter',
                ],
                [
                    'key' => 'icon',
                    'value' => 'ti ti-brand-x',
                ],
                [
                    'key' => 'url',
                    'value' => 'https://www.twitter.com/',
                ],
            ],
            [
                [
                    'key' => 'name',
                    'value' => 'YouTube',
                ],
                [
                    'key' => 'icon',
                    'value' => 'ti ti-brand-youtube',
                ],
                [
                    'key' => 'url',
                    'value' => 'https://www.youtube.com/',
                ],
            ],
            [
                [
                    'key' => 'name',
                    'value' => 'Pinterest',
                ],
                [
                    'key' => 'icon',
                    'value' => 'ti ti-brand-pinterest',
                ],
                [
                    'key' => 'url',
                    'value' => 'https://www.pinterest.com/',
                ],
            ],
        ];

        $titles = [
            'Sr. Manager',
            'Chef Editor',
            'Creative Director',
            'Chef Marketing Officer',
            'Marketing Director',
        ];

        $defaultMember = Member::query()->create([
            'first_name' => 'John',
            'last_name' => 'Smith',
            'email' => 'member@archielite.com',
            'password' => Hash::make('12345678'),
            'dob' => $faker->dateTime(),
            'phone' => $faker->phoneNumber(),
            'avatar_id' => ! $files[0]['error'] ? $files[0]['data']->id : 0,
            'description' => $faker->realText(30),
            'confirmed_at' => $now,
        ]);

        MetaBox::saveMetaBoxData($defaultMember, 'title', $titles[rand(0, count($titles) - 1)]);
        MetaBox::saveMetaBoxData($defaultMember, 'social_links', $socialLinks);

        for ($i = 0; $i < 7; $i++) {
            $firstName = $faker->firstName();
            $lastName = $faker->lastName();

            $name = $firstName . ' ' . $lastName;

            $member = Member::query()->create([
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $faker->email(),
                'password' => Hash::make('12345678'),
                'dob' => $faker->dateTime(),
                'phone' => $faker->phoneNumber(),
                'avatar_id' => ! $files[$i + 1]['error'] ? $files[$i + 1]['data']->id : 0,
                'description' => 'Hi, I’m ' . $name . ', Your Blogging Journey Guide 🖋️. Writing, one blog post at a time, to inspire, inform, and ignite your curiosity. Join me as we explore the world through words and embark on a limitless adventure of knowledge and creativity. Let’s bring your thoughts to life on these digital pages. 🌟 #BloggingAdventures',
                'confirmed_at' => $now,
            ]);

            MetaBox::saveMetaBoxData($member, 'title', $titles[rand(0, count($titles) - 1)]);
            MetaBox::saveMetaBoxData($member, 'social_links', $socialLinks);

            $member->save();
        }

        foreach (Member::query()->get() as $member) {
            Slug::query()->create([
                'reference_type' => Member::class,
                'reference_id' => $member->getKey(),
                'key' => Str::slug($member->first_name . ' ' . $member->last_name),
                'prefix' => SlugHelper::getPrefix(Member::class, 'member'),
            ]);
        }
    }
}
