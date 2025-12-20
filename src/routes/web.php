<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Admin\AdminLoginController;
use App\Http\Controllers\Admin\AdminAttendanceController;

use App\Http\Controllers\User\LoginController;
use App\Http\Controllers\User\RegisterController;

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

//Route::get('/', function () {
//  return view('layouts/app');
//});

//Route::get('/admin', function () {
//    return view('layouts/app_admin');
//});

//Route::get('/user', function () {
//    return view('layouts/app_user');
//});

// 管理者ログイン
Route::get('/admin/login',  [AdminLoginController::class, 'showLoginForm'])->name('admin.login');
Route::post('/admin/login', [AdminLoginController::class, 'login'])->name('admin.login');
Route::post('/admin/logout', [AdminLoginController::class, 'logout'])->name('admin.logout');

// 管理者専用ページ
Route::middleware('admin')->group(function () {
    Route::get('/admin/attendance/list', [AdminAttendanceController::class, 'index'])
        ->name('admin.attendance.list');
});

//　一般ユーザー新規登録
Route::get('/register', [RegisterController::class, 'show'])->name('register');
Route::post('/register', [RegisterController::class, 'register'])->name('register.post');

//一般ユーザーログイン
Route::get('/login', [LoginController::class, 'showloginform'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

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
