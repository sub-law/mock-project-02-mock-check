<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('layouts/app');
});

Route::get('/admin', function () {
    return view('layouts/app_admin');
});

Route::get('/user', function () {
    return view('layouts/app_user');
});

Route::get('/register', function () {
    return view('user.register');
})->name('register');


Route::get('/login', function () {
    return view('user.user_login');
})->name('login');

Route::get('/verify-email', function () {
    return view('user.verify');
    })->name('verification.verify');

Route::get('/attendance', function () {
    return view('user.attendance');
})->name('attendance');

Route::get('/attendance/list', function () {
    return view('user.attendance_list');
})->name('attendance.list');

Route::get('/attendance/detail/{id}', function () {
    return view('user.attendance_detail');
})->name('attendance.detail');

// 一般ユーザー用・管理者共通、後程修正
Route::get('/stamp_correction_request_list', function () {
    return view('common.stamp_correction_request_list');
})->name('stamp.correction.request.list');

Route::get('/admin/login', function () {
    return view('admin.admin_login');
})->name('admin.login');

Route::get('/admin/attendance/list', function () {
    return view('admin.admin_attendance_list');
})->name('admin.attendance.list');

Route::get('/admin/attendance/{id}', function () {
    return view('admin.attendance_detail');
})->name('admin.attendance.detail');

Route::get('/admin/staff/list', function () {
    return view('admin.admin_staff_list');
})->name('admin.staff.list');

Route::get('/admin/attendance/staff/{id}', function () {
    return view('admin.admin_attendance_staff');
})->name('admin.attendance.staff');

Route::get('/stamp_correction_request/approve/{attendance_correct_request_id}', function () {
    return view('admin.stamp_correction_request_approve');
    })->name('stamp.correction.request.approve');
