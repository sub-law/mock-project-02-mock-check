<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class AdminStaffController extends Controller
{
    public function index()
    {
        // 一般ユーザーのみ取得（role = 0）
        $users = User::where('role', 0)
            ->orderBy('id', 'asc')
            ->get();

        return view('admin.admin_staff_list', compact('users'));
    }
}
