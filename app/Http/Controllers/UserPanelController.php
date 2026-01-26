<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserPanelController extends Controller
{
    public function index()
    {
        return view('user.panel');
    }

    public function uploadAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048'
        ]);

        $file = $request->file('avatar');
        $path = $file->store('avatars', 'public');

        $appAuth = session('app_auth', []);
        $appAuth['avatar'] = $path;
        session(['app_auth' => $appAuth]);

        return redirect()->route('user.panel')->with('status', 'Avatar actualizado');
    }
}
