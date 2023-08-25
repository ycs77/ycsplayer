<?php

namespace App\Http\Controllers\Room;

use App\Enums\RoomType;
use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Presenters\RoomPresenter;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;
use Inertia\Inertia;

class RoomSettingController extends Controller
{
    public function show(Room $room)
    {
        return Inertia::render('Room/Settings', [
            'room' => fn () => RoomPresenter::make($room)->preset('show'),
        ]);
    }

    public function store(Request $request, Room $room)
    {
        $request->validate([
            'type' => ['required', new Enum(RoomType::class)],
            'auto_play' => ['required', 'boolean'],
            'auto_remove' => ['required', 'boolean'],
        ]);

        $room->update($request->only('type', 'auto_play', 'auto_remove'));
    }
}
