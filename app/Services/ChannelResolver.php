<?php

namespace App\Services;

use App\Models\HostedPage;
use Illuminate\Support\Facades\Cache;

class ChannelResolver
{
    public function resolve(array $payload): array
    {
        if (! empty($payload['slug'])) {
            $slug = (string) $payload['slug'];
            $hostedPage = Cache::remember("hosted_page:slug:{$slug}", 300, function () use ($slug) {
                return HostedPage::query()
                    ->where('slug', $slug)
                    ->where('status', 'active')
                    ->first(['id', 'client_id', 'slug', 'status'])
                    ?->toArray();
            });

            if (is_array($hostedPage)) {
                return [
                    'channel' => 'hosted_page',
                    'client_id' => $hostedPage['client_id'],
                    'channel_ref' => $hostedPage['slug'],
                    'hosted_page' => $hostedPage,
                ];
            }
        }

        if (! empty($payload['widget_id'])) {
            return [
                'channel' => 'widget',
                'client_id' => null,
                'channel_ref' => (string) $payload['widget_id'],
            ];
        }

        return [
            'channel' => 'api',
            'client_id' => null,
            'channel_ref' => 'api',
        ];
    }
}
