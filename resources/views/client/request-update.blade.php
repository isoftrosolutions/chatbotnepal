@extends('layouts.client')
@section('title', 'Request Update')

<div class="max-w-2xl">
    <h2 class="text-xl font-semibold text-gray-900 mb-6">Request Knowledge Base Update</h2>

    <div class="bg-white rounded-lg shadow p-6">
        <p class="text-gray-600 mb-6">Tell us about any changes in your business — new services, updated pricing, changed hours, or new FAQs. We'll update your chatbot knowledge base.</p>

        <form action="{{ route('client.request-update.store') }}" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">What changed?</label>
                <textarea name="details" rows="6" required class="w-full px-4 py-3 border rounded-lg focus:ring-indigo-500 focus:border-indigo-500" placeholder="Example: We added new yoga classes at Rs. 2,000/month. Please update the chatbot knowledge base.">{{ old('details') }}</textarea>
                @error('details') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>
            <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">Submit Request</button>
        </form>
    </div>
</div>
