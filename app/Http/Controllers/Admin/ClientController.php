<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\WidgetConfig;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ClientController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::where('role', 'client')->with('widgetConfig');

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('company_name', 'like', "%{$search}%");
            });
        }

        $clients = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        // Real counts from DB — not from the paginated collection
        $clientStats = [
            'total'    => User::where('role', 'client')->count(),
            'active'   => User::where('role', 'client')->where('status', 'active')->count(),
            'enabled'  => User::where('role', 'client')->where('chatbot_enabled', true)->count(),
            'inactive' => User::where('role', 'client')->where('status', '!=', 'active')->count(),
        ];

        return view('admin.clients.index', compact('clients', 'clientStats'));
    }

    public function create(): View
    {
        return view('admin.clients.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'             => 'required|string|max:255',
            'email'            => 'required|email|unique:users,email',
            'phone'            => 'nullable|string|max:20',
            'password'         => 'required|min:8',
            'company_name'     => 'nullable|string|max:255',
            'website_url'      => 'nullable|url|max:500',
            'plan'             => 'required|in:basic,standard,growth,pro',
            'status'           => 'required|in:active,inactive,suspended',
            'prechat_enabled'  => 'boolean',
        ]);

        $client = User::create([
            ...collect($validated)->except('prechat_enabled')->all(),
            'role'             => 'client',
            'api_token'        => Str::random(64),
            'chatbot_enabled'  => true,
        ]);

        WidgetConfig::create([
            'user_id'         => $client->id,
            ...WidgetConfig::getDefaultConfig(),
            'prechat_enabled' => $request->boolean('prechat_enabled'),
        ]);

        return redirect()->route('admin.clients.index')
            ->with('success', 'Client created successfully');
    }

    public function edit(int $id): View
    {
        $client = User::where('role', 'client')->with('widgetConfig')->findOrFail($id);

        return view('admin.clients.edit', compact('client'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $client = User::where('role', 'client')->findOrFail($id);

        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'email'           => 'required|email|unique:users,email,'.$id,
            'phone'           => 'nullable|string|max:20',
            'password'        => 'nullable|min:8',
            'company_name'    => 'nullable|string|max:255',
            'website_url'     => 'nullable|url|max:500',
            'plan'            => 'required|in:basic,standard,growth,pro',
            'status'          => 'required|in:active,inactive,suspended',
            'prechat_enabled' => 'boolean',
            'welcome_buttons' => 'nullable|string',
        ]);

        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        $client->update(collect($validated)->except(['prechat_enabled', 'welcome_buttons'])->all());

        // Parse welcome_buttons JSON — silently ignore malformed input
        $welcomeButtonsRaw = $request->input('welcome_buttons', '');
        $welcomeButtons    = [];
        if ($welcomeButtonsRaw) {
            $decoded = json_decode($welcomeButtonsRaw, true);
            if (is_array($decoded)) {
                $welcomeButtons = $decoded;
            }
        }

        WidgetConfig::updateOrCreate(
            ['user_id' => $client->id],
            [
                'prechat_enabled' => $request->boolean('prechat_enabled'),
                'welcome_buttons' => $welcomeButtons ?: null,
            ]
        );

        return redirect()->route('admin.clients.index')
            ->with('success', 'Client updated successfully');
    }

    public function destroy(int $id): RedirectResponse
    {
        $client = User::where('role', 'client')->findOrFail($id);
        $client->delete();

        return redirect()->route('admin.clients.index')
            ->with('success', 'Client deleted successfully');
    }

    public function toggle(int $id): RedirectResponse
    {
        $client = User::where('role', 'client')->findOrFail($id);
        $client->update(['chatbot_enabled' => ! $client->chatbot_enabled]);

        $status = $client->chatbot_enabled ? 'enabled' : 'disabled';

        return redirect()->back()
            ->with('success', "Chatbot {$status} successfully");
    }
}
