<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Auth;

class AdminLoginController extends Controller
{
    public function showLoginForm()
    {
        return view('admin.admin_login');
    }

    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();

        // ★ 管理者用 guard を使う
        if (Auth::guard('admin')->attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->route('admin.attendance.list');
        }

        return back()->withErrors([
            'email' => 'ログイン情報が登録されていません',
        ]);
    }

    public function logout()
    {
        // ★ 管理者用 guard をログアウト
        Auth::guard('admin')->logout();

        return redirect('/admin/login')->with('status', 'ログアウトしました');
    }
}
