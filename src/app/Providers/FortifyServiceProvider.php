<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    public function register()
    {

    }
    public function boot()
    {
        Fortify::ignoreRoutes();

        Fortify::createUsersUsing(CreateNewUser::class);

        Fortify::authenticateUsing(function (Request $request) {
            $user = User::query()
                ->where('email', $request->input('email'))
                ->first();
            if (!$user || !Hash::check((string) $request->input('password'), (string) $user->password)) {
                throw ValidationException::withMessages([
                    Fortify::username() => ['メールアドレスまたはパスワードが正しくありません。'],
                ]);
            }

            if (method_exists($user, 'hasVerifiedEmail') && !$user->hasVerifiedEmail()) {
                throw ValidationException::withMessages([
                    Fortify::username() => ['メール認証が完了していません。認証メールを確認してください。'],
                ]);
            }

            return $user;
        });
    }
}