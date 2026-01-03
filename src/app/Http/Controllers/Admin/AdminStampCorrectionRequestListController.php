<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminStampCorrectionRequestListController extends Controller
{
    public function index()
    {
        return view('admin.stamp_correction_request_list');
    }
}
