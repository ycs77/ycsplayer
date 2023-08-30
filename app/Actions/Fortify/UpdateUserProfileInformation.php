<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;
use Laravel\Fortify\Rules\Password;

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
            'password' => ['required_with:current_password', 'nullable', 'string', new Password, 'confirmed'],
        ]), [
            'current_password.current_password' => __('The provided password does not match your current password.'),
        ])->validate();

        if ($input['email'] !== $user->email &&
            $user instanceof MustVerifyEmail) {
            $this->updateVerifiedUser($user, $input);
        } else {
            $user->forceFill(array_merge([
                'name' => $input['name'],
                'email' => $input['email'],
            ], ! config('ycsplayer.password_less') && $input['password'] ? [
                'password' => Hash::make($input['password']),
            ] : []))->save();
        }
    }

    /**
     * Update the given verified user's profile information.
     *
     * @param  array<string, string>  $input
     */
    protected function updateVerifiedUser(User $user, array $input): void
    {
        $user->forceFill(array_merge([
            'name' => $input['name'],
            'email' => $input['email'],
            'email_verified_at' => null,
        ], ! config('ycsplayer.password_less') && $input['password'] ? [
            'password' => Hash::make($input['password']),
        ] : []))->save();

        $user->sendEmailVerificationNotification();
    }
}
