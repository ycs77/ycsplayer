<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class UserController extends Controller
{
    public function show()
    {
        return Inertia::render('User/Settings', [
            'passwordLess' => config('ycsplayer.password_less'),
        ]);
    }

    public function destroy(Request $request)
    {
        /** @var \App\Models\User */
        $user = Auth::user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Inertia::location('/');
    }
}
