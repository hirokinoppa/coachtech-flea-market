<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureProfileIsComplete
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        $isComplete =
            !empty($user->name) &&
            !empty($user->postal_code) &&
            !empty($user->address);

        $isProfileRoute =
            $request->routeIs('profile.edit') ||
            $request->routeIs('mypage.update') ||
            $request->routeIs('logout');

        if (!$isComplete && !$isProfileRoute) {
            if (!$request->session()->has('url.intended')) {
                $request->session()->put('url.intended', $request->fullUrl());
            }

            return redirect()->route('profile.edit');
        }

        return $next($request);
    }
}