<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StampCorrectionRequestListController extends Controller
{
    public function index()
    {
        if (Auth::guard('admin')->check()) {
            return view('common.stamp_correction_request_list', [
                'role' => 'admin',
            ]);
        }

        if (Auth::guard('web')->check()) {
            return view('common.stamp_correction_request_list', [
                'role' => 'user',
            ]);
        }

        abort(403);
    }
}
