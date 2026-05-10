@extends('layouts.client')

@section('content')
<div class="p-6">
  <div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold">Hosted Chat Pages</h1>
    <a href="{{ route('client.hosted-pages.create') }}" class="px-4 py-2 bg-teal-700 text-white rounded">Create Page</a>
  </div>
  <div class="bg-white rounded border">
    <table class="w-full text-sm">
      <thead><tr class="text-left border-b"><th class="p-3">Slug</th><th>Status</th><th>Public URL</th><th></th></tr></thead>
      <tbody>
      @forelse($pages as $page)
        <tr class="border-b">
          <td class="p-3">{{ $page->slug }}</td>
          <td>{{ $page->status }}</td>
          <td><a class="text-teal-700" href="{{ url('/c/'.$page->slug) }}" target="_blank">{{ url('/c/'.$page->slug) }}</a></td>
          <td><a class="text-blue-700" href="{{ route('client.hosted-pages.edit', $page) }}">Edit</a></td>
        </tr>
      @empty
        <tr><td class="p-3" colspan="4">No hosted pages yet.</td></tr>
      @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
