<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdminLoginRequest;
use Illuminate\Support\Facades\Auth;

class AdminAuthController extends Controller
{
    public function login(AdminLoginRequest $request)
    {
        $credentials = $request->only('email', 'password');

        if (! Auth::attempt($credentials)) {
            return back()->withErrors([
                'email' => 'ログイン情報が登録されていません',
            ]);
        }

        if (! Auth::user()->admin_status) {
            Auth::logout();

            return back()->withErrors([
                'email' => 'ログイン情報が登録されていません',
            ]);
        }
        $request->session()->regenerate();

        return redirect()->route('admin.attendance.list');
    }
}
