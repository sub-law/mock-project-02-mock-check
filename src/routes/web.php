<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Admin\AdminLoginController;
use App\Http\Controllers\Admin\AdminAttendanceController;
use App\Http\Controllers\User\LoginController;
use App\Http\Controllers\User\RegisterController;
use App\Http\Controllers\User\VerifyEmailController;
use App\Http\Controllers\User\AttendanceListController;
use App\Http\Controllers\User\EmailVerificationNotificationController;
use App\Http\Controllers\User\AttendanceController;



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

// 認証待ち画面
Route::get('/email/verify', function () {
    return view('user.verify');
})->middleware('auth')->name('verification.notice');

// 認証リンク
Route::get('/email/verify/{id}/{hash}', VerifyEmailController::class)
    ->middleware(['auth', 'signed'])
    ->name('verification.verify');

// 認証メール再送
Route::post(
    '/email/verification-notification',
    [EmailVerificationNotificationController::class, 'store']
)->middleware(['auth', 'throttle:6,1'])
    ->name('verification.send');

// 勤怠画面・出勤・退勤機能・休憩機能
Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
Route::post('/attendance/clock-in', [AttendanceController::class, 'clockIn'])->name('attendance.clockIn');
Route::post('/attendance/clock-out', [AttendanceController::class, 'clockOut'])
    ->name('attendance.clockOut');
Route::post('/attendance/break-in', [AttendanceController::class, 'breakIn'])
    ->name('attendance.breakIn');
Route::post('/attendance/break-out', [AttendanceController::class, 'breakOut'])
    ->name('attendance.breakOut');

//勤怠一覧画面(一般ユーザー)
Route::get('/attendance/list', [AttendanceListController::class, 'index'])
    ->name('attendance.list');


//勤怠詳細画面  

Route::get('/attendance/detail/{id}', [AttendanceController::class, 'detail'])
    ->name('attendance.detail');



//Route::get('/attendance/detail/{id}', function () {
//   return view('user.attendance_detail');
//})->name('attendance.detail');

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
