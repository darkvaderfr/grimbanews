<?php

namespace Database\Seeders\Themes\AI;

use Database\Seeders\Themes\Main\BlogSeeder as BaseBlogSeeder;

class BlogSeeder extends BaseBlogSeeder
{
    public function getData(): array
    {
        $this->uploadFiles('news');

        return  [
            ['name' => 'Understanding Neural Networks', 'description' => 'Explore the inner workings of neural networks and their applications in artificial intelligence. From feedforward to recurrent models, dive deep into the fundamentals of this powerful technology.'],
            ['name' => 'Natural Language Processing Essentials', 'description' => 'Discover the fascinating world of natural language processing (NLP) and its role in AI. Learn about techniques like sentiment analysis, named entity recognition, and language generation, and their real-world applications.'],
            ['name' => 'Introduction to Machine Learning Algorithms', 'description' => 'Get started with machine learning algorithms, from classic models like linear regression to advanced techniques like support vector machines and random forests. Learn how to apply these algorithms to solve real-world problems.'],
            ['name' => 'The Rise of Deep Learning', 'description' => 'Witness the rise of deep learning and its impact on artificial intelligence. From image recognition to speech synthesis, explore the groundbreaking advancements made possible by deep neural networks.'],
            ['name' => 'Reinforcement Learning Basics', 'description' => 'Delve into the world of reinforcement learning and its role in training intelligent agents. Learn about reward functions, policy gradients, and value iteration, and see how these concepts are applied in autonomous systems.'],
            ['name' => 'Computer Vision Applications in AI', 'description' => 'Discover the exciting field of computer vision and its applications in artificial intelligence. From object detection to image segmentation, explore the algorithms and techniques used to analyze and interpret visual data.'],
            ['name' => 'Ethical Considerations in AI Development', 'description' => 'Examine the ethical implications of AI development and deployment. From bias in algorithms to concerns about privacy and security, explore the complex issues surrounding the responsible use of artificial intelligence.'],
            ['name' => 'Robotics and AI: The Future of Automation', 'description' => 'Explore the intersection of robotics and artificial intelligence and its potential to revolutionize industries. From autonomous vehicles to smart manufacturing, discover how AI-powered robots are reshaping the way we work and live.'],
            ['name' => 'AI in Healthcare: Transforming Medicine', 'description' => 'Learn about the transformative impact of artificial intelligence on healthcare. From medical imaging to drug discovery, explore how AI is revolutionizing diagnosis, treatment, and patient care.'],
            ['name' => 'AI in Finance: Revolutionizing the Industry', 'description' => 'Discover how artificial intelligence is revolutionizing the finance industry. From algorithmic trading to risk management, explore the applications of AI in banking, insurance, and investment management.'],
            ['name' => 'Exploring Generative Adversarial Networks (GANs)', 'description' => 'Dive into the world of generative adversarial networks (GANs) and their applications in AI. Learn how GANs are used to generate realistic images, videos, and text, and explore their potential for creative applications.'],
            ['name' => 'The Future of AI: Trends and Predictions', 'description' => 'Explore the future of artificial intelligence and emerging trends in the field. From explainable AI to AI ethics, delve into the challenges and opportunities that lie ahead as AI continues to evolve and transform industries.'],
            ['name' => 'AI-Powered Personal Assistants: Beyond Siri and Alexa', 'description' => 'Discover the latest advancements in AI-powered personal assistants and virtual agents. From natural language understanding to context-aware recommendations, explore how these technologies are revolutionizing the way we interact with computers and devices.'],
            ['name' => 'AI for Social Good: Addressing Global Challenges', 'description' => 'Learn about the role of artificial intelligence in addressing global challenges and promoting social good. From disaster response to healthcare access, explore how AI is being used to tackle some of the world\'s most pressing issues and create positive change.'],
            ['name' => 'The Ethics of AI: Bias, Fairness, and Accountability', 'description' => 'Examine the ethical considerations surrounding the development and deployment of artificial intelligence. From algorithmic bias to data privacy, explore the principles and practices that ensure AI is developed and used responsibly.'],
            ['name' => 'Advancements in AI Hardware: GPUs, TPUs, and Beyond', 'description' => 'Explore the latest advancements in AI hardware and accelerators, from graphics processing units (GPUs) to tensor processing units (TPUs). Learn how these specialized chips are powering the next generation of AI applications and driving innovation in the field.'],
            ['name' => 'AI in Education: Personalized Learning and Adaptive Assessments', 'description' => 'Discover how artificial intelligence is transforming education through personalized learning and adaptive assessments. From intelligent tutoring systems to automated grading, explore how AI is revolutionizing teaching and learning in classrooms around the world.'],
            ['name' => 'AI in Agriculture: Precision Farming and Crop Monitoring', 'description' => 'Learn about the applications of artificial intelligence in agriculture and farming. From precision farming to crop monitoring, explore how AI-powered technologies are optimizing agricultural processes and improving crop yields.'],
            ['name' => 'AI in Transportation: Autonomous Vehicles and Traffic Management', 'description' => 'Explore the role of artificial intelligence in transportation and mobility. From self-driving cars to intelligent traffic management systems, discover how AI is revolutionizing the way we move people and goods.'],
        ];
    }

    protected function getBlogCategoryData(): array
    {
        return [
            ['name' => 'Neural Networks'],
            ['name' => 'Natural Language Processing'],
            ['name' => 'Machine Learning'],
            ['name' => 'Deep Learning'],
            ['name' => 'Reinforcement Learning'],
            ['name' => 'Computer Vision'],
            ['name' => 'Ethics in AI'],
            ['name' => 'Robotics'],
            ['name' => 'AI in Healthcare'],
            ['name' => 'AI in Finance'],
        ];
    }

    protected function getBlogTagData(): array
    {
        return [
            ['name' => 'Artificial Intelligence'],
            ['name' => 'Machine Learning'],
            ['name' => 'Deep Learning'],
            ['name' => 'Neural Networks'],
            ['name' => 'Natural Language Processing'],
            ['name' => 'Computer Vision'],
            ['name' => 'Reinforcement Learning'],
            ['name' => 'Robotics'],
            ['name' => 'Healthcare AI'],
            ['name' => 'Finance AI'],
        ];
    }

    protected function getFilePathImageForm(int $index): string
    {
        return $this->filePath('news/' . ($index + 1) . '.jpg', 'ai');
    }
}
