<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;

class AdminStaffController extends Controller
{
    public function index()
    {
        $users = User::orderBy('id', 'asc')->get();

        return view('admin.admin_staff_list', compact('users'));
    }
}
