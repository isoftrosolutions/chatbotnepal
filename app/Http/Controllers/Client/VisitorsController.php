<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Visitor;
use Illuminate\Http\Request;

class VisitorsController extends Controller
{
    public function index(Request $request)
    {
        $user  = auth()->user();
        $query = Visitor::where('user_id', $user->id);

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $sort = $request->get('sort', 'latest');
        match ($sort) {
            'oldest'  => $query->orderBy('first_seen_at'),
            'name'    => $query->orderBy('name'),
            default   => $query->orderByDesc('last_seen_at'),
        };

        $visitors = $query->withCount([
            'conversations as total_conversations' => fn ($q) => $q->where('user_id', $user->id),
        ])->paginate(24);

        $totalVisitors  = Visitor::where('user_id', $user->id)->count();
        $knownVisitors  = Visitor::where('user_id', $user->id)->whereNotNull('name')->count();
        $todayVisitors  = Visitor::where('user_id', $user->id)->whereDate('last_seen_at', today())->count();

        return view('client.visitors', compact('visitors', 'totalVisitors', 'knownVisitors', 'todayVisitors'));
    }
}
