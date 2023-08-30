<?php

namespace App\PasswordlessLogin\Http\Controllers;

use App\Facades\Flash;
use Illuminate\Http\Request;

class PasswordlessDestroyUserController extends PasswordlessController
{
    public function destroy(Request $request)
    {
        abort_unless(config('ycsplayer.password_less'), 401);

        $this->preparePasswordless($request);

        $user = $this->getUser();

        $user->delete();

        Flash::success('帳號刪除成功');

        return redirect()->route('login');
    }
}
