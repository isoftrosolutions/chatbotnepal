@extends('layouts.admin')
@section('title', 'Settings')
@section('header', 'Platform Settings')

@section('content')
<form action="{{ route('admin.settings.update') }}" method="POST" class="max-w-4xl">
    @csrf @method('PUT')

    <div class="space-y-8">
        <!-- Groq AI Settings -->
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-8 border-b border-gray-50 flex items-center gap-4">
                <div class="w-12 h-12 bg-[#F4F7FE] rounded-2xl flex items-center justify-center">
                    <i data-lucide="cpu" class="text-[#4318FF] w-6 h-6"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-[#1B1B38]">Groq AI Configuration</h3>
                    <p class="text-sm text-gray-400">Manage your Groq API connection and model behaviour</p>
                </div>
            </div>
            <div class="p-8 grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2">API Key</label>
                    <input type="password" name="grok_api_key" value="{{ old('grok_api_key', $settings['grok_api_key']) }}"
                           class="w-full bg-[#F4F7FE] border-none rounded-xl px-4 py-3 text-sm text-[#1B1B38] focus:ring-2 focus:ring-[#4318FF]/20 transition-all"
                           placeholder="gsk_...">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2">Model Version</label>
                    <input type="text" name="grok_model" value="{{ old('grok_model', $settings['grok_model']) }}"
                           class="w-full bg-[#F4F7FE] border-none rounded-xl px-4 py-3 text-sm text-[#1B1B38] focus:ring-2 focus:ring-[#4318FF]/20 transition-all">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2">API Endpoint URL</label>
                    <input type="url" name="groq_api_url" value="{{ old('groq_api_url', $settings['groq_api_url']) }}"
                           class="w-full bg-[#F4F7FE] border-none rounded-xl px-4 py-3 text-sm text-[#1B1B38] focus:ring-2 focus:ring-[#4318FF]/20 transition-all font-mono text-xs">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2">Max Response Tokens</label>
                    <input type="number" name="grok_max_tokens" value="{{ old('grok_max_tokens', $settings['grok_max_tokens']) }}"
                           class="w-full bg-[#F4F7FE] border-none rounded-xl px-4 py-3 text-sm text-[#1B1B38] focus:ring-2 focus:ring-[#4318FF]/20 transition-all">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2">Temperature (Creativity)</label>
                    <input type="number" step="0.1" name="grok_temperature" value="{{ old('grok_temperature', $settings['grok_temperature']) }}"
                           class="w-full bg-[#F4F7FE] border-none rounded-xl px-4 py-3 text-sm text-[#1B1B38] focus:ring-2 focus:ring-[#4318FF]/20 transition-all">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2">Global System Prompt</label>
                    <p class="text-[10px] text-[#4318FF] font-bold mb-2 uppercase tracking-wide">Tip: Use {business_name} as a dynamic placeholder</p>
                    <textarea name="grok_system_prompt" rows="4"
                              class="w-full bg-[#F4F7FE] border-none rounded-2xl px-4 py-3 text-sm text-[#1B1B38] focus:ring-2 focus:ring-[#4318FF]/20 transition-all font-mono">{{ old('grok_system_prompt', $settings['grok_system_prompt']) }}</textarea>
                </div>
            </div>
        </div>

        <!-- Platform & Payments Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- Platform Settings -->
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-8 border-b border-gray-50 flex items-center gap-4">
                    <div class="w-12 h-12 bg-[#E2FFF3] rounded-2xl flex items-center justify-center">
                        <i data-lucide="globe" class="text-[#05CD99] w-6 h-6"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-[#1B1B38]">Identity</h3>
                    </div>
                </div>
                <div class="p-8 space-y-6">
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2">Platform Name</label>
                        <input type="text" name="platform_name" value="{{ old('platform_name', $settings['platform_name']) }}"
                               class="w-full bg-[#F4F7FE] border-none rounded-xl px-4 py-3 text-sm text-[#1B1B38] focus:ring-2 focus:ring-[#4318FF]/20 transition-all">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2">Support Email</label>
                        <input type="email" name="admin_email" value="{{ old('admin_email', $settings['admin_email']) }}"
                               class="w-full bg-[#F4F7FE] border-none rounded-xl px-4 py-3 text-sm text-[#1B1B38] focus:ring-2 focus:ring-[#4318FF]/20 transition-all">
                    </div>
                </div>
            </div>

            <!-- Payment Settings -->
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-8 border-b border-gray-50 flex items-center gap-4">
                    <div class="w-12 h-12 bg-[#FFF5E9] rounded-2xl flex items-center justify-center">
                        <i data-lucide="credit-card" class="text-[#FFB547] w-6 h-6"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-[#1B1B38]">Payments</h3>
                    </div>
                </div>
                <div class="p-8 space-y-6">
                    <!-- eSewa -->
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2">eSewa Merchant ID</label>
                        <input type="text" name="esewa_merchant_id" value="{{ old('esewa_merchant_id', $settings['esewa_merchant_id']) }}"
                               class="w-full bg-[#F4F7FE] border-none rounded-xl px-4 py-3 text-sm text-[#1B1B38] focus:ring-2 focus:ring-[#4318FF]/20 transition-all">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2">eSewa Environment</label>
                        <div class="flex gap-4">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="esewa_env" value="test"
                                       {{ old('esewa_env', $settings['esewa_env']) === 'test' ? 'checked' : '' }}
                                       class="text-[#4318FF] focus:ring-[#4318FF]">
                                <span class="text-sm font-medium text-gray-600">Test / UAT</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="esewa_env" value="live"
                                       {{ old('esewa_env', $settings['esewa_env']) === 'live' ? 'checked' : '' }}
                                       class="text-[#4318FF] focus:ring-[#4318FF]">
                                <span class="text-sm font-medium text-gray-600">Live / Production</span>
                            </label>
                        </div>
                    </div>
                    <!-- Khalti -->
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2">Khalti Secret Key</label>
                        <input type="password" name="khalti_secret_key" value="{{ old('khalti_secret_key', $settings['khalti_secret_key']) }}"
                               class="w-full bg-[#F4F7FE] border-none rounded-xl px-4 py-3 text-sm text-[#1B1B38] focus:ring-2 focus:ring-[#4318FF]/20 transition-all">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2">Khalti Verify URL</label>
                        <input type="url" name="khalti_verify_url" value="{{ old('khalti_verify_url', $settings['khalti_verify_url']) }}"
                               class="w-full bg-[#F4F7FE] border-none rounded-xl px-4 py-3 text-sm text-[#1B1B38] focus:ring-2 focus:ring-[#4318FF]/20 transition-all font-mono text-xs">
                    </div>
                </div>
            </div>
        </div>

        <!-- Billing Control -->
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-8 border-b border-gray-50 flex items-center gap-4">
                <div class="w-12 h-12 bg-[#FEECEC] rounded-2xl flex items-center justify-center">
                    <i data-lucide="bell-ring" class="text-[#EE5D50] w-6 h-6"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-[#1B1B38]">Billing & Enforcement</h3>
                    <p class="text-sm text-gray-400">Configure automated subscription management and reminders</p>
                </div>
            </div>
            <div class="p-8 grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2">Reminder Frequency (Days before due)</label>
                    <input type="number" name="billing_reminder_days" value="{{ old('billing_reminder_days', $settings['billing_reminder_days']) }}"
                           class="w-full bg-[#F4F7FE] border-none rounded-xl px-4 py-3 text-sm text-[#1B1B38] focus:ring-2 focus:ring-[#4318FF]/20 transition-all">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2">Auto-suspend Grace Period (Days after due)</label>
                    <input type="number" name="auto_disable_after_days" value="{{ old('auto_disable_after_days', $settings['auto_disable_after_days']) }}"
                           class="w-full bg-[#F4F7FE] border-none rounded-xl px-4 py-3 text-sm text-[#1B1B38] focus:ring-2 focus:ring-[#4318FF]/20 transition-all">
                </div>
            </div>
        </div>

        <!-- Pricing -->
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-8 border-b border-gray-50 flex items-center gap-4">
                <div class="w-12 h-12 bg-[#F4F7FE] rounded-2xl flex items-center justify-center">
                    <i data-lucide="tag" class="text-[#4318FF] w-6 h-6"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-[#1B1B38]">Plan Pricing (NPR / month)</h3>
                    <p class="text-sm text-gray-400">Monthly subscription amounts charged to clients per plan</p>
                </div>
            </div>
            <div class="p-8 grid grid-cols-2 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2">Starter</label>
                    <input type="number" name="plan_price_starter" value="{{ old('plan_price_starter', $settings['plan_price_starter']) }}"
                           class="w-full bg-[#F4F7FE] border-none rounded-xl px-4 py-3 text-sm text-[#1B1B38] focus:ring-2 focus:ring-[#4318FF]/20 transition-all">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2">Basic</label>
                    <input type="number" name="plan_price_basic" value="{{ old('plan_price_basic', $settings['plan_price_basic']) }}"
                           class="w-full bg-[#F4F7FE] border-none rounded-xl px-4 py-3 text-sm text-[#1B1B38] focus:ring-2 focus:ring-[#4318FF]/20 transition-all">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2">Standard</label>
                    <input type="number" name="plan_price_standard" value="{{ old('plan_price_standard', $settings['plan_price_standard']) }}"
                           class="w-full bg-[#F4F7FE] border-none rounded-xl px-4 py-3 text-sm text-[#1B1B38] focus:ring-2 focus:ring-[#4318FF]/20 transition-all">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2">Growth</label>
                    <input type="number" name="plan_price_growth" value="{{ old('plan_price_growth', $settings['plan_price_growth']) }}"
                           class="w-full bg-[#F4F7FE] border-none rounded-xl px-4 py-3 text-sm text-[#1B1B38] focus:ring-2 focus:ring-[#4318FF]/20 transition-all">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2">Pro</label>
                    <input type="number" name="plan_price_pro" value="{{ old('plan_price_pro', $settings['plan_price_pro']) }}"
                           class="w-full bg-[#F4F7FE] border-none rounded-xl px-4 py-3 text-sm text-[#1B1B38] focus:ring-2 focus:ring-[#4318FF]/20 transition-all">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2">Enterprise</label>
                    <input type="number" name="plan_price_enterprise" value="{{ old('plan_price_enterprise', $settings['plan_price_enterprise']) }}"
                           class="w-full bg-[#F4F7FE] border-none rounded-xl px-4 py-3 text-sm text-[#1B1B38] focus:ring-2 focus:ring-[#4318FF]/20 transition-all">
                </div>
            </div>
            <div class="px-8 pb-8">
                <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2">Cost Per Token (NPR)</label>
                <input type="number" step="0.000001" name="cost_per_token" value="{{ old('cost_per_token', $settings['cost_per_token']) }}"
                       class="w-full max-w-xs bg-[#F4F7FE] border-none rounded-xl px-4 py-3 text-sm text-[#1B1B38] focus:ring-2 focus:ring-[#4318FF]/20 transition-all">
                <p class="text-xs text-gray-400 mt-1">Used to estimate AI cost shown to clients</p>
            </div>
        </div>

        <!-- SMTP / Mail Settings -->
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-8 border-b border-gray-50 flex items-center gap-4">
                <div class="w-12 h-12 bg-[#F4F7FE] rounded-2xl flex items-center justify-center">
                    <i data-lucide="mail" class="text-[#4318FF] w-6 h-6"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-[#1B1B38]">Email / SMTP</h3>
                    <p class="text-sm text-gray-400">Outgoing mail settings for invoices and notifications</p>
                </div>
            </div>
            <div class="p-8 grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2">SMTP Host</label>
                    <input type="text" name="smtp_host" value="{{ old('smtp_host', $settings['smtp_host']) }}"
                           placeholder="smtp.gmail.com"
                           class="w-full bg-[#F4F7FE] border-none rounded-xl px-4 py-3 text-sm text-[#1B1B38] focus:ring-2 focus:ring-[#4318FF]/20 transition-all">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2">SMTP Port</label>
                    <input type="number" name="smtp_port" value="{{ old('smtp_port', $settings['smtp_port']) }}"
                           class="w-full bg-[#F4F7FE] border-none rounded-xl px-4 py-3 text-sm text-[#1B1B38] focus:ring-2 focus:ring-[#4318FF]/20 transition-all">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2">Encryption</label>
                    <select name="smtp_encryption"
                            class="w-full bg-[#F4F7FE] border-none rounded-xl px-4 py-3 text-sm text-[#1B1B38] focus:ring-2 focus:ring-[#4318FF]/20 transition-all">
                        <option value="tls"  {{ old('smtp_encryption', $settings['smtp_encryption']) === 'tls'  ? 'selected' : '' }}>TLS (587)</option>
                        <option value="ssl"  {{ old('smtp_encryption', $settings['smtp_encryption']) === 'ssl'  ? 'selected' : '' }}>SSL (465)</option>
                        <option value="none" {{ old('smtp_encryption', $settings['smtp_encryption']) === 'none' ? 'selected' : '' }}>None</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2">Username</label>
                    <input type="text" name="smtp_username" value="{{ old('smtp_username', $settings['smtp_username']) }}"
                           class="w-full bg-[#F4F7FE] border-none rounded-xl px-4 py-3 text-sm text-[#1B1B38] focus:ring-2 focus:ring-[#4318FF]/20 transition-all">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2">Password</label>
                    <input type="password" name="smtp_password" value="{{ old('smtp_password', $settings['smtp_password']) }}"
                           class="w-full bg-[#F4F7FE] border-none rounded-xl px-4 py-3 text-sm text-[#1B1B38] focus:ring-2 focus:ring-[#4318FF]/20 transition-all">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2">From Address</label>
                    <input type="email" name="mail_from_address" value="{{ old('mail_from_address', $settings['mail_from_address']) }}"
                           placeholder="noreply@example.com"
                           class="w-full bg-[#F4F7FE] border-none rounded-xl px-4 py-3 text-sm text-[#1B1B38] focus:ring-2 focus:ring-[#4318FF]/20 transition-all">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2">From Name</label>
                    <input type="text" name="mail_from_name" value="{{ old('mail_from_name', $settings['mail_from_name']) }}"
                           class="w-full bg-[#F4F7FE] border-none rounded-xl px-4 py-3 text-sm text-[#1B1B38] focus:ring-2 focus:ring-[#4318FF]/20 transition-all">
                </div>
            </div>
        </div>

        <!-- Submit -->
        <div class="flex items-center justify-end pb-12">
            <button type="submit" class="bg-[#4318FF] text-white px-10 py-4 rounded-2xl text-sm font-bold shadow-[0_10px_20px_-5px_rgba(67,24,255,0.4)] hover:scale-[1.02] active:scale-[0.98] transition-all flex items-center gap-2">
                <i data-lucide="save" class="w-5 h-5"></i>
                Save Configuration
            </button>
        </div>
    </div>
</form>
@endsection
