<?php

namespace App\Http\Controllers;

use App\Facades\Flash;
use App\Models\Room;
use App\Models\User;
use App\Presenters\RoomMemberPresenter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\ValidationException;

class RoomMemberController extends Controller
{
    public function join(Room $room)
    {
        /** @var \App\Models\User */
        $user = Auth::user();

        if ($room->isMember($user)) {
            return redirect()->route('rooms.show', $room);
        }

        $room->join($user);

        Flash::success('加入房間成功');

        return redirect()->route('rooms.show', $room);
    }

    public function generateJoinLink(Room $room)
    {
        $this->authorize('inviteMember', $room);

        $joinLink = URL::temporarySignedRoute(
            'rooms.join', now()->addDay(), ['room' => $room->hash_id]
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

        Flash::success('用戶加入成功');
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
        $this->authorize('view', $room);

        /** @var \App\Models\User */
        $user = Auth::user();

        if ($user->is($member)) {
            if ($user->getRoleNames()->contains("rooms.{$room->id}.admin")) {
                ValidationException::withMessages([
                    'leave_room' => '管理員不可以離開房間，可以將管理員轉交給其他成員，或者直接刪除房間。',
                ]);
            }

            $room->leave($member);

            Flash::success('離開房間成功');

            return redirect()->route('rooms.index');
        }

        $this->authorize('removeMember', $room);

        if ($room->isMember($member)) {
            $room->leave($member);

            Flash::success('用戶退出房間成功');
        }
    }
}
