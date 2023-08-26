<?php

namespace App\Http\Controllers;

use App\Enums\RoomType;
use App\Models\Room;
use App\Presenters\RoomPresenter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Enum;
use Inertia\Inertia;

class RoomSettingController extends Controller
{
    public function show(Room $room)
    {
        $this->authorize('settings', $room);

        /** @var \App\Models\User */
        $user = Auth::user();

        return Inertia::render('Room/Settings', [
            'room' => fn () => RoomPresenter::make($room),
            'can' => fn () => [
                'uplaodFiles' => $user->can('uplaodFiles', $room),
                'settings' => $user->can('settings', $room),
            ],
        ]);
    }

    public function store(Request $request, Room $room)
    {
        $this->authorize('settings', $room);

        $request->validate([
            'type' => [new Enum(RoomType::class)],
            'auto_play' => ['required', 'boolean'],
            'auto_remove' => ['required', 'boolean'],
        ]);

        $room->update($request->only('type', 'auto_play', 'auto_remove'));
    }
}
