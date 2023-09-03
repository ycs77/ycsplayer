<?php

namespace App\Http\Controllers;

use App\Facades\Flash;
use App\PasswordlessLogin\DestroyUserUrl;
use App\PasswordlessLogin\Notifications\SendPasswordlessDestroyUserLink;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

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
        return Inertia::render('User/Settings', [
            'passwordLess' => config('ycsplayer.password_less'),
        ])->title('帳號設定');
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
