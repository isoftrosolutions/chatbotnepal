<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ChatOrchestratorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HostedChatController extends Controller
{
    public function __construct(private readonly ChatOrchestratorService $orchestrator) {}

    public function init(Request $request): JsonResponse
    {
        $request->validate([
            'slug' => 'required|string|max:120',
            'visitor_fingerprint' => 'nullable|string|max:191',
            'source_url' => 'nullable|url|max:500',
        ]);

        $result = $this->orchestrator->initSession([
            'slug' => $request->string('slug')->toString(),
            'visitor_fingerprint' => $request->input('visitor_fingerprint'),
            'source_url' => $request->input('source_url'),
            'user_agent' => $request->userAgent(),
        ], $request->ip());

        return response()->json($result, ($result['success'] ?? false) ? 200 : 400);
    }

    public function message(Request $request): JsonResponse
    {
        $request->validate([
            'session_id' => 'required|uuid',
            'message' => 'required|string|max:1000',
            'source_url' => 'nullable|url|max:500',
            'visitor_name' => 'nullable|string|max:100',
            'visitor_email' => 'nullable|email|max:191',
            'visitor_phone' => 'nullable|string|max:30',
        ]);

        $result = $this->orchestrator->processMessage([
            'session_id' => $request->string('session_id')->toString(),
            'message' => $this->sanitizeInput($request->string('message')->toString()),
            'source_url' => $request->input('source_url'),
            'visitor_name' => $request->input('visitor_name'),
            'visitor_email' => $request->input('visitor_email'),
            'visitor_phone' => $request->input('visitor_phone'),
        ], $request->ip(), $request->userAgent());

        return response()->json($result, ($result['success'] ?? false) ? 200 : 400);
    }

    public function lead(Request $request): JsonResponse
    {
        $request->validate([
            'session_id' => 'required|uuid',
            'name' => 'nullable|string|max:100',
            'email' => 'nullable|email|max:191',
            'phone' => 'nullable|string|max:30',
            'notes' => 'nullable|string|max:1000',
            'trigger' => 'nullable|string|max:64',
        ]);

        $result = $this->orchestrator->captureLead($request->only([
            'session_id', 'name', 'email', 'phone', 'notes', 'trigger',
        ]));

        return response()->json($result, ($result['success'] ?? false) ? 200 : 400);
    }

    private function sanitizeInput(string $input): string
    {
        $cleaned = strip_tags($input);
        $cleaned = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $cleaned);
        $cleaned = preg_replace('/<iframe\b[^>]*>(.*?)<\/iframe>/is', '', $cleaned);
        $cleaned = preg_replace('/javascript:/i', '', $cleaned);
        $cleaned = preg_replace('/on\w+\s*=/i', '', $cleaned);

        return trim($cleaned);
    }
}
