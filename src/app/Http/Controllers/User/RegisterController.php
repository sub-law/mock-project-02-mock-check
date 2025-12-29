<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Events\Registered;

class RegisterController extends Controller
{
    public function show()
    {
        return view('user.register');
    }
    public function register(RegisterRequest $request)
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role' => User::ROLE_USER,
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect()->route('verification.notice');
    }

}
