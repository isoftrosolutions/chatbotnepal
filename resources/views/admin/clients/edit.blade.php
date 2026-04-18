@extends('layouts.admin')
@section('title', 'Edit Client')
@section('header', 'Edit Client')

@section('content')
<div class="max-w-4xl">
  <div class="mb-6 flex items-center gap-4">
    <a href="{{ route('admin.clients.index') }}"
       class="w-10 h-10 bg-white rounded-xl flex items-center justify-center text-gray-400 hover:text-[#4318FF] shadow-sm border border-gray-100 transition-all">
      <i data-lucide="chevron-left" class="w-5 h-5"></i>
    </a>
    <div>
      <h3 class="text-xl font-bold text-[#1B1B38]">Edit Client</h3>
      <p class="text-sm text-gray-400">{{ $client->company_name ?? $client->name }}</p>
    </div>
  </div>

  @if(session('success'))
  <div class="mb-4 bg-[#E2FFF3] border border-[#05CD99]/20 text-[#05CD99] rounded-xl px-5 py-3 text-sm font-semibold flex items-center gap-2">
    <i data-lucide="check-circle" class="w-4 h-4"></i>
    {{ session('success') }}
  </div>
  @endif

  <form action="{{ route('admin.clients.update', $client->id) }}" method="POST">
    @csrf @method('PUT')
    <div class="space-y-6">

      {{-- Basic Information --}}
      <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-50 flex items-center gap-4">
          <div class="w-12 h-12 bg-[#F4F7FE] rounded-2xl flex items-center justify-center">
            <i data-lucide="user" class="text-[#4318FF] w-6 h-6"></i>
          </div>
          <h3 class="text-lg font-bold text-[#1B1B38]">Basic Information</h3>
        </div>
        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2">Full Name *</label>
            <input type="text" name="name" value="{{ old('name', $client->name) }}" required
              class="w-full bg-[#F4F7FE] border-none rounded-xl px-4 py-3 text-sm text-[#1B1B38] focus:ring-2 focus:ring-[#4318FF]/20 transition-all outline-none">
            @error('name') <span class="text-[#EE5D50] text-[10px] font-bold mt-1 uppercase tracking-wide block">{{ $message }}</span> @enderror
          </div>
          <div>
            <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2">Email Address *</label>
            <input type="email" name="email" value="{{ old('email', $client->email) }}" required
              class="w-full bg-[#F4F7FE] border-none rounded-xl px-4 py-3 text-sm text-[#1B1B38] focus:ring-2 focus:ring-[#4318FF]/20 transition-all outline-none">
            @error('email') <span class="text-[#EE5D50] text-[10px] font-bold mt-1 uppercase tracking-wide block">{{ $message }}</span> @enderror
          </div>
          <div>
            <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2">Phone Number</label>
            <input type="text" name="phone" value="{{ old('phone', $client->phone) }}"
              class="w-full bg-[#F4F7FE] border-none rounded-xl px-4 py-3 text-sm text-[#1B1B38] focus:ring-2 focus:ring-[#4318FF]/20 transition-all outline-none"
              placeholder="+977 98XXXXXXXX">
          </div>
          <div>
            <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2">New Password <span class="normal-case text-gray-300">(leave blank to keep)</span></label>
            <input type="password" name="password"
              class="w-full bg-[#F4F7FE] border-none rounded-xl px-4 py-3 text-sm text-[#1B1B38] focus:ring-2 focus:ring-[#4318FF]/20 transition-all outline-none"
              placeholder="••••••••">
            @error('password') <span class="text-[#EE5D50] text-[10px] font-bold mt-1 uppercase tracking-wide block">{{ $message }}</span> @enderror
          </div>
        </div>
      </div>

      {{-- Business Details --}}
      <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-50 flex items-center gap-4">
          <div class="w-12 h-12 bg-[#E2FFF3] rounded-2xl flex items-center justify-center">
            <i data-lucide="briefcase" class="text-[#05CD99] w-6 h-6"></i>
          </div>
          <h3 class="text-lg font-bold text-[#1B1B38]">Business Details</h3>
        </div>
        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2">Company Name</label>
            <input type="text" name="company_name" value="{{ old('company_name', $client->company_name) }}"
              class="w-full bg-[#F4F7FE] border-none rounded-xl px-4 py-3 text-sm text-[#1B1B38] focus:ring-2 focus:ring-[#4318FF]/20 transition-all outline-none"
              placeholder="e.g. Himalayan Tech">
          </div>
          <div>
            <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2">Website URL</label>
            <input type="url" name="website_url" value="{{ old('website_url', $client->website_url) }}"
              class="w-full bg-[#F4F7FE] border-none rounded-xl px-4 py-3 text-sm text-[#1B1B38] focus:ring-2 focus:ring-[#4318FF]/20 transition-all outline-none"
              placeholder="https://example.com">
          </div>
        </div>
      </div>

      {{-- Plan & Status --}}
      <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-50 flex items-center gap-4">
          <div class="w-12 h-12 bg-[#FFF5E9] rounded-2xl flex items-center justify-center">
            <i data-lucide="zap" class="text-[#FFB547] w-6 h-6"></i>
          </div>
          <h3 class="text-lg font-bold text-[#1B1B38]">Plan & Status</h3>
        </div>
        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2">Subscription Plan *</label>
            <select name="plan" required
              class="w-full bg-[#F4F7FE] border-none rounded-xl px-4 py-3 text-sm text-[#1B1B38] focus:ring-2 focus:ring-[#4318FF]/20 transition-all cursor-pointer outline-none">
              <option value="starter"    {{ $client->plan === 'starter'    ? 'selected' : '' }}>Starter — Rs. 999/mo</option>
              <option value="growth"     {{ $client->plan === 'growth'     ? 'selected' : '' }}>Growth — Rs. 3,000/mo</option>
              <option value="enterprise" {{ $client->plan === 'enterprise' ? 'selected' : '' }}>Enterprise — Custom</option>
            </select>
          </div>
          <div>
            <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2">Account Status *</label>
            <select name="status" required
              class="w-full bg-[#F4F7FE] border-none rounded-xl px-4 py-3 text-sm text-[#1B1B38] focus:ring-2 focus:ring-[#4318FF]/20 transition-all cursor-pointer outline-none">
              <option value="active"    {{ $client->status === 'active'    ? 'selected' : '' }}>Active</option>
              <option value="inactive"  {{ $client->status === 'inactive'  ? 'selected' : '' }}>Inactive</option>
              <option value="suspended" {{ $client->status === 'suspended' ? 'selected' : '' }}>Suspended</option>
            </select>
          </div>
        </div>
      </div>

      {{-- API Token (read-only) --}}
      <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-50 flex items-center gap-4">
          <div class="w-12 h-12 bg-[#F4F7FE] rounded-2xl flex items-center justify-center">
            <i data-lucide="key" class="text-[#4318FF] w-6 h-6"></i>
          </div>
          <h3 class="text-lg font-bold text-[#1B1B38]">API Credentials</h3>
        </div>
        <div class="p-6">
          <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2">API Token <span class="normal-case text-gray-300">(read-only)</span></label>
          <div class="flex items-center gap-3">
            <code class="flex-1 bg-[#F4F7FE] rounded-xl px-4 py-3 text-sm text-[#4318FF] font-mono break-all">{{ $client->api_token }}</code>
            <button type="button" onclick="navigator.clipboard.writeText('{{ $client->api_token }}'); this.innerHTML='<i data-lucide=\'check\' class=\'w-4 h-4\'></i>'; lucide.createIcons(); setTimeout(()=>{this.innerHTML='<i data-lucide=\'copy\' class=\'w-4 h-4\'></i>'; lucide.createIcons();}, 1500)"
              class="p-3 text-gray-400 hover:text-[#4318FF] hover:bg-[#4318FF]/5 rounded-xl transition-all" title="Copy token">
              <i data-lucide="copy" class="w-4 h-4"></i>
            </button>
          </div>
        </div>
      </div>

      {{-- Actions --}}
      <div class="flex items-center justify-end gap-4 pb-10">
        <a href="{{ route('admin.clients.index') }}"
           class="px-8 py-4 rounded-2xl text-sm font-bold text-gray-500 hover:bg-gray-100 transition-all">
          Cancel
        </a>
        <button type="submit"
           class="bg-[#4318FF] text-white px-10 py-4 rounded-2xl text-sm font-bold shadow-[0_10px_20px_-5px_rgba(67,24,255,0.4)] hover:scale-[1.02] active:scale-[0.98] transition-all flex items-center gap-2">
          <i data-lucide="save" class="w-5 h-5"></i>
          Save Changes
        </button>
      </div>
    </div>
  </form>
</div>
@endsection
