<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileRequest;
use App\Models\Profile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(): View
    {
        $profile = Profile::firstOrNew([
            'user_id' => Auth::id(),
        ]);

        return view('auth.profile', compact('profile'));
    }

    public function update(ProfileRequest $request): RedirectResponse
    {
        $profile = Profile::firstOrNew([
            'user_id' => Auth::id(),
        ]);

        $data = $request->validated();

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('profile', 'public');
            $data['image_path'] = $path;
        }

        unset($data['image']);

        $profile->fill($data);
        $profile->user_id = Auth::id();
        $profile->save();

        return redirect()->route('item.index');
    }
}