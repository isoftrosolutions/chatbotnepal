@extends('layouts.client')
@section('title', 'Change Password')
@section('header', 'Change Password')

@section('content')
<div class="max-w-lg">
  <div class="bg-white rounded-2xl shadow p-8">

    <div class="flex items-center gap-3 mb-6">
      <div class="w-10 h-10 bg-amber-100 rounded-xl flex items-center justify-center">
        <i data-lucide="lock" class="w-5 h-5 text-amber-600"></i>
      </div>
      <div>
        <h3 class="font-bold text-gray-900">Change your password</h3>
        <p class="text-sm text-gray-500">All other sessions will be signed out on success</p>
      </div>
    </div>

    @if($errors->any())
      <div class="mb-4 p-3 bg-red-50 border border-red-100 rounded-xl text-red-600 text-sm flex items-center gap-2">
        <i data-lucide="alert-circle" class="w-4 h-4 flex-shrink-0"></i>
        {{ $errors->first() }}
      </div>
    @endif

    <form method="POST" action="{{ route('profile.password.update') }}">
      @csrf @method('PATCH')

      <div class="mb-4">
        <label class="block text-xs font-bold uppercase tracking-widest text-gray-600 mb-1">Current Password</label>
        <input type="password" name="current_password" required autocomplete="current-password"
               class="w-full px-4 py-2.5 border rounded-xl focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 outline-none transition {{ $errors->has('current_password') ? 'border-red-400' : 'border-gray-200' }}"/>
        @error('current_password')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
      </div>

      <div class="mb-4">
        <label class="block text-xs font-bold uppercase tracking-widest text-gray-600 mb-1">New Password</label>
        <input type="password" name="password" required autocomplete="new-password"
               class="w-full px-4 py-2.5 border rounded-xl focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 outline-none transition {{ $errors->has('password') ? 'border-red-400' : 'border-gray-200' }}"/>
        @error('password')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
        <p class="text-xs text-gray-400 mt-1">Min 12 characters · uppercase &amp; lowercase · number · symbol</p>
      </div>

      <div class="mb-6">
        <label class="block text-xs font-bold uppercase tracking-widest text-gray-600 mb-1">Confirm New Password</label>
        <input type="password" name="password_confirmation" required autocomplete="new-password"
               class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 outline-none transition"/>
      </div>

      <div class="flex items-center gap-4">
        <button type="submit" class="px-6 py-2.5 bg-indigo-600 text-white rounded-xl font-semibold hover:bg-indigo-700 transition">
          Update Password
        </button>
        <a href="{{ route('profile.show') }}" class="text-sm text-gray-500 hover:text-gray-700 transition">Cancel</a>
      </div>
    </form>

  </div>
</div>
@endsection
