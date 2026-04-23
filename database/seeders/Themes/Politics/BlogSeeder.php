<?php

namespace Database\Seeders\Themes\Politics;

use Database\Seeders\Themes\Main\BlogSeeder as BaseBlogSeeder;

class BlogSeeder extends BaseBlogSeeder
{
    public function getData(): array
    {
        $this->uploadFiles('news');

        return   [
            [
                'name' => 'Navigating Political Landscapes: Understanding the Dynamics of Global Alliances',
                'description' => 'Explore the intricate web of international relations and the impact of global alliances on political landscapes. Delve into the nuances that shape diplomatic strategies and geopolitical shifts.',
            ],
            [
                'name' => 'The Evolution of Political Discourse: A Historical Perspective',
                'description' => 'Trace the evolution of political discourse through the ages, examining how communication styles, rhetoric, and public engagement have shaped the political narrative.',
            ],
            [
                'name' => 'Beyond Borders: Exploring the Role of Supranational Organizations in Modern Politics',
                'description' => 'Investigate the influence of supranational organizations on contemporary politics, analyzing their role in addressing global challenges and fostering cooperation among nations.',
            ],
            [
                'name' => 'From the Streets to Social Media: The Power of Activism in Shaping Political Movements',
                'description' => 'Uncover the ways in which activism, both offline and online, plays a pivotal role in shaping political movements and influencing policy decisions.',
            ],
            [
                'name' => 'Populism and Democracy: Navigating the Thin Line',
                'description' => 'Examine the complexities surrounding populism and its impact on democratic institutions, analyzing the challenges posed by charismatic leaders and their populist agendas.',
            ],
            [
                'name' => 'Climate Change Policy: Bridging the Gap Between Science and Politics',
                'description' => 'Evaluate the intersection of science and politics in the formulation of climate change policies, exploring the challenges and opportunities for global environmental governance.',
            ],
            [
                'name' => 'Identity Politics: Understanding the Role of Social Identities in Political Discourse',
                'description' => 'Delve into the dynamics of identity politics, exploring how social identities shape political narratives, policies, and the overall political landscape.',
            ],
            [
                'name' => 'The Role of Technology in Modern Politics: Navigating the Digital Age',
                'description' => 'Investigate the impact of technology on political processes, examining how social media, artificial intelligence, and data analytics influence political campaigns and governance.',
            ],
            [
                'name' => 'Global Economic Policies: Balancing Growth and Equality',
                'description' => 'Analyze the challenges of crafting economic policies that promote both growth and equality on a global scale, exploring the tensions between competing economic ideologies.',
            ],
            [
                'name' => 'The Power of Diplomacy: Case Studies in Successful International Relations',
                'description' => 'Explore successful instances of diplomatic efforts, analyzing the strategies and negotiations that led to positive outcomes in resolving international conflicts.',
            ],
            [
                'name' => "Media's Role in Politics: Shaping Public Opinion and Influencing Elections",
                'description' => 'Examine the influence of media on political processes, analyzing how the media shapes public opinion, frames political discourse, and impacts electoral outcomes.',
            ],
            [
                'name' => 'Human Rights in Contemporary Politics: Struggles, Progress, and Challenges',
                'description' => 'Investigate the current state of human rights in the political arena, exploring both progress and challenges in the global effort to protect and promote human rights.',
            ],
            [
                'name' => 'Nationalism vs. Globalism: A Balancing Act in Modern Governance',
                'description' => 'Delve into the tensions between nationalism and globalism, analyzing how leaders navigate the balance between prioritizing national interests and participating in global cooperation.',
            ],
            [
                'name' => 'The Role of Non-Governmental Organizations (NGOs) in Shaping Political Agendas',
                'description' => 'Explore the impact of NGOs on political agendas, examining how these organizations influence policy-making and advocate for social and environmental issues.',
            ],
            [
                'name' => 'Post-Pandemic Politics: Resilience, Recovery, and the New Normal',
                'description' => 'Analyze the political implications of the post-pandemic era, exploring how governments worldwide are addressing the challenges of recovery, public health, and economic stability.',
            ],
            [
                'name' => 'Cybersecurity in Politics: Safeguarding Democracies in the Digital Age',
                'description' => 'Examine the increasing importance of cybersecurity in political landscapes, analyzing the threats posed by cyberattacks and the measures taken to secure electoral systems and government institutions.',
            ],
            [
                'name' => 'Political Polarization: Understanding Divides and Building Bridges',
                'description' => 'Investigate the root causes of political polarization, exploring strategies to bridge ideological divides and foster constructive dialogue in a polarized political climate.',
            ],
            [
                'name' => 'Feminism in Politics: Progress, Challenges, and the Fight for Equality',
                'description' => 'Examine the intersection of feminism and politics, analyzing the progress made in achieving gender equality and the ongoing challenges faced by women in political spheres.',
            ],
            [
                'name' => 'The Ethics of Political Leadership: Navigating Moral Dilemmas in Public Service',
                'description' => 'Explore the ethical challenges faced by political leaders, examining how moral dilemmas impact decision-making and public trust in government.',
            ],
            [
                'name' => 'Disinformation and Democracy: Countering the Threats to Information Integrity',
                'description' => 'Investigate the dangers of disinformation in democratic processes, exploring strategies to counteract false narratives, promote media literacy, and safeguard the integrity of information in politics.',
            ],
        ];
    }

    protected function getBlogCategoryData(): array
    {
        return [
            ['name' => 'Political Systems'],
            ['name' => 'Media and Politics'],
            ['name' => 'Elections and Campaigns'],
            ['name' => 'Political Ideologies'],
            ['name' => 'International Relations'],
            ['name' => 'Political Economy'],
            ['name' => 'Political Leadership'],
            ['name' => 'Political Ethics'],
            ['name' => 'Human Rights'],
            ['name' => 'Political Polarization'],
        ];
    }

    protected function getBlogTagData(): array
    {
        return [
            ['name' => 'Politics'],
            ['name' => 'Political Science'],
            ['name' => 'Elections'],
            ['name' => 'Government'],
            ['name' => 'Democracy'],
            ['name' => 'International Relations'],
            ['name' => 'Media'],
            ['name' => 'Policy'],
            ['name' => 'Political Theory'],
            ['name' => 'Public Opinion'],
        ];
    }

    protected function getFilePathImageForm(int $index): string
    {
        return $this->filePath('news/' . ($index + 1) . '.jpg', 'politics');
    }
}
