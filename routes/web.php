<?php

use App\Broadcasting\Http\Controllers\PusherWebhookController;
use App\Http\Controllers\PlayerController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\RoomMediaController;
use App\Http\Controllers\RoomPlaylistController;
use App\Http\Controllers\RoomSettingController;
use App\Http\Controllers\RoomUploadMediaController;
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

Route::post('/rooms/{room}/playlist', [RoomPlaylistController::class, 'store']);
Route::post('/rooms/{room}/playlist/{item}', [RoomPlaylistController::class, 'click']);
Route::delete('/rooms/{room}/playlist/{item}', [RoomPlaylistController::class, 'destroy']);
Route::post('/rooms/{room}/next', [RoomPlaylistController::class, 'next']);

Route::get('/rooms/{room}/medias', [RoomMediaController::class, 'index']);
Route::delete('/rooms/{room}/medias/{media:uuid}', [RoomMediaController::class, 'delete']);

Route::post('/rooms/{room}/upload', RoomUploadMediaController::class);

Route::get('/rooms/{room}/settings', [RoomSettingController::class, 'show']);
Route::post('/rooms/{room}/settings', [RoomSettingController::class, 'store']);

Route::post('/player/play', [PlayerController::class, 'play']);
Route::post('/player/pause', [PlayerController::class, 'pause']);
Route::post('/player/seeked', [PlayerController::class, 'seeked']);
Route::post('/player/time-update', [PlayerController::class, 'timeUpdate']);
Route::post('/player/end', [PlayerController::class, 'end']);

Route::post('/pusher/webhook', PusherWebhookController::class);
