@extends('layouts.client')
@section('title', 'Request Update')
@section('header', 'Knowledge Base Update')

@section('content')
<div class="max-w-4xl space-y-8">
    <!-- Header Card -->
    <div class="bg-gradient-to-br from-indigo-600 to-violet-600 rounded-3xl p-8 text-white shadow-xl relative overflow-hidden">
        <div class="absolute -right-4 -top-4 w-32 h-32 bg-white/5 rounded-full blur-3xl"></div>
        <div class="relative">
            <div class="w-16 h-16 bg-white/10 rounded-3xl flex items-center justify-center mb-6">
                <i data-lucide="edit-3" class="w-8 h-8 text-white"></i>
            </div>
            <h1 class="text-3xl font-bold mb-4">Request Knowledge Base Update</h1>
            <p class="text-indigo-100 text-lg leading-relaxed">
                Keep your chatbot current and accurate. Tell us about any changes in your business, and we'll update the knowledge base to ensure your visitors get the most up-to-date information.
            </p>
        </div>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8">
        <div class="mb-8">
            <h2 class="text-xl font-bold text-[#1B1B38] mb-2">What Changed?</h2>
            <p class="text-gray-400">Describe the updates you'd like us to make to your chatbot's knowledge base.</p>
        </div>

        <form action="{{ route('client.request-update.store') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Details Textarea -->
            <div>
                <label for="details" class="block text-sm font-bold uppercase tracking-widest text-gray-600 mb-3">
                    Update Details
                    <span class="text-red-500 ml-1">*</span>
                </label>
                <div class="relative">
                    <textarea
                        name="details"
                        id="details"
                        rows="8"
                        required
                        class="w-full px-4 py-4 border border-gray-200 rounded-2xl focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 outline-none transition-all resize-none text-gray-800 placeholder-gray-400"
                        placeholder="Example: We added new yoga classes at Rs. 2,000/month and changed our opening hours to 6 AM - 9 PM. Please update the chatbot knowledge base with this new information."
                        oninput="updateCharCount()">{{ old('details') }}</textarea>
                    <div class="absolute bottom-3 right-3 text-xs text-gray-400" id="char-count">0 characters</div>
                </div>
                @error('details')
                    <p class="text-red-500 text-sm mt-2 flex items-center gap-2">
                        <i data-lucide="alert-circle" class="w-4 h-4"></i>
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <!-- Examples -->
            <div class="bg-[#F4F7FE] rounded-2xl p-6">
                <h3 class="text-sm font-bold text-[#1B1B38] mb-4 flex items-center gap-2">
                    <i data-lucide="lightbulb" class="w-4 h-4 text-indigo-600"></i>
                    Examples of Updates
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-600">
                    <div class="flex gap-3">
                        <div class="w-2 h-2 bg-indigo-600 rounded-full mt-2 flex-shrink-0"></div>
                        <span>New services or products added to your business</span>
                    </div>
                    <div class="flex gap-3">
                        <div class="w-2 h-2 bg-indigo-600 rounded-full mt-2 flex-shrink-0"></div>
                        <span>Price changes or new pricing tiers</span>
                    </div>
                    <div class="flex gap-3">
                        <div class="w-2 h-2 bg-indigo-600 rounded-full mt-2 flex-shrink-0"></div>
                        <span>Updated business hours or location changes</span>
                    </div>
                    <div class="flex gap-3">
                        <div class="w-2 h-2 bg-indigo-600 rounded-full mt-2 flex-shrink-0"></div>
                        <span>New FAQs or common questions</span>
                    </div>
                    <div class="flex gap-3">
                        <div class="w-2 h-2 bg-indigo-600 rounded-full mt-2 flex-shrink-0"></div>
                        <span>Policy updates or terms changes</span>
                    </div>
                    <div class="flex gap-3">
                        <div class="w-2 h-2 bg-indigo-600 rounded-full mt-2 flex-shrink-0"></div>
                        <span>Contact information updates</span>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="flex items-center justify-between pt-6 border-t border-gray-50">
                <div class="text-sm text-gray-500">
                    We'll review your request and update the knowledge base within 24 hours.
                </div>
                <button type="submit" class="px-8 py-4 bg-indigo-600 text-white rounded-2xl font-bold hover:bg-indigo-700 transition-all shadow-lg hover:shadow-xl flex items-center gap-2">
                    <i data-lucide="send" class="w-5 h-5"></i>
                    Submit Update Request
                </button>
            </div>
        </form>
    </div>

    <!-- Recent Updates Info -->
    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-[#FFF5E9] rounded-2xl flex items-center justify-center">
                <i data-lucide="clock" class="w-6 h-6 text-[#FFB547]"></i>
            </div>
            <div>
                <h3 class="text-lg font-bold text-[#1B1B38]">Update Processing</h3>
                <p class="text-gray-400 text-sm">Your requests are typically processed within 24 hours during business days.</p>
            </div>
        </div>
    </div>
</div>
@endsection

<script>
function updateCharCount() {
    const textarea = document.getElementById('details');
    const counter = document.getElementById('char-count');
    const length = textarea.value.length;
    counter.textContent = length + ' character' + (length !== 1 ? 's' : '');
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    lucide.createIcons();
    updateCharCount();
});
</script>
