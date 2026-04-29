@extends('layouts.admin')

@section('title', 'Add Link')
@section('header', 'Add New Link')

@section('content')
<div class="max-w-2xl">
    <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100">
        <form action="{{ route('admin.links.store') }}" method="POST">
            @csrf

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Quick Presets</label>
                <div class="flex flex-wrap gap-2">
                    <button type="button" class="preset-btn px-3 py-1.5 text-xs font-medium bg-gray-100 hover:bg-[#4318FF]/10 hover:text-[#4318FF] rounded-lg transition-colors" data-name="WhatsApp" data-slug="whatsapp" data-placeholder="https://wa.me/977XXXXXXXXXX">WhatsApp</button>
                    <button type="button" class="preset-btn px-3 py-1.5 text-xs font-medium bg-gray-100 hover:bg-[#4318FF]/10 hover:text-[#4318FF] rounded-lg transition-colors" data-name="Facebook" data-slug="facebook" data-placeholder="https://facebook.com/yourpage">Facebook</button>
                    <button type="button" class="preset-btn px-3 py-1.5 text-xs font-medium bg-gray-100 hover:bg-[#4318FF]/10 hover:text-[#4318FF] rounded-lg transition-colors" data-name="Instagram" data-slug="instagram" data-placeholder="https://instagram.com/yourpage">Instagram</button>
                    <button type="button" class="preset-btn px-3 py-1.5 text-xs font-medium bg-gray-100 hover:bg-[#4318FF]/10 hover:text-[#4318FF] rounded-lg transition-colors" data-name="YouTube" data-slug="youtube" data-placeholder="https://youtube.com/@yourchannel">YouTube</button>
                    <button type="button" class="preset-btn px-3 py-1.5 text-xs font-medium bg-gray-100 hover:bg-[#4318FF]/10 hover:text-[#4318FF] rounded-lg transition-colors" data-name="Website" data-slug="website" data-placeholder="https://yourwebsite.com">Website</button>
                    <button type="button" class="preset-btn px-3 py-1.5 text-xs font-medium bg-gray-100 hover:bg-[#4318FF]/10 hover:text-[#4318FF] rounded-lg transition-colors" data-name="Book Now" data-slug="booking" data-placeholder="https://yourwebsite.com/booking">Book Now</button>
                    <button type="button" class="preset-btn px-3 py-1.5 text-xs font-medium bg-gray-100 hover:bg-[#4318FF]/10 hover:text-[#4318FF] rounded-lg transition-colors" data-name="Call Us" data-slug="phone" data-placeholder="tel:+977XXXXXXXXXX">Call Us</button>
                    <button type="button" class="preset-btn px-3 py-1.5 text-xs font-medium bg-gray-100 hover:bg-[#4318FF]/10 hover:text-[#4318FF] rounded-lg transition-colors" data-name="Email" data-slug="email" data-placeholder="mailto:you@example.com">Email</button>
                    <button type="button" class="preset-btn px-3 py-1.5 text-xs font-medium bg-gray-100 hover:bg-[#4318FF]/10 hover:text-[#4318FF] rounded-lg transition-colors" data-name="Location" data-slug="location" data-placeholder="https://maps.google.com/?q=Your+Business">Map/Location</button>
                    <button type="button" class="preset-btn px-3 py-1.5 text-xs font-medium bg-gray-100 hover:bg-[#4318FF]/10 hover:text-[#4318FF] rounded-lg transition-colors" data-name="Twitter/X" data-slug="twitter" data-placeholder="https://x.com/yourhandle">Twitter/X</button>
                </div>
            </div>

            <div class="mb-5">
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Name</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required maxlength="100" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#4318FF]/20 focus:border-[#4318FF] transition-all" placeholder="e.g. WhatsApp, Facebook, Book Now">
                @error('name')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-5">
                <label for="link" class="block text-sm font-medium text-gray-700 mb-2">Link URL</label>
                <input type="url" name="link" id="link" value="{{ old('link') }}" required maxlength="500" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#4318FF]/20 focus:border-[#4318FF] transition-all" placeholder="https://wa.me/977XXXXXXXXXX">
                @error('link')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-5">
                <label for="slug" class="block text-sm font-medium text-gray-700 mb-2">Slug (optional)</label>
                <input type="text" name="slug" id="slug" value="{{ old('slug') }}" maxlength="100" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#4318FF]/20 focus:border-[#4318FF] transition-all" placeholder="Auto-generated from name">
                <p class="text-xs text-gray-500 mt-1">Used in button responses. Auto-generated if empty.</p>
                @error('slug')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} class="w-5 h-5 rounded border-gray-300 text-[#4318FF] focus:ring-[#4318FF]">
                    <span class="text-sm font-medium text-gray-700">Active</span>
                </label>
            </div>

            <div class="flex gap-3">
                <button type="submit" class="flex-1 bg-[#4318FF] text-white px-6 py-3 rounded-xl font-semibold hover:scale-[1.02] active:scale-[0.98] transition-all">
                    Save Link
                </button>
                <a href="{{ route('admin.links.index') }}" class="px-6 py-3 rounded-xl font-medium text-gray-600 hover:bg-gray-100 transition-colors">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const nameInput = document.getElementById('name');
    const slugInput = document.getElementById('slug');
    const linkInput = document.getElementById('link');

    // Auto-generate slug from name
    nameInput.addEventListener('input', function() {
        if (!slugInput.dataset.manual) {
            slugInput.value = this.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
        }
    });

    slugInput.addEventListener('input', function() {
        slugInput.dataset.manual = 'true';
    });

    // Preset buttons
    document.querySelectorAll('.preset-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            nameInput.value = this.dataset.name;
            slugInput.value = this.dataset.slug;
            slugInput.dataset.manual = 'true';
            linkInput.placeholder = this.dataset.placeholder;
            linkInput.focus();
        });
    });
});
</script>
@endsection