<?php

namespace Database\Seeders;

use App\Models\KnowledgeBase;
use App\Models\User;
use App\Models\WidgetConfig;
use Illuminate\Database\Seeder;

class DemoClientSeeder extends Seeder
{
    public function run(): void
    {
        $client = User::updateOrCreate(
            ['email' => 'jimsathi@demo.com'],
            [
                'name' => 'Jim Sathi Fitness',
                'password' => bcrypt('Demo@123'),
                'role' => 'client',
                'phone' => '9800000000',
                'company_name' => 'Jim Sathi Gym',
                'website_url' => 'https://jimsathi.com',
                'plan' => 'basic',
                'status' => 'active',
                'chatbot_enabled' => true,
                'api_token' => 'demo_token_jimsathi_'.bin2hex(random_bytes(16)),
            ]
        );

        WidgetConfig::updateOrCreate(
            ['user_id' => $client->id],
            [
                'welcome_message' => 'Namaste! Welcome to Jim Sathi Fitness. How can I help you today?',
                'primary_color' => '#4F46E5',
                'position' => 'bottom-right',
                'bot_name' => 'Jim Sathi Assistant',
                'show_powered_by' => true,
            ]
        );

        $kbFiles = [
            [
                'file_name' => 'about.md',
                'file_type' => 'about',
                'content' => "# About Jim Sathi Fitness\n\nJim Sathi is a premium fitness center located in Bharatpur, Chitwan, Nepal.\nWe have been serving the community since 2020.\nOur gym features modern equipment, experienced trainers, and a friendly environment.",
            ],
            [
                'file_name' => 'services.md',
                'file_type' => 'services',
                'content' => "# Our Services & Pricing\n\n- Gym Membership: Rs. 2,000/month\n- Personal Training: Rs. 5,000/month\n- Yoga Classes: Rs. 1,500/month\n- Zumba Classes: Rs. 1,500/month\n- Combo (Gym + Yoga): Rs. 3,000/month\n- Student Discount: 20% off on all plans",
            ],
            [
                'file_name' => 'faq.md',
                'file_type' => 'faq',
                'content' => "# Frequently Asked Questions\n\n**Q: What are your opening hours?**\nA: We are open from 5:30 AM to 9:00 PM, Monday to Saturday. Sunday is closed.\n\n**Q: Do you have female trainers?**\nA: Yes, we have dedicated female trainers available for female members.\n\n**Q: Is there parking available?**\nA: Yes, free parking is available for all members.\n\n**Q: Can I freeze my membership?**\nA: Yes, you can freeze up to 15 days per year with prior notice.",
            ],
            [
                'file_name' => 'contact.md',
                'file_type' => 'contact',
                'content' => "# Contact Information\n\n- Phone: 9800000000\n- Email: info@jimsathi.com\n- Location: Narayangarh Chowk, Bharatpur, Chitwan\n- Facebook: facebook.com/jimsathi\n- Instagram: @jimsathi",
            ],
        ];

        foreach ($kbFiles as $file) {
            KnowledgeBase::updateOrCreate(
                ['user_id' => $client->id, 'file_name' => $file['file_name']],
                [
                    'file_type' => $file['file_type'],
                    'content' => $file['content'],
                    'is_active' => true,
                ]
            );
        }
    }
}
