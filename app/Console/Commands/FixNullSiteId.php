<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class FixNullSiteId extends Command
{
    protected $signature = 'app:fix-null-site-id {--dry-run : Show what would be fixed without making changes}';

    protected $description = 'Generate site_id for users where site_id is NULL';

    public function handle(): int
    {
        $users = User::whereNull('site_id')->get();

        if ($users->isEmpty()) {
            $this->info('No users with NULL site_id found.');
            return Command::SUCCESS;
        }

        if ($this->option('dry-run')) {
            $this->warn('Found ' . $users->count() . ' users with NULL site_id:');
            foreach ($users as $user) {
                $newSiteId = $this->generateUniqueSiteId($user);
                $this->line("  ID: {$user->id}, Name: {$user->name}, Email: {$user->email} => site_id: {$newSiteId}");
            }
            return Command::SUCCESS;
        }

        $fixed = 0;
        foreach ($users as $user) {
            $newSiteId = $this->generateUniqueSiteId($user);
            $user->site_id = $newSiteId;
            $user->save();
            $fixed++;
            $this->line("Fixed user {$user->id}: site_id = {$newSiteId}");
        }

        $this->info("Fixed {$fixed} users with NULL site_id.");

        return Command::SUCCESS;
    }

    private function generateUniqueSiteId(User $user): string
    {
        $base = Str::slug($user->company_name ?? $user->name ?? $user->email ?? 'user');
        $base = substr($base, 0, 20);

        $siteId = $base;
        $attempt = 1;

        while (User::where('site_id', $siteId)->where('id', '!=', $user->id)->exists()) {
            $siteId = substr($base, 0, 18) . $attempt;
            $attempt++;
        }

        return $siteId;
    }
}