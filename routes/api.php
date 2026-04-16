<?php

use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\StreamChatController;
use App\Http\Controllers\Api\WebhookController;
use App\Http\Controllers\Api\WidgetConfigController;
use App\Http\Controllers\Api\WidgetSessionController;
use Illuminate\Support\Facades\Route;

Route::post('/widget/session', [WidgetSessionController::class, 'createSession'])
    ->middleware('throttle:session');

Route::post('/chat', [ChatController::class, 'chat'])
    ->middleware(['throttle:chat', 'widget.domain']);

Route::post('/chat/stream', [StreamChatController::class, 'stream'])
    ->middleware(['throttle:chat', 'widget.domain']);

Route::get('/widget-config/{token}', [WidgetConfigController::class, 'show']);

Route::post('/webhooks/esewa', [WebhookController::class, 'esewa']);
Route::post('/webhooks/khalti', [WebhookController::class, 'khalti']);
