<?php

namespace App\Console\Commands;

use App\Models\KnowledgeBase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SyncKnowledgeBaseFiles extends Command
{
    protected $signature   = 'kb:sync';
    protected $description = 'Write all knowledge base DB entries to disk as .md files';

    public function handle(): void
    {
        $entries = KnowledgeBase::with('user')->get();

        if ($entries->isEmpty()) {
            $this->info('No knowledge base entries found.');
            return;
        }

        foreach ($entries as $kb) {
            $slug = Str::slug($kb->user->company_name ?? $kb->user->name);
            $path = 'clients/' . $kb->user_id . '_' . $slug . '/' . $kb->file_name;
            Storage::disk('local')->put($path, $kb->content);
            $this->line("  Written: storage/app/private/{$path}");
        }

        $this->info("Synced {$entries->count()} file(s) to disk.");
    }
}
