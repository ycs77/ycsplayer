<?php

namespace App\Http\Controllers;

use App\Facades\Flash;
use App\PasswordlessLogin\DestroyUserUrl;
use App\PasswordlessLogin\Notifications\SendPasswordlessDestroyUserLink;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\File;
use Inertia\Inertia;
use Spatie\Image\Image;
use Spatie\Image\Manipulations;

class UserController extends Controller
{
    public function __construct()
    {
        if (! config('ycsplayer.password_less')) {
            $this->middleware('password.confirm')->only('confirmDestroy');
        }
    }

    public function show()
    {
        /** @var \App\Models\User */
        $user = Auth::user();

        return Inertia::render('User/Settings', [
            'user' => [
                'id' => $user->hash_id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $user->avatar_url,
            ],
            'passwordLess' => config('ycsplayer.password_less'),
            'can' => [
                'uploadAvatar' => config('ycsplayer.upload_avatar'),
            ],
        ])->title('帳號設定');
    }

    public function uploadAvatar(Request $request)
    {
        abort_unless(config('ycsplayer.upload_avatar'), 404);

        $request->validate([
            'avatar' => [
                'nullable',
                File::types(['jpg', 'jpeg', 'png'])
                    ->max(5120), // 5M
            ],
        ]);

        /** @var \App\Models\User */
        $user = Auth::user();

        if ($avatar = $request->file('avatar')) {
            $user->avatar = $avatar->store('avatars');

            /** @phpstan-ignore-next-line */
            Image::load(Storage::path($user->avatar))
                ->fit(Manipulations::FIT_CROP, 250, 250)
                ->save();
        }

        $user->save();

        Flash::success('用戶頭像上傳成功');
    }

    public function removeAvatar()
    {
        abort_unless(config('ycsplayer.upload_avatar'), 404);

        /** @var \App\Models\User */
        $user = Auth::user();
        $user->update(['avatar' => null]);

        Flash::success('用戶頭像刪除成功，恢復成用戶預設頭像');
    }

    public function confirmDestroy()
    {
        return Inertia::render('User/ConfirmDestroyUser');
    }

    public function destroy(Request $request)
    {
        /** @var \App\Models\User */
        $user = Auth::user();

        // 無密碼模式下，要寄送刪除帳號連結的信件
        if (config('ycsplayer.password_less')) {
            $url = DestroyUserUrl::forUser($user)->generate();

            $user->notify(new SendPasswordlessDestroyUserLink($url));

            Auth::logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('passwordless-destroy-user.send')
                ->with('email', $user->email);
        }

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        Flash::success('帳號刪除成功');

        return redirect()->route('login');
    }
}
