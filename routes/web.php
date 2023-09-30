<?php

use App\Broadcasting\Http\Controllers\PusherWebhookController;
use App\Http\Controllers\PlayerController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\RoomMediaController;
use App\Http\Controllers\RoomMemberController;
use App\Http\Controllers\RoomNoteController;
use App\Http\Controllers\RoomPlaylistController;
use App\Http\Controllers\RoomUploadMediaController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'landing-page')->middleware('guest')->name('home');

Route::middleware([
    'auth',
    ...(config('ycsplayer.mail') ? ['verified'] : []),
])->group(function () {
    Route::get('/rooms', [RoomController::class, 'index'])->name('rooms.index');
    Route::post('/rooms', [RoomController::class, 'store'])->name('rooms.store');
    Route::get('/rooms/{room}', [RoomController::class, 'show'])->name('rooms.show');
    Route::put('/rooms/{room}', [RoomController::class, 'update'])->name('rooms.update');
    Route::delete('/rooms/{room}', [RoomController::class, 'destroy'])->name('rooms.destroy');

    Route::post('/rooms/{room}/note', [RoomNoteController::class, 'edit'])->name('rooms.note.edit');
    Route::put('/rooms/{room}/note', [RoomNoteController::class, 'update'])->name('rooms.note.update');
    Route::delete('/rooms/{room}/note', [RoomNoteController::class, 'destroy'])->name('rooms.note.destroy');

    Route::post('/rooms/{room}/playlist', [RoomPlaylistController::class, 'store'])->name('rooms.playlist.store');
    Route::post('/rooms/{room}/playlist/youtube-title', [RoomPlaylistController::class, 'youtubeTitle'])->name('rooms.playlist.youtube-title');
    Route::post('/rooms/{room}/playlist/{item}', [RoomPlaylistController::class, 'click'])->name('rooms.playlist.click');
    Route::delete('/rooms/{room}/playlist/{item}', [RoomPlaylistController::class, 'destroy'])->name('rooms.playlist.destroy');
    Route::post('/rooms/{room}/next', [RoomPlaylistController::class, 'next'])->name('rooms.playlist.next');

    Route::get('/rooms/{room}/join', [RoomMemberController::class, 'join'])
        ->middleware('signed')->name('rooms.join');
    Route::post('/rooms/{room}/generate-join-link', [RoomMemberController::class, 'generateJoinLink'])->name('rooms.generate-join-link');
    Route::post('/rooms/{room}/invite', [RoomMemberController::class, 'invite'])->name('rooms.invite');
    Route::post('/rooms/{room}/search-member', [RoomMemberController::class, 'searchMember'])->name('rooms.member.search');
    Route::patch('/rooms/{room}/members/{member}/role', [RoomMemberController::class, 'role'])->name('rooms.member.role');
    Route::delete('/rooms/{room}/members/{member}', [RoomMemberController::class, 'destroy'])->name('rooms.member.destroy');

    Route::get('/rooms/{room}/medias', [RoomMediaController::class, 'index'])->name('rooms.medias.index');
    Route::delete('/rooms/{room}/medias/{media:uuid}', [RoomMediaController::class, 'destroy'])->name('rooms.medias.destroy');

    Route::post('/rooms/{room}/upload', RoomUploadMediaController::class)->name('rooms.medias.upload');

    Route::post('/player/play', [PlayerController::class, 'play'])->name('player.play');
    Route::post('/player/pause', [PlayerController::class, 'pause'])->name('player.pause');
    Route::post('/player/seeked', [PlayerController::class, 'seeked'])->name('player.seeked');
    Route::post('/player/time-update', [PlayerController::class, 'timeUpdate'])->name('player.time-update');
    Route::post('/player/end', [PlayerController::class, 'end'])->name('player.end');
    if (config('ycsplayer.debug')) {
        Route::post('/player/debug', [PlayerController::class, 'debug'])->name('player.debug');
    }

    Route::get('/user/settings', [UserController::class, 'show'])->name('user.settings');
    Route::post('/user/avatar', [UserController::class, 'uploadAvatar'])->name('user.avatar.store');
    Route::delete('/user/avatar', [UserController::class, 'removeAvatar'])->name('user.avatar.destroy');
    Route::get('/user/destroy/confirm', [UserController::class, 'confirmDestroy'])->name('user.destroy.confirm');
    Route::delete('/user', [UserController::class, 'destroy'])->name('user.destroy');
});

Route::post('/pusher/webhook', PusherWebhookController::class);
