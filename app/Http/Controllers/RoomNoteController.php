<?php

namespace App\Http\Controllers;

use App\Events\RoomNoteCanceled;
use App\Events\RoomNoteUpdated;
use App\Events\RoomNoteUpdating;
use App\Facades\Flash;
use App\Models\Room;
use App\Room\RoomNoteEditorCacheRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoomNoteController extends Controller
{
    public function __construct(
        protected RoomNoteEditorCacheRepository $noteEditor,
    ) {
        //
    }

    public function edit(Room $room)
    {
        $this->authorize('editNote', $room);

        $user = Auth::user();

        $this->noteEditor->start($room->hash_id, $user->hash_id, $user->name);

        RoomNoteUpdating::broadcast($room->hash_id)->toOthers();

        return response()->noContent();
    }

    public function update(Request $request, Room $room)
    {
        $this->authorize('editNote', $room);

        $request->validate([
            'note' => ['nullable', 'string', 'max:500'],
        ], [], [
            'note' => '記事本',
        ]);

        $room->update($request->only('note'));

        $this->noteEditor->end($room->hash_id);

        RoomNoteUpdated::broadcast($room->hash_id)->toOthers();

        Flash::success('記事本更新成功');
    }

    public function destroy(Room $room)
    {
        $this->authorize('editNote', $room);

        $this->noteEditor->end($room->hash_id);

        RoomNoteCanceled::broadcast($room->hash_id)->toOthers();

        return response()->noContent();
    }
}
