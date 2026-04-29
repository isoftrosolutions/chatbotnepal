<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\KnowledgeBase;
use App\Models\WidgetConfig;
use Illuminate\Support\Str;

$email = 'info@avocadoresort.com';
$user = User::updateOrCreate(
    ['email' => $email],
    [
        'name' => 'Avocado Resort Admin',
        'password' => bcrypt('Avocado@123'),
        'role' => 'client',
        'company_name' => 'Avocado and Orchid Resort',
        'website_url' => 'https://www.avocadoresort.com/',
        'status' => 'active',
        'chatbot_enabled' => true,
        'api_token' => 'avocado_token_' . bin2hex(random_bytes(16)),
        'site_id' => 'avocado-resort-' . Str::lower(Str::random(6)),
    ]
);

echo "User created: {$user->name} (ID: {$user->id})\n";

WidgetConfig::updateOrCreate(
    ['user_id' => $user->id],
    [
        'welcome_message' => 'Welcome to Avocado and Orchid Resort! How can I help you with your stay today?',
        'primary_color' => '#2D5A27',
        'position' => 'bottom-right',
        'bot_name' => 'Avocado Assistant',
    ]
);

$files = glob(__DIR__ . '/../avocado md  files/*.md');
foreach ($files as $file) {
    $filename = basename($file);
    $content = file_get_contents($file);
    
    // Determine type
    $type = 'custom';
    if (str_contains($filename, 'overview')) $type = 'about';
    elseif (str_contains($filename, 'faq')) $type = 'faq';
    elseif (str_contains($filename, 'dining')) $type = 'services';
    elseif (str_contains($filename, 'rooms')) $type = 'services';
    elseif (str_contains($filename, 'transport')) $type = 'contact';
    
    KnowledgeBase::updateOrCreate(
        ['user_id' => $user->id, 'file_name' => $filename],
        [
            'file_type' => $type,
            'content' => $content,
            'is_active' => true,
        ]
    );
    echo "Imported: {$filename} as {$type}\n";
}
