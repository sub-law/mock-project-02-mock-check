<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\StampCorrectionRequest;

class AdminStampCorrectionRequestListController extends Controller
{
    public function index()
    {
        $pending = StampCorrectionRequest::with('user')
            ->where('status', StampCorrectionRequest::STATUS_PENDING)
            ->orderBy('created_at', 'desc')
            ->get();

        $approved = StampCorrectionRequest::with('user')
            ->where('status', StampCorrectionRequest::STATUS_APPROVED)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.stamp_correction_request_list', compact('pending', 'approved'));
    }

    public function detail()
    {
        return view('admin.stamp_correction_request_list', compact('pending', 'approved'));
    }

    public function approve()
    {
        return view('admin.stamp_correction_request_list', compact('pending', 'approved'));
    }
}
