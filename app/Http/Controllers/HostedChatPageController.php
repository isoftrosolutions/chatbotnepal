<?php

namespace App\Http\Controllers;

use App\Models\HostedPage;

class HostedChatPageController extends Controller
{
    public function show(string $slug)
    {
        $hostedPage = HostedPage::query()
            ->where('slug', $slug)
            ->where('status', 'active')
            ->firstOrFail();

        $client = $hostedPage->client;
        if (! $client || ! $client->isActive()) {
            abort(404);
        }

        $config = $hostedPage->public_config ?? [];

        return response()
            ->view('hosted.chat-page', [
                'hostedPage' => $hostedPage,
                'ogDescription' => $config['og_description'] ?? 'Chat with our AI assistant instantly.',
            ])
            ->header('Cache-Control', 'public, max-age=300');
    }
}
