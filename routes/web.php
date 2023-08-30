<?php

use App\Broadcasting\Http\Controllers\PusherWebhookController;
use App\Http\Controllers\PlayerController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\RoomMediaController;
use App\Http\Controllers\RoomMemberController;
use App\Http\Controllers\RoomPlaylistController;
use App\Http\Controllers\RoomSettingController;
use App\Http\Controllers\RoomUploadMediaController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'landing-page')->middleware('guest');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/rooms', [RoomController::class, 'index']);
    Route::get('/rooms/{room}', [RoomController::class, 'show']);
    Route::get('/rooms/{room}/members', [RoomController::class, 'members']);

    Route::post('/rooms/{room}/playlist', [RoomPlaylistController::class, 'store']);
    Route::post('/rooms/{room}/playlist/{item}', [RoomPlaylistController::class, 'click']);
    Route::delete('/rooms/{room}/playlist/{item}', [RoomPlaylistController::class, 'destroy']);
    Route::post('/rooms/{room}/next', [RoomPlaylistController::class, 'next']);

    Route::get('/rooms/{room}/join', [RoomMemberController::class, 'join'])->name('rooms.join');
    Route::post('/rooms/{room}/generate-join-link', [RoomMemberController::class, 'generateJoinLink']);
    Route::post('/rooms/{room}/invite', [RoomMemberController::class, 'invite']);
    Route::post('/rooms/{room}/search-member', [RoomMemberController::class, 'searchMember']);
    Route::delete('/rooms/{room}/members/{member}', [RoomMemberController::class, 'destroy']);

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

    Route::get('/user/settings', [UserController::class, 'show']);
    Route::get('/user/destroy/confirm', [UserController::class, 'confirmDestroy'])
        ->middleware(config('ycsplayer.password_less') ? null : 'password.confirm');
    Route::delete('/user', [UserController::class, 'destroy']);
});

Route::post('/pusher/webhook', PusherWebhookController::class);
