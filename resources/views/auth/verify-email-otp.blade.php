@extends('layouts.client')
@section('title', 'Verify Email Change')
@section('header', 'Verify New Email')

@section('content')
<div class="max-w-lg">
  <div class="bg-white rounded-2xl shadow p-8">

    <div class="flex items-center gap-3 mb-6">
      <div class="w-10 h-10 bg-indigo-100 rounded-xl flex items-center justify-center">
        <i data-lucide="mail-check" class="w-5 h-5 text-indigo-600"></i>
      </div>
      <div>
        <h3 class="font-bold text-gray-900">Confirm your new email</h3>
        <p class="text-sm text-gray-500">Enter the 6-digit code sent to <strong>{{ $pendingEmail }}</strong></p>
      </div>
    </div>

    @if($errors->any())
      <div class="mb-4 p-3 bg-red-50 border border-red-100 rounded-xl text-red-600 text-sm flex items-center gap-2">
        <i data-lucide="alert-circle" class="w-4 h-4 flex-shrink-0"></i>
        {{ $errors->first() }}
      </div>
    @endif

    <form method="POST" action="{{ route('profile.email.verify') }}">
      @csrf
      <div class="mb-6">
        <label class="block text-xs font-bold uppercase tracking-widest text-gray-600 mb-2">Verification Code</label>
        <input
          type="text"
          name="otp"
          inputmode="numeric"
          pattern="\d{6}"
          maxlength="6"
          placeholder="000000"
          autocomplete="one-time-code"
          autofocus
          required
          class="w-full border-2 border-gray-200 rounded-xl px-4 py-4 text-center text-3xl font-bold tracking-[12px] font-mono focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 outline-none transition {{ $errors->has('otp') ? 'border-red-400' : '' }}"
        />
        <p class="text-xs text-gray-400 mt-2 text-center">Check your inbox and spam folder — expires in 15 minutes</p>
      </div>

      <button type="submit" class="w-full py-3 bg-indigo-600 text-white rounded-xl font-semibold hover:bg-indigo-700 transition">
        Confirm Email Change
      </button>
    </form>

    <div class="mt-4 text-center">
      <a href="{{ route('profile.show') }}" class="text-sm text-gray-500 hover:text-indigo-600 transition">
        Cancel — keep current email
      </a>
    </div>
  </div>
</div>
@endsection
