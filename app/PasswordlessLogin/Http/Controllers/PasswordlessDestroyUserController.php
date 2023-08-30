<?php

namespace App\PasswordlessLogin\Http\Controllers;

use Illuminate\Http\Request;

class PasswordlessDestroyUserController extends PasswordlessController
{
    public function destroy(Request $request)
    {
        abort_unless(config('ycsplayer.password_less'), 401);

        $this->preparePasswordless($request);

        $user = $this->getUser();

        $user->delete();

        return redirect('/');
    }
}
