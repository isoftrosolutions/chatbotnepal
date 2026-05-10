<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\HostedPage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class HostedPageController extends Controller
{
    public function index(Request $request): View
    {
        $pages = HostedPage::where('client_id', $request->user()->id)->latest()->get();

        return view('client.hosted-pages.index', compact('pages'));
    }

    public function create(): View
    {
        return view('client.hosted-pages.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'slug' => 'required|string|max:120|unique:hosted_pages,slug',
            'title' => 'required|string|max:120',
            'welcome_message' => 'required|string|max:500',
            'logo_url' => 'nullable|url|max:500',
            'brand_primary' => 'nullable|string|max:20',
            'brand_bg' => 'nullable|string|max:20',
            'brand_font' => 'nullable|string|max:120',
        ]);

        HostedPage::create([
            'client_id' => $request->user()->id,
            'slug' => Str::slug($data['slug']),
            'status' => 'active',
            'public_config' => [
                'title' => $data['title'],
                'welcome_message' => $data['welcome_message'],
                'logo_url' => $data['logo_url'] ?? null,
                'branding' => [
                    'primary' => $data['brand_primary'] ?? '#0f766e',
                    'bg' => $data['brand_bg'] ?? '#f8fafc',
                    'font' => $data['brand_font'] ?? 'system-ui, sans-serif',
                ],
            ],
            'behavior_config' => [
                'lead_capture' => ['enabled' => true, 'trigger_depth' => 6],
                'rate_limit' => ['per_hour' => 30],
            ],
        ]);

        return redirect()->route('client.hosted-pages.index')->with('status', 'Hosted page created.');
    }

    public function edit(Request $request, HostedPage $hostedPage): View
    {
        abort_if($hostedPage->client_id !== $request->user()->id, 404);

        return view('client.hosted-pages.edit', compact('hostedPage'));
    }

    public function update(Request $request, HostedPage $hostedPage): RedirectResponse
    {
        abort_if($hostedPage->client_id !== $request->user()->id, 404);

        $data = $request->validate([
            'status' => 'required|in:active,disabled',
            'title' => 'required|string|max:120',
            'welcome_message' => 'required|string|max:500',
            'logo_url' => 'nullable|url|max:500',
            'brand_primary' => 'nullable|string|max:20',
            'brand_bg' => 'nullable|string|max:20',
            'brand_font' => 'nullable|string|max:120',
        ]);

        $hostedPage->update([
            'status' => $data['status'],
            'public_config' => [
                'title' => $data['title'],
                'welcome_message' => $data['welcome_message'],
                'logo_url' => $data['logo_url'] ?? null,
                'branding' => [
                    'primary' => $data['brand_primary'] ?? '#0f766e',
                    'bg' => $data['brand_bg'] ?? '#f8fafc',
                    'font' => $data['brand_font'] ?? 'system-ui, sans-serif',
                ],
            ],
        ]);

        return redirect()->route('client.hosted-pages.index')->with('status', 'Hosted page updated.');
    }
}
