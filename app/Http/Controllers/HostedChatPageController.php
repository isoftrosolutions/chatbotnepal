<?php

namespace App\Http\Controllers;

use App\Models\HostedPage;
use Illuminate\Support\Facades\Cache;

class HostedChatPageController extends Controller
{
    public function show(string $slug)
    {
        $hostedPage = Cache::remember("hosted_page:slug:{$slug}", 300, function () use ($slug) {
            return HostedPage::with('client')->where('slug', $slug)->where('status', 'active')->first();
        });

        if (! $hostedPage || ! $hostedPage->client || ! $hostedPage->client->isActive()) {
            abort(404);
        }

        $config = $hostedPage->public_config ?? [];

        return response()
            ->view('hosted.chat-page', [
                'slug' => $hostedPage->slug,
                'title' => $config['title'] ?? ($hostedPage->client->company_name ?: 'AI Assistant'),
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
