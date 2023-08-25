<?php

use App\Broadcasting\Http\Controllers\PusherWebhookController;
use App\Http\Controllers\PlayerController;
use App\Http\Controllers\RoomController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

if (app()->environment('local') && ! app()->runningInConsole()) {
    Auth::login(User::first());
}

Route::redirect('/', '/rooms');

Route::get('/rooms', [RoomController::class, 'index']);
Route::get('/rooms/{room}', [RoomController::class, 'show']);
Route::get('/rooms/{room}/members', [RoomController::class, 'members']);
Route::post('/rooms/{room}/play/{item}', [RoomController::class, 'clickMedia']);
Route::post('/rooms/{room}/next', [RoomController::class, 'nextMedia']);

Route::post('/player/play', [PlayerController::class, 'play']);
Route::post('/player/pause', [PlayerController::class, 'pause']);
Route::post('/player/seeked', [PlayerController::class, 'seeked']);
Route::post('/player/time-update', [PlayerController::class, 'timeUpdate']);
Route::post('/player/end', [PlayerController::class, 'end']);

Route::post('/pusher/webhook', PusherWebhookController::class);
