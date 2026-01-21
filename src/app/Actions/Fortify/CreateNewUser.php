<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    private const PASSWORD_MIN_LENGTH = 8;

    public function create(array $input): User
    {
        $this->validate($input);

        return User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => Hash::make($input['password']),
        ]);
    }

    private function validate(array $input): void
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique(User::class, 'email')],

            'password' => ['required', 'string', 'min:' . self::PASSWORD_MIN_LENGTH],
            'password_confirmation' => ['required', 'string', 'min:' . self::PASSWORD_MIN_LENGTH, 'same:password'],
        ]
        )->validate();
    }
}