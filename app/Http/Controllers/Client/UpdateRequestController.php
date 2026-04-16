<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UpdateRequestController extends Controller
{
    public function create(): View
    {
        return view('client.request-update');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'details' => 'required|string|max:2000',
        ]);

        return redirect()->route('client.request-update.create')
            ->with('success', 'Your update request has been submitted. Our team will review and update your knowledge base shortly.');
    }
}
