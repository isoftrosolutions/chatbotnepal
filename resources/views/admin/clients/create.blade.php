@extends('layouts.admin')
@section('title', 'Add Client')
@section('header', 'Bot Manager')

@section('content')
<div class="max-w-4xl">
    <div class="mb-6 flex items-center gap-4">
        <a href="{{ route('admin.clients.index') }}" class="w-10 h-10 bg-white rounded-xl flex items-center justify-center text-gray-400 hover:text-[#4318FF] shadow-sm border border-gray-100 transition-all">
            <i data-lucide="chevron-left" class="w-5 h-5"></i>
        </a>
        <div>
            <h3 class="text-xl font-bold text-[#1B1B38]">Onboard New Client</h3>
            <p class="text-sm text-gray-400">Set up a new business account and chatbot profile</p>
        </div>
    </div>

    <form action="{{ route('admin.clients.store') }}" method="POST">
        @csrf
        <div class="space-y-6">
            <!-- Basic Information -->
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-8 border-b border-gray-50 flex items-center gap-4">
                    <div class="w-12 h-12 bg-[#F4F7FE] rounded-2xl flex items-center justify-center">
                        <i data-lucide="user" class="text-[#4318FF] w-6 h-6"></i>
                    </div>
                    <h3 class="text-lg font-bold text-[#1B1B38]">Basic Information</h3>
                </div>
                <div class="p-8 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2">Full Name *</label>
                        <input type="text" name="name" value="{{ old('name') }}" required 
                               class="w-full bg-[#F4F7FE] border-none rounded-xl px-4 py-3 text-sm text-[#1B1B38] focus:ring-2 focus:ring-[#4318FF]/20 transition-all" 
                               placeholder="e.g. John Doe">
                        @error('name') <span class="text-[#EE5D50] text-[10px] font-bold mt-1 uppercase tracking-wide">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2">Email Address *</label>
                        <input type="email" name="email" value="{{ old('email') }}" required 
                               class="w-full bg-[#F4F7FE] border-none rounded-xl px-4 py-3 text-sm text-[#1B1B38] focus:ring-2 focus:ring-[#4318FF]/20 transition-all" 
                               placeholder="john@example.com">
                        @error('email') <span class="text-[#EE5D50] text-[10px] font-bold mt-1 uppercase tracking-wide">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2">Phone Number</label>
                        <input type="text" name="phone" value="{{ old('phone') }}" 
                               class="w-full bg-[#F4F7FE] border-none rounded-xl px-4 py-3 text-sm text-[#1B1B38] focus:ring-2 focus:ring-[#4318FF]/20 transition-all" 
                               placeholder="+977 98XXXXXXXX">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2">Account Password *</label>
                        <input type="password" name="password" required 
                               class="w-full bg-[#F4F7FE] border-none rounded-xl px-4 py-3 text-sm text-[#1B1B38] focus:ring-2 focus:ring-[#4318FF]/20 transition-all" 
                               placeholder="••••••••">
                        @error('password') <span class="text-[#EE5D50] text-[10px] font-bold mt-1 uppercase tracking-wide">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <!-- Business Details -->
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-8 border-b border-gray-50 flex items-center gap-4">
                    <div class="w-12 h-12 bg-[#E2FFF3] rounded-2xl flex items-center justify-center">
                        <i data-lucide="briefcase" class="text-[#05CD99] w-6 h-6"></i>
                    </div>
                    <h3 class="text-lg font-bold text-[#1B1B38]">Business Details</h3>
                </div>
                <div class="p-8 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2">Company Name</label>
                        <input type="text" name="company_name" value="{{ old('company_name') }}" 
                               class="w-full bg-[#F4F7FE] border-none rounded-xl px-4 py-3 text-sm text-[#1B1B38] focus:ring-2 focus:ring-[#4318FF]/20 transition-all" 
                               placeholder="e.g. Himalayan Tech">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2">Website URL</label>
                        <input type="url" name="website_url" value="{{ old('website_url') }}" 
                               class="w-full bg-[#F4F7FE] border-none rounded-xl px-4 py-3 text-sm text-[#1B1B38] focus:ring-2 focus:ring-[#4318FF]/20 transition-all" 
                               placeholder="https://example.com">
                    </div>
                </div>
            </div>

            <!-- Subscription & Status -->
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-8 border-b border-gray-50 flex items-center gap-4">
                    <div class="w-12 h-12 bg-[#FFF5E9] rounded-2xl flex items-center justify-center">
                        <i data-lucide="zap" class="text-[#FFB547] w-6 h-6"></i>
                    </div>
                    <h3 class="text-lg font-bold text-[#1B1B38]">Plan & Status</h3>
                </div>
                <div class="p-8 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2">Subscription Plan *</label>
                        <select name="plan" required 
                                class="w-full bg-[#F4F7FE] border-none rounded-xl px-4 py-3 text-sm text-[#1B1B38] focus:ring-2 focus:ring-[#4318FF]/20 transition-all cursor-pointer">
                            <option value="starter">Starter - Rs. 1,500/mo</option>
                            <option value="growth" selected>Growth - Rs. 5,000/mo</option>
                            <option value="enterprise">Enterprise - Custom</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2">Account Status *</label>
                        <select name="status" required 
                                class="w-full bg-[#F4F7FE] border-none rounded-xl px-4 py-3 text-sm text-[#1B1B38] focus:ring-2 focus:ring-[#4318FF]/20 transition-all cursor-pointer">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="suspended">Suspended</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Chatbot Features -->
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-8 border-b border-gray-50 flex items-center gap-4">
                    <div class="w-12 h-12 bg-violet-50 rounded-2xl flex items-center justify-center">
                        <i data-lucide="settings-2" class="text-violet-600 w-6 h-6"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-[#1B1B38]">Chatbot Features</h3>
                        <p class="text-xs text-gray-400 mt-0.5">Configure which features are active for this client</p>
                    </div>
                </div>
                <div class="p-8">
                    <label class="flex items-start gap-4 cursor-pointer group">
                        <div class="relative mt-0.5 flex-shrink-0">
                            <input type="hidden" name="prechat_enabled" value="0">
                            <input type="checkbox" name="prechat_enabled" value="1" id="prechat_enabled_create"
                                   {{ old('prechat_enabled') ? 'checked' : '' }}
                                   class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-checked:bg-indigo-600 rounded-full transition-colors duration-200 peer-focus:ring-2 peer-focus:ring-indigo-300"></div>
                            <div class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform duration-200 peer-checked:translate-x-5"></div>
                        </div>
                        <div>
                            <p class="font-semibold text-[#1B1B38] text-sm">Pre-Chat Visitor Form</p>
                            <p class="text-xs text-gray-400 mt-1 leading-relaxed">Ask visitors for their name, email, and phone number before the conversation starts. All fields are optional for the visitor.</p>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-end gap-4 pb-12">
                <a href="{{ route('admin.clients.index') }}" class="px-8 py-4 rounded-2xl text-sm font-bold text-gray-500 hover:bg-gray-100 transition-all">
                    Cancel
                </a>
                <button type="submit" class="bg-[#4318FF] text-white px-10 py-4 rounded-2xl text-sm font-bold shadow-[0_10px_20px_-5px_rgba(67,24,255,0.4)] hover:scale-[1.02] active:scale-[0.98] transition-all flex items-center gap-2">
                    <i data-lucide="user-plus" class="w-5 h-5"></i>
                    Create Client Account
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
