<?php

use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\StreamChatController;
use App\Http\Controllers\Api\WebhookController;
use App\Http\Controllers\Api\WidgetConfigController;
use App\Http\Controllers\Api\WidgetSessionController;
use Illuminate\Support\Facades\Route;

Route::options('/{any}', function () {
    return response('', 204)
        ->header('Access-Control-Allow-Origin', '*')
        ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
        ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Session-Token, X-Requested-With, Accept');
})->where('any', '.*');

Route::post('/widget/session', [WidgetSessionController::class, 'createSession'])
    ->middleware('throttle:session')
    ->withoutMiddleware('widget.domain');

Route::post('/widget/session/verify', [WidgetSessionController::class, 'verifySession'])
    ->middleware('throttle:session')
    ->withoutMiddleware('widget.domain');

Route::post('/chat', [ChatController::class, 'chat'])
    ->middleware(['throttle:chat', 'widget.domain']);

Route::post('/chat/stream', [StreamChatController::class, 'stream'])
    ->middleware(['throttle:chat', 'widget.domain']);

Route::post('/chat/history', [ChatController::class, 'history'])
    ->middleware(['throttle:60,1', 'widget.domain']);

Route::get('/widget-config/{token}', [WidgetConfigController::class, 'show']);

Route::post('/webhooks/esewa', [WebhookController::class, 'esewa']);
Route::post('/webhooks/khalti', [WebhookController::class, 'khalti']);
