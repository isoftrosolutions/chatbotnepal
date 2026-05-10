<?php

use App\Models\HostedPage;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        User::where('role', 'client')
            ->whereDoesntHave('hostedPages')
            ->chunkById(100, function ($clients) {
                foreach ($clients as $client) {
                    HostedPage::create([
                        'client_id' => $client->id,
                        'slug' => HostedPage::generateUniqueSlug($client->company_name ?? $client->name),
                        'status' => 'active',
                        'public_config' => HostedPage::defaultPublicConfig($client),
                        'behavior_config' => HostedPage::defaultBehaviorConfig(),
                    ]);
                }
            });
    }

    public function down(): void
    {
        // keep data on rollback
    }
};
