<?php

use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\WebhookController;
use App\Http\Controllers\Api\WidgetConfigController;
use Illuminate\Support\Facades\Route;

Route::post('/chat', [ChatController::class, 'chat'])
    ->middleware('throttle:chat');

Route::get('/widget-config/{token}', [WidgetConfigController::class, 'show']);

Route::post('/webhooks/esewa', [WebhookController::class, 'esewa']);
Route::post('/webhooks/khalti', [WebhookController::class, 'khalti']);
