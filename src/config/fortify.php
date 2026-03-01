<?php

use App\Providers\RouteServiceProvider;
use Laravel\Fortify\Features;

return [

    'guard' => 'web',

    'passwords' => 'users',

    'username' => 'email',

    'email' => 'email',

    'lowercase_usernames' => true,

    'home' => RouteServiceProvider::HOME,

    'prefix' => '',

    'domain' => null,

    'middleware' => ['web'],

    'limiters' => [
        'login' => 'login',
        'two-factor' => 'two-factor',
    ],

    // Blade自作運用なら false のままでOK
    'views' => false,

    'features' => [
        Features::registration(),
        Features::resetPasswords(),

        // ✅ ここをON（メール認証機能）
        Features::emailVerification(),

        Features::updateProfileInformation(),
        Features::updatePasswords(),

        Features::twoFactorAuthentication([
            'confirm' => true,
            'confirmPassword' => true,
            // 'window' => 0,
        ]),
    ],

];