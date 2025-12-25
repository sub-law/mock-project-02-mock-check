<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('user.user_login');
    }

    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();

        $credentials['role'] = 0;

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->route('attendance.index');
        }

        return back()->withErrors([
            'email' => 'ログイン情報が登録されていません',
        ]);
    }

    public function logout()
    {
        Auth::logout();
        return redirect('/login')->with('status', 'ログアウトしました');
    }
}
