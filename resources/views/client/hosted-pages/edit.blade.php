@extends('layouts.client')

@section('content')
<div class="p-6 max-w-2xl">
  <h1 class="text-2xl font-bold mb-6">Edit Hosted Page</h1>
  <form method="POST" action="{{ route('client.hosted-pages.update', $hostedPage) }}" class="space-y-4 bg-white p-4 rounded border">
    @csrf
    @method('PUT')
    <select name="status" class="w-full border p-2">
      <option value="active" @selected($hostedPage->status==='active')>active</option>
      <option value="disabled" @selected($hostedPage->status==='disabled')>disabled</option>
    </select>
    <input name="title" class="w-full border p-2" value="{{ $hostedPage->public_config['title'] ?? '' }}" required>
    <textarea name="welcome_message" class="w-full border p-2" required>{{ $hostedPage->public_config['welcome_message'] ?? '' }}</textarea>
    <input name="logo_url" class="w-full border p-2" value="{{ $hostedPage->public_config['logo_url'] ?? '' }}">
    <input name="brand_primary" class="w-full border p-2" value="{{ $hostedPage->public_config['branding']['primary'] ?? '#0f766e' }}">
    <input name="brand_bg" class="w-full border p-2" value="{{ $hostedPage->public_config['branding']['bg'] ?? '#f8fafc' }}">
    <input name="brand_font" class="w-full border p-2" value="{{ $hostedPage->public_config['branding']['font'] ?? 'system-ui, sans-serif' }}">
    <button class="px-4 py-2 bg-teal-700 text-white rounded">Save</button>
  </form>
</div>
@endsection
