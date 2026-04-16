<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\View\View;

class EmbedScriptController extends Controller
{
    public function index(): View
    {
        $clients = User::where('role', 'client')
            ->where('status', 'active')
            ->orderBy('name')
            ->get()
            ->map(function ($client) {
                $client->embed_script = $this->generateEmbedScript($client);

                return $client;
            });

        return view('admin.embed-scripts', compact('clients'));
    }

    public function show(string $id): View
    {
        $client = User::where('role', 'client')->findOrFail($id);

        $embedScript = $this->generateEmbedScript($client);

        return view('admin.embed-scripts', [
            'client' => $client,
            'embedScript' => $embedScript,
            'clients' => User::where('role', 'client')
                ->where('status', 'active')
                ->orderBy('name')
                ->get()
                ->map(function ($c) {
                    $c->embed_script = $this->generateEmbedScript($c);

                    return $c;
                }),
        ]);
    }

    private function generateEmbedScript(User $client): string
    {
        return '<script src="'.config('app.url').'/widget.js" data-token="'.$client->api_token.'" defer></script>';
    }
}
