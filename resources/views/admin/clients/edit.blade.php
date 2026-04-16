@extends('layouts.admin')
@section('title', 'Edit Client')
@section('header', 'Edit Client')

<form action="{{ route('admin.clients.update', $client->id) }}" method="POST" class="bg-gray-800 rounded-lg border border-gray-700 p-6 max-w-2xl">
    @csrf @method('PUT')
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm text-gray-400 mb-1">Name *</label>
            <input type="text" name="name" value="{{ old('name', $client->name) }}" required class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-indigo-500">
        </div>
        <div>
            <label class="block text-sm text-gray-400 mb-1">Email *</label>
            <input type="email" name="email" value="{{ old('email', $client->email) }}" required class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-indigo-500">
        </div>
        <div>
            <label class="block text-sm text-gray-400 mb-1">Phone</label>
            <input type="text" name="phone" value="{{ old('phone', $client->phone) }}" class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-indigo-500">
        </div>
        <div>
            <label class="block text-sm text-gray-400 mb-1">Password (leave empty to keep)</label>
            <input type="password" name="password" class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-indigo-500">
        </div>
        <div>
            <label class="block text-sm text-gray-400 mb-1">Company Name</label>
            <input type="text" name="company_name" value="{{ old('company_name', $client->company_name) }}" class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-indigo-500">
        </div>
        <div>
            <label class="block text-sm text-gray-400 mb-1">Website URL</label>
            <input type="url" name="website_url" value="{{ old('website_url', $client->website_url) }}" class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-indigo-500">
        </div>
        <div>
            <label class="block text-sm text-gray-400 mb-1">Plan *</label>
            <select name="plan" required class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-indigo-500">
                <option value="basic" {{ $client->plan === 'basic' ? 'selected' : '' }}>Basic</option>
                <option value="standard" {{ $client->plan === 'standard' ? 'selected' : '' }}>Standard</option>
                <option value="growth" {{ $client->plan === 'growth' ? 'selected' : '' }}>Growth</option>
                <option value="pro" {{ $client->plan === 'pro' ? 'selected' : '' }}>Pro</option>
            </select>
        </div>
        <div>
            <label class="block text-sm text-gray-400 mb-1">Status *</label>
            <select name="status" required class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-indigo-500">
                <option value="active" {{ $client->status === 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ $client->status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                <option value="suspended" {{ $client->status === 'suspended' ? 'selected' : '' }}>Suspended</option>
            </select>
        </div>
    </div>
    <div class="mt-6">
        <p class="text-sm text-gray-400 mb-2">API Token: <code class="text-indigo-400">{{ $client->api_token }}</code></p>
    </div>
    <div class="mt-6 flex gap-4">
        <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">Update Client</button>
        <a href="{{ route('admin.clients.index') }}" class="px-6 py-2 bg-gray-700 text-gray-300 rounded-lg hover:bg-gray-600">Cancel</a>
    </div>
</form>
