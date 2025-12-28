<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

class VerifyEmailController extends Controller
{
    public function __invoke(EmailVerificationRequest $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('attendance.index')
                ->with('message', 'すでに認証済みです');
        }

        $request->fulfill();

        return redirect()->route('attendance.index')
            ->with('message', 'メール認証が完了しました');
    }
}
