<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EmailVerificationNotificationController extends Controller
{
    public function store(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('attendance');
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('message', '認証メールを再送しました。');
    }
}
