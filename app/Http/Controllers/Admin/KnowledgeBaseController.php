<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KnowledgeBase;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class KnowledgeBaseController extends Controller
{
    // Global KB overview — all clients and their files
    public function overview(): View
    {
        $clients = User::where('role', 'client')
            ->with('knowledgeBases')
            ->orderBy('company_name')
            ->get();

        return view('admin.knowledge-base', compact('clients'));
    }

    public function index(int $clientId): View
    {
        $client = User::where('role', 'client')->findOrFail($clientId);
        $kbFiles = $client->knowledgeBases()->orderBy('sort_order')->orderBy('id')->get();
        $diskPath = $this->clientDiskPath($client);

        return view('admin.clients.knowledge-base', compact('client', 'kbFiles', 'diskPath'));
    }

    public function store(Request $request, int $clientId): RedirectResponse
    {
        $client = User::where('role', 'client')->findOrFail($clientId);

        $validated = $request->validate([
            'file_name' => 'required|string|max:255',
            'file_type' => 'required|in:about,services,faq,contact,custom',
            'content'   => 'required|string',
        ]);

        $validated['file_name'] = $this->ensureMdExtension($validated['file_name']);

        $kb = KnowledgeBase::create([
            'user_id'   => $client->id,
            'is_active' => true,
            ...$validated,
        ]);

        $this->writeToDisk($client, $kb);

        return redirect()->back()->with('success', 'Knowledge base file added successfully');
    }

    public function update(Request $request, int $clientId, int $kbId): RedirectResponse
    {
        $kb = KnowledgeBase::where('id', $kbId)->where('user_id', $clientId)->firstOrFail();
        $client = $kb->user;

        $validated = $request->validate([
            'file_name' => 'required|string|max:255',
            'file_type' => 'required|in:about,services,faq,contact,custom',
            'content'   => 'required|string',
        ]);

        $validated['file_name'] = $this->ensureMdExtension($validated['file_name']);

        // If file was renamed, delete the old file on disk
        if ($kb->file_name !== $validated['file_name']) {
            $this->deleteFromDisk($client, $kb->file_name);
        }

        $kb->update($validated);
        $this->writeToDisk($client, $kb);

        return redirect()->back()->with('success', 'Knowledge base file updated successfully');
    }

    public function destroy(int $clientId, int $kbId): RedirectResponse
    {
        $kb = KnowledgeBase::where('id', $kbId)->where('user_id', $clientId)->firstOrFail();
        $client = $kb->user;

        $this->deleteFromDisk($client, $kb->file_name);
        $kb->delete();

        return redirect()->back()->with('success', 'Knowledge base file deleted successfully');
    }

    public function reorder(Request $request, int $clientId): JsonResponse
    {
        $order = $request->input('order', []);
        foreach ($order as $i => $id) {
            KnowledgeBase::where('id', $id)->where('user_id', $clientId)->update(['sort_order' => $i]);
        }
        return response()->json(['success' => true]);
    }

    public function toggleActive(int $clientId, int $kbId): RedirectResponse
    {
        $kb = KnowledgeBase::where('id', $kbId)->where('user_id', $clientId)->firstOrFail();
        $kb->update(['is_active' => ! $kb->is_active]);

        return redirect()->back()->with('success', 'Knowledge base status updated');
    }

    // --- Disk helpers ---

    private function clientDiskPath(User $client): string
    {
        $slug = Str::slug($client->company_name ?? $client->name);
        return 'clients/' . $client->id . '_' . $slug;
    }

    private function writeToDisk(User $client, KnowledgeBase $kb): void
    {
        $path = $this->clientDiskPath($client) . '/' . $kb->file_name;
        Storage::disk('local')->put($path, $kb->content);
    }

    private function deleteFromDisk(User $client, string $fileName): void
    {
        $path = $this->clientDiskPath($client) . '/' . $fileName;
        Storage::disk('local')->delete($path);
    }

    private function ensureMdExtension(string $name): string
    {
        if (!Str::endsWith($name, ['.md', '.markdown'])) {
            return $name . '.md';
        }
        return $name;
    }
}
