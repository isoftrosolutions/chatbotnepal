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
    public function index(): View
    {
        $clients = User::where('role', 'client')
            ->with('widgetConfig')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.clients.index', compact('clients'));
    }

    public function create(): View
    {
        return view('admin.clients.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|min:8',
            'company_name' => 'nullable|string|max:255',
            'website_url' => 'nullable|url|max:500',
            'plan' => 'required|in:basic,standard,growth,pro',
            'status' => 'required|in:active,inactive,suspended',
        ]);

        $client = User::create([
            ...$validated,
            'role' => 'client',
            'api_token' => Str::random(64),
            'chatbot_enabled' => true,
        ]);

        WidgetConfig::create([
            'user_id' => $client->id,
            ...WidgetConfig::getDefaultConfig(),
        ]);

        return redirect()->route('admin.clients.index')
            ->with('success', 'Client created successfully');
    }

    public function edit(int $id): View
    {
        $client = User::where('role', 'client')->findOrFail($id);

        return view('admin.clients.edit', compact('client'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $client = User::where('role', 'client')->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$id,
            'phone' => 'nullable|string|max:20',
            'password' => 'nullable|min:8',
            'company_name' => 'nullable|string|max:255',
            'website_url' => 'nullable|url|max:500',
            'plan' => 'required|in:basic,standard,growth,pro',
            'status' => 'required|in:active,inactive,suspended',
        ]);

        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        $client->update($validated);

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
