<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class EnsureProfileIsComplete
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        $profile = $user->profile;

        $isComplete =
            $profile &&
            !empty($profile->name) &&
            !empty($profile->postal_code) &&
            !empty($profile->address);

        $isProfileRoute = false;
        if ($request->route()) {
            $isProfileRoute =
                $request->routeIs('profile.edit') ||
                $request->routeIs('profile.update') ||
                $request->routeIs('logout');
        }

        if (!$isComplete && !$isProfileRoute) {
            if (!$request->session()->has('url.intended')) {
                Redirect::setIntendedUrl($request->fullUrl());
            }

            return redirect()->route('profile.edit');
        }

        return $next($request);
    }
}