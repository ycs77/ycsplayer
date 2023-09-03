<?php

use App\Broadcasting\Http\Controllers\PusherWebhookController;
use App\Http\Controllers\PlayerController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\RoomMediaController;
use App\Http\Controllers\RoomMemberController;
use App\Http\Controllers\RoomPlaylistController;
use App\Http\Controllers\RoomUploadMediaController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'landing-page')->middleware('guest')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/rooms', [RoomController::class, 'index'])->name('rooms.index');
    Route::post('/rooms', [RoomController::class, 'store'])->name('rooms.store');
    Route::get('/rooms/{room}', [RoomController::class, 'show'])->name('rooms.show');
    Route::put('/rooms/{room}/note', [RoomController::class, 'note'])->name('rooms.note.update');
    Route::get('/rooms/{room}/members', [RoomController::class, 'members'])->name('rooms.members');
    Route::get('/rooms/{room}/settings', [RoomController::class, 'edit'])->name('rooms.edit');
    Route::post('/rooms/{room}/settings', [RoomController::class, 'update'])->name('rooms.update');
    Route::delete('/rooms/{room}', [RoomController::class, 'destroy'])->name('rooms.destroy');

    Route::post('/rooms/{room}/playlist', [RoomPlaylistController::class, 'store'])->name('rooms.playlist.store');
    Route::post('/rooms/{room}/playlist/{item}', [RoomPlaylistController::class, 'click'])->name('rooms.playlist.click');
    Route::delete('/rooms/{room}/playlist/{item}', [RoomPlaylistController::class, 'destroy'])->name('rooms.playlist.destroy');
    Route::post('/rooms/{room}/next', [RoomPlaylistController::class, 'next'])->name('rooms.playlist.next');

    Route::get('/rooms/{room}/join', [RoomMemberController::class, 'join'])
        ->middleware('signed')->name('rooms.join');
    Route::post('/rooms/{room}/generate-join-link', [RoomMemberController::class, 'generateJoinLink'])->name('rooms.generate-join-link');
    Route::post('/rooms/{room}/invite', [RoomMemberController::class, 'invite'])->name('rooms.invite');
    Route::post('/rooms/{room}/search-member', [RoomMemberController::class, 'searchMember'])->name('rooms.member.search');
    Route::delete('/rooms/{room}/members/{member}', [RoomMemberController::class, 'destroy'])->name('rooms.member.destroy');

    Route::get('/rooms/{room}/medias', [RoomMediaController::class, 'index'])->name('rooms.medias.index');
    Route::delete('/rooms/{room}/medias/{media:uuid}', [RoomMediaController::class, 'destroy'])->name('rooms.medias.destroy');

    Route::post('/rooms/{room}/upload', RoomUploadMediaController::class)->name('rooms.medias.upload');

    Route::post('/player/play', [PlayerController::class, 'play'])->name('player.play');
    Route::post('/player/pause', [PlayerController::class, 'pause'])->name('player.pause');
    Route::post('/player/seeked', [PlayerController::class, 'seeked'])->name('player.seeked');
    Route::post('/player/time-update', [PlayerController::class, 'timeUpdate'])->name('player.time-update');
    Route::post('/player/end', [PlayerController::class, 'end'])->name('player.end');

    Route::get('/user/settings', [UserController::class, 'show'])->name('user.settings');
    Route::get('/user/destroy/confirm', [UserController::class, 'confirmDestroy'])->name('user.destroy.confirm');
    Route::delete('/user', [UserController::class, 'destroy'])->name('user.destroy');
});

Route::post('/pusher/webhook', PusherWebhookController::class);
