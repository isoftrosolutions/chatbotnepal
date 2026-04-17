<?php

namespace App\Console\Commands;

use App\Models\Setting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class AppSetup extends Command
{
    protected $signature = 'app:setup
                            {--non-interactive : Read all values from .env without prompting}
                            {--test-mail : Send a test email after configuring SMTP}';

    protected $description = 'Configure secrets (Groq API, SMTP, etc.) into the settings table';

    private array $secrets = [
        'groq' => [
            'label'    => 'Groq API Key',
            'env'      => 'GROQ_API_KEY',
            'db_key'   => 'grok_api_key',
            'secret'   => true,
            'required' => true,
        ],
        'groq_model' => [
            'label'    => 'Groq Model',
            'env'      => 'GROQ_MODEL',
            'db_key'   => 'grok_model',
            'secret'   => false,
            'required' => false,
            'default'  => 'llama-3.3-70b-versatile',
        ],
        'smtp_host' => [
            'label'    => 'SMTP Host',
            'env'      => 'MAIL_HOST',
            'db_key'   => 'mail_host',
            'secret'   => false,
            'required' => true,
            'default'  => 'smtp.gmail.com',
        ],
        'smtp_port' => [
            'label'    => 'SMTP Port',
            'env'      => 'MAIL_PORT',
            'db_key'   => 'mail_port',
            'secret'   => false,
            'required' => true,
            'default'  => '587',
        ],
        'smtp_username' => [
            'label'    => 'SMTP Username (email)',
            'env'      => 'MAIL_USERNAME',
            'db_key'   => 'mail_username',
            'secret'   => false,
            'required' => true,
        ],
        'smtp_password' => [
            'label'    => 'SMTP Password / App Password',
            'env'      => 'MAIL_PASSWORD',
            'db_key'   => 'mail_password',
            'secret'   => true,
            'required' => true,
        ],
        'smtp_from' => [
            'label'    => 'Mail From Address',
            'env'      => 'MAIL_FROM_ADDRESS',
            'db_key'   => 'mail_from_address',
            'secret'   => false,
            'required' => true,
        ],
    ];

    public function handle(): int
    {
        $this->info('');
        $this->info('  ChatBot Nepal — Setup Wizard');
        $this->info('  ─────────────────────────────────────');

        if (! $this->checkDatabase()) {
            return self::FAILURE;
        }

        $nonInteractive = $this->option('non-interactive');
        $saved = 0;

        foreach ($this->secrets as $key => $cfg) {
            $envValue = env($cfg['env']);

            if ($nonInteractive) {
                $value = $envValue;
            } else {
                $placeholder = $envValue
                    ? ($cfg['secret'] ? $this->mask($envValue) : $envValue)
                    : ($cfg['default'] ?? '');

                $prompt = "  {$cfg['label']}";
                if ($placeholder) {
                    $prompt .= " [{$placeholder}]";
                }

                $value = $cfg['secret']
                    ? $this->secret($prompt) ?: $envValue
                    : $this->ask($prompt, $envValue ?? ($cfg['default'] ?? null));
            }

            if (! $value && ($cfg['required'] ?? false)) {
                $this->warn("  Skipping {$cfg['label']} — no value provided.");
                continue;
            }

            if ($value) {
                Setting::updateOrCreate(['key' => $cfg['db_key']], ['value' => $value]);
                $display = $cfg['secret'] ? $this->mask($value) : $value;
                $this->line("  <info>✓</info> {$cfg['label']}: <comment>{$display}</comment>");
                $saved++;
            }
        }

        $this->info('');
        $this->info("  {$saved} secret(s) saved to settings table.");

        if ($this->option('test-mail') || (! $nonInteractive && $this->confirm('  Send a test email now?', true))) {
            $this->testMail();
        }

        Artisan::call('config:clear');
        $this->info('  Config cache cleared.');
        $this->info('');

        return self::SUCCESS;
    }

    private function checkDatabase(): bool
    {
        try {
            DB::connection()->getPdo();

            return true;
        } catch (\Exception $e) {
            $this->error('  Database connection failed: '.$e->getMessage());

            return false;
        }
    }

    private function testMail(): void
    {
        $this->line('  Sending test email...');
        try {
            $mailFrom  = Setting::get('mail_username', env('MAIL_FROM_ADDRESS'));
            $mailHost  = Setting::get('mail_host', env('MAIL_HOST'));
            $mailPort  = Setting::get('mail_port', env('MAIL_PORT'));
            $mailUser  = Setting::get('mail_username', env('MAIL_USERNAME'));
            $mailPass  = Setting::get('mail_password', env('MAIL_PASSWORD'));

            config([
                'mail.mailers.smtp.host'       => $mailHost,
                'mail.mailers.smtp.port'       => $mailPort,
                'mail.mailers.smtp.username'   => $mailUser,
                'mail.mailers.smtp.password'   => $mailPass,
                'mail.mailers.smtp.encryption' => 'tls',
                'mail.from.address'            => $mailFrom,
            ]);

            \Illuminate\Support\Facades\Mail::raw(
                'ChatBot Nepal setup test — SMTP is working!',
                fn ($m) => $m->to($mailFrom)->subject('ChatBot Nepal — SMTP Test')
            );

            $this->info('  ✓ Test email sent to '.$mailFrom);
        } catch (\Exception $e) {
            $this->error('  Mail failed: '.$e->getMessage());
        }
    }

    private function mask(string $value): string
    {
        $len = strlen($value);
        if ($len <= 6) {
            return str_repeat('*', $len);
        }

        return substr($value, 0, 4).str_repeat('*', $len - 8).substr($value, -4);
    }
}
