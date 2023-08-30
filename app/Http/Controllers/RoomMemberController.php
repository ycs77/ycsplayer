<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\User;
use App\Presenters\RoomMemberPresenter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\ValidationException;

class RoomMemberController extends Controller
{
    public function join(Request $request, Room $room)
    {
        if (! $request->hasValidSignature()) {
            abort(401);
        }

        /** @var \App\Models\User */
        $user = Auth::user();

        if ($room->isMember($user)) {
            return redirect()->route('rooms.show', $room);
        }

        $room->join($user);

        return redirect()->route('rooms.show', $room);
    }

    public function generateJoinLink(Room $room)
    {
        $this->authorize('inviteMember', $room);

        $joinLink = URL::temporarySignedRoute(
            'rooms.join', now()->addDay(), $room
        );

        return response()->json([
            'join_link' => $joinLink,
        ]);
    }

    public function invite(Request $request, Room $room)
    {
        $this->authorize('inviteMember', $room);

        $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
        ]);

        if ($room->members()->where('email', $request->input('email'))->exists()) {
            throw ValidationException::withMessages([
                'email' => '使用者已加入該房間',
            ]);
        }

        /** @var \App\Models\User */
        $member = User::where('email', $request->input('email'))->first();

        $room->join($member);
    }

    public function searchMember(Request $request, Room $room)
    {
        $this->authorize('inviteMember', $room);

        $request->validate([
            'email' => ['required', 'email'],
        ]);

        /** @var \App\Models\User */
        $member = User::where('email', $request->input('email'))->first();

        return response()->json([
            'member' => RoomMemberPresenter::make($member)->toArray(),
        ]);
    }

    public function destroy(Room $room, User $member)
    {
        /** @var \App\Models\User */
        $user = Auth::user();

        if (! $user->is($member)) {
            $this->authorize('removeMember', $room);
        }

        if ($room->isMember($member)) {
            $room->leave($member);
        }
    }
}
