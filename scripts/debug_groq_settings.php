<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$keys = [
    'grok_api_key',
    'groq_api_url',
    'grok_model',
    'grok_max_tokens',
    'grok_temperature',
];

foreach ($keys as $key) {
    $val = App\Models\Setting::get($key, null);
    if ($key === 'grok_api_key') {
        $masked = $val ? (substr($val, 0, 8) . '...' . substr($val, -4)) : '(empty)';
        echo $key . "\t" . $masked . PHP_EOL;
        continue;
    }
    echo $key . "\t" . ($val === null ? '(null)' : (string) $val) . PHP_EOL;
}
