@extends('layouts.client')

@section('content')
<div class="p-6 max-w-2xl">
  <h1 class="text-2xl font-bold mb-6">Create Hosted Page</h1>
  <form method="POST" action="{{ route('client.hosted-pages.store') }}" class="space-y-4 bg-white p-4 rounded border">
    @csrf
    <input name="slug" class="w-full border p-2" placeholder="hotel-diyalo" required>
    <input name="title" class="w-full border p-2" placeholder="Business title" required>
    <textarea name="welcome_message" class="w-full border p-2" required>Hello, how can we help?</textarea>
    <input name="logo_url" class="w-full border p-2" placeholder="https://cdn.example.com/logo.png">
    <input name="brand_primary" class="w-full border p-2" placeholder="#0f766e">
    <input name="brand_bg" class="w-full border p-2" placeholder="#f8fafc">
    <input name="brand_font" class="w-full border p-2" placeholder="system-ui, sans-serif">
    <button class="px-4 py-2 bg-teal-700 text-white rounded">Create</button>
  </form>
</div>
@endsection
