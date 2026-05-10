<?php

namespace App\Http\Controllers;

use App\Models\HostedPage;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class HostedChatPageController extends Controller
{
    public function show(string $slug)
    {
        $hostedPageData = Cache::remember("hosted_page:slug:{$slug}", 300, function () use ($slug) {
            return HostedPage::query()
                ->where('slug', $slug)
                ->where('status', 'active')
                ->first(['id', 'client_id', 'slug', 'public_config'])
                ?->toArray();
        });

        if (! is_array($hostedPageData)) {
            abort(404);
        }

        $client = User::query()->where('id', $hostedPageData['client_id'] ?? 0)->first();
        if (! $client || ! $client->isActive()) {
            abort(404);
        }

        $config = is_array($hostedPageData['public_config'] ?? null) ? $hostedPageData['public_config'] : [];

        return response()
            ->view('hosted.chat-page', [
                'slug' => $hostedPageData['slug'],
                'title' => $config['title'] ?? ($client->company_name ?: 'AI Assistant'),
                'welcomeMessage' => $config['welcome_message'] ?? 'Hello, how can we help you today?',
                'logoUrl' => $config['logo_url'] ?? null,
                'brandPrimary' => $config['branding']['primary'] ?? '#0f766e',
                'brandBg' => $config['branding']['bg'] ?? '#f8fafc',
                'brandFont' => $config['branding']['font'] ?? 'system-ui, sans-serif',
                'ogDescription' => $config['og_description'] ?? 'Chat with our AI assistant instantly.',
            ])
            ->header('Cache-Control', 'public, max-age=300');
    }
}
