<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, array_merge([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class),
            ],
        ], config('ycsplayer.password_less') ? [] : [
            'password' => $this->passwordRules(),
        ]))->validate();

        return User::create(array_merge([
            'name' => $input['name'],
            'email' => $input['email'],
        ], config('ycsplayer.password_less') ? [
            'password' => Str::random(16),
        ] : [
            'password' => $input['password'],
        ]));
    }
}
