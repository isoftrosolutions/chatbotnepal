@extends('layouts.client')
@section('title', 'My Profile')
@section('header', 'My Profile')

@section('content')
<div class="max-w-4xl space-y-8">

  {{-- Account info --}}
  <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8">
    <div class="flex items-center gap-3 mb-6">
      <div class="w-10 h-10 bg-indigo-100 rounded-xl flex items-center justify-center">
        <i data-lucide="user" class="w-5 h-5 text-indigo-600"></i>
      </div>
      <div>
        <h3 class="font-bold text-gray-900">Account Information</h3>
        <p class="text-sm text-gray-500">Update your name, email, and contact details</p>
      </div>
    </div>

    @if($errors->hasBag('default') || $errors->any())
      <div class="mb-4 p-3 bg-red-50 border border-red-100 rounded-xl text-red-600 text-sm flex items-center gap-2">
        <i data-lucide="alert-circle" class="w-4 h-4 flex-shrink-0"></i>
        {{ $errors->first() }}
      </div>
    @endif

    <form action="{{ route('profile.update') }}" method="POST">
      @csrf @method('PATCH')
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

        <div>
          <label class="block text-xs font-bold uppercase tracking-widest text-gray-600 mb-1">Name</label>
          <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                  class="w-full px-4 py-3 border border-gray-200 rounded-2xl focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 outline-none transition-all {{ $errors->has('name') ? 'border-red-400' : 'border-gray-200' }}"/>
          @error('name')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
          <label class="block text-xs font-bold uppercase tracking-widest text-gray-600 mb-1">
            Email
            <span class="ml-1 text-indigo-500 font-normal normal-case tracking-normal">(change requires OTP)</span>
          </label>
          <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                  class="w-full px-4 py-3 border border-gray-200 rounded-2xl focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 outline-none transition-all {{ $errors->has('email') ? 'border-red-400' : 'border-gray-200' }}"/>
          @error('email')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
          <label class="block text-xs font-bold uppercase tracking-widest text-gray-600 mb-1">Phone</label>
          <input type="text" name="phone" value="{{ old('phone', $user->phone) }}"
                  class="w-full px-4 py-3 border border-gray-200 rounded-2xl focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 outline-none transition-all"/>
        </div>

        <div>
          <label class="block text-xs font-bold uppercase tracking-widest text-gray-600 mb-1">Company Name</label>
          <input type="text" name="company_name" value="{{ old('company_name', $user->company_name) }}"
                  class="w-full px-4 py-3 border border-gray-200 rounded-2xl focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 outline-none transition-all"/>
        </div>

        <div class="md:col-span-2">
          <label class="block text-xs font-bold uppercase tracking-widest text-gray-600 mb-1">Website URL</label>
          <input type="url" name="website_url" value="{{ old('website_url', $user->website_url) }}"
                  class="w-full px-4 py-3 border border-gray-200 rounded-2xl focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 outline-none transition-all"/>
        </div>
      </div>

      <div class="mt-6 flex items-center justify-between">
        <button type="submit" class="px-6 py-2.5 bg-indigo-600 text-white rounded-xl font-semibold hover:bg-indigo-700 transition">
          Save Changes
        </button>
        <div class="text-xs text-gray-400 space-y-0.5 text-right">
          <p>Role: <span class="font-semibold text-gray-600 capitalize">{{ $user->role }}</span></p>
          <p>Member since: <span class="font-semibold text-gray-600">{{ $user->created_at->format('M j, Y') }}</span></p>
          @if($user->last_login_at)
            <p>Last login: <span class="font-semibold text-gray-600">{{ $user->last_login_at->diffForHumans() }}</span></p>
          @endif
        </div>
      </div>
    </form>
  </div>

  {{-- Change password link --}}
  <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8 flex items-center justify-between">
    <div class="flex items-center gap-3">
      <div class="w-10 h-10 bg-amber-100 rounded-xl flex items-center justify-center">
        <i data-lucide="lock" class="w-5 h-5 text-amber-600"></i>
      </div>
      <div>
        <p class="font-semibold text-gray-900">Password</p>
        <p class="text-sm text-gray-500">Change your account password</p>
      </div>
    </div>
    <a href="{{ route('profile.password.edit') }}"
       class="px-5 py-2.5 border border-gray-200 rounded-xl text-sm font-semibold text-gray-700 hover:bg-gray-50 transition">
      Change Password
    </a>
  </div>

</div>
@endsection
