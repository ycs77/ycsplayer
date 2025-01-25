<?php

namespace App\Actions\Fortify;

use App\Facades\Flash;
use App\Models\User;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;

class UpdateUserProfileInformation implements UpdatesUserProfileInformation
{
    /**
     * Validate and update the given user's profile information.
     *
     * @param  array<string, string>  $input
     */
    public function update(User $user, array $input): void
    {
        Validator::make($input, array_merge([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($user->id),
            ],
        ], config('ycsplayer.password_less') ? [] : [
            'current_password' => ['nullable', 'string', 'current_password:web'],
            'password' => ['required_with:current_password', 'nullable', 'string', Password::min(8), 'confirmed'],
        ]), [
            'current_password.current_password' => __('The provided password does not match your current password.'),
        ])->validate();

        $user->name = $input['name'];
        $user->email = $input['email'];

        if (! config('ycsplayer.password_less') && $input['password']) {
            $user->password = $input['password'];
        }

        $user->save();

        if ($input['email'] !== $user->email && $user instanceof MustVerifyEmail) {
            $user->sendEmailVerificationNotification();
        }

        Flash::success('帳號設定更新成功');
    }
}
