<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

class UserStampCorrectionRequestListController extends Controller
{
    public function index()
    {
        return view('user.stamp_correction_request_list');
    }
}
