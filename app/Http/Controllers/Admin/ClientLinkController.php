<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClientLink;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ClientLinkController extends Controller
{
    public function index(Request $request): View
    {
        $links = ClientLink::where('user_id', auth()->id())
            ->orderBy('sort_order')
            ->get();

        return view('admin.links.index', compact('links'));
    }

    public function create(): View
    {
        return view('admin.links.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:100',
            'link'     => 'required|url|max:500',
            'slug'     => 'nullable|alpha_dash|max:100',
            'is_active' => 'boolean',
        ]);

        $slug = $validated['slug'] ?? Str::slug($validated['name']);

        // Check unique slug for this user
        $exists = ClientLink::where('user_id', auth()->id())
            ->where('slug', $slug)
            ->exists();

        if ($exists) {
            return back()->withErrors(['slug' => 'This slug is already in use.'])->withInput();
        }

        $maxOrder = ClientLink::where('user_id', auth()->id())->max('sort_order') ?? 0;

        ClientLink::create([
            'user_id'    => auth()->id(),
            'name'      => $validated['name'],
            'slug'      => $slug,
            'link'      => $validated['link'],
            'is_active' => $validated['is_active'] ?? true,
            'sort_order' => $maxOrder + 1,
        ]);

        return redirect()->route('admin.links.index')
            ->with('success', 'Link created successfully');
    }

    public function edit(int $id): View
    {
        $link = ClientLink::where('user_id', auth()->id())->findOrFail($id);

        return view('admin.links.edit', compact('link'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $link = ClientLink::where('user_id', auth()->id())->findOrFail($id);

        $validated = $request->validate([
            'name'     => 'required|string|max:100',
            'link'     => 'required|url|max:500',
            'slug'     => 'nullable|alpha_dash|max:100',
            'is_active' => 'boolean',
        ]);

        $slug = $validated['slug'] ?? Str::slug($validated['name']);

        // Check unique slug for this user (excluding current link)
        $exists = ClientLink::where('user_id', auth()->id())
            ->where('slug', $slug)
            ->where('id', '!=', $id)
            ->exists();

        if ($exists) {
            return back()->withErrors(['slug' => 'This slug is already in use.'])->withInput();
        }

        $link->update([
            'name'      => $validated['name'],
            'slug'      => $slug,
            'link'      => $validated['link'],
            'is_active' => $validated['is_active'] ?? $link->is_active,
        ]);

        return redirect()->route('admin.links.index')
            ->with('success', 'Link updated successfully');
    }

    public function destroy(int $id): RedirectResponse
    {
        $link = ClientLink::where('user_id', auth()->id())->findOrFail($id);
        $link->delete();

        return redirect()->route('admin.links.index')
            ->with('success', 'Link deleted successfully');
    }

    public function toggle(int $id): JsonResponse
    {
        $link = ClientLink::where('user_id', auth()->id())->findOrFail($id);

        $link->update(['is_active' => !$link->is_active]);

        return response()->json([
            'success'    => true,
            'is_active' => $link->is_active,
            'message'   => $link->is_active ? 'Link enabled' : 'Link disabled',
        ]);
    }

    public function reorder(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order' => 'required|array',
            'order.*' => 'integer',
        ]);

        foreach ($validated['order'] as $index => $linkId) {
            ClientLink::where('user_id', auth()->id())
                ->where('id', $linkId)
                ->update(['sort_order' => $index]);
        }

        return response()->json(['success' => true, 'message' => 'Order updated']);
    }

    public function publicIndex(string $siteId): JsonResponse
    {
        $user = User::where('site_id', $siteId)->first();

        if (!$user) {
            return response()->json(['links' => []], 404);
        }

        $links = ClientLink::where('user_id', $user->id)
            ->active()
            ->orderBy('sort_order')
            ->get(['name', 'slug', 'link']);

        return response()->json([
            'links' => $links,
        ]);
    }
}