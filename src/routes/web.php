<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Admin\AdminLoginController;
use App\Http\Controllers\Admin\AdminAttendanceController;
use App\Http\Controllers\Admin\AdminStaffController;
use App\Http\Controllers\Admin\AdminStampCorrectionRequestListController;

use App\Http\Controllers\User\LoginController;
use App\Http\Controllers\User\RegisterController;
use App\Http\Controllers\User\VerifyEmailController;
use App\Http\Controllers\User\AttendanceListController;
use App\Http\Controllers\User\EmailVerificationNotificationController;
use App\Http\Controllers\User\AttendanceController;
use App\Http\Controllers\User\StampCorrectionRequestController;
use App\Http\Controllers\User\UserStampCorrectionRequestListController;

/*
|--------------------------------------------------------------------------
| 一般ユーザー（web）ルート
|--------------------------------------------------------------------------
*/

// 新規登録
Route::get('/register', [RegisterController::class, 'show'])->name('register');
Route::post('/register', [RegisterController::class, 'register'])->name('register.post');

// ログイン / ログアウト
Route::get('/login', [LoginController::class, 'showloginform'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// ログイン後（web guard）
Route::middleware('auth:web')->group(function () 
{

    // メール認証
    Route::get('/email/verify', fn() => view('user.verify'))
        ->name('verification.notice');

    Route::get('/email/verify/{id}/{hash}', VerifyEmailController::class)
        ->middleware('signed')
        ->name('verification.verify');

    Route::post('/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    // 勤怠
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/attendance/clock-in', [AttendanceController::class, 'clockIn'])->name('attendance.clockIn');
    Route::post('/attendance/clock-out', [AttendanceController::class, 'clockOut'])->name('attendance.clockOut');
    Route::post('/attendance/break-in', [AttendanceController::class, 'breakIn'])->name('attendance.breakIn');
    Route::post('/attendance/break-out', [AttendanceController::class, 'breakOut'])->name('attendance.breakOut');

    Route::get('/attendance/list', [AttendanceListController::class, 'index'])->name('attendance.list');
    Route::get('/attendance/detail/{id}', [AttendanceController::class, 'detail'])->name('attendance.detail');

    // 修正申請（store）
    Route::post('/attendance/{attendanceId}/correction', [StampCorrectionRequestController::class, 'store'])
        ->name('correction.store');

    Route::get('/stamp_correction_request_list', [UserStampCorrectionRequestListController::class, 'index'])->name('stamp.correction.request.list');
});


/*
|--------------------------------------------------------------------------
| 管理者（admin）ルート
|--------------------------------------------------------------------------
*/

// 管理者ログイン
Route::get('/admin/login', [AdminLoginController::class, 'showLoginForm'])->name('admin.login');
Route::post('/admin/login', [AdminLoginController::class, 'login'])->name('admin.login.post');
Route::post('/admin/logout', [AdminLoginController::class, 'logout'])->name('admin.logout');

// 管理者ログイン後
Route::prefix('admin')->name('admin.')->middleware('auth:admin')->group(function () 
{
    Route::get('/attendance/list', [AdminAttendanceController::class, 'index'])
        ->name('attendance.list');

    Route::get('/attendance/{id}', [AdminAttendanceController::class, 'detail'])
        ->name('attendance.detail');

    Route::get('/staff/list', [AdminStaffController::class, 'index'])
        ->name('staff.list');

    Route::get('/attendance/staff/{id}', [AdminAttendanceController::class, 'staffAttendance'])
        ->name('staff.attendance');

    Route::get(
        '/stamp_correction_request_list',
        [AdminStampCorrectionRequestListController::class, 'index']
    )->name('stamp.correction.request.list');


});


    // 承認処理は後で実装
    // Route::get('/stamp_correction_request/approve/{id}', ...)
//});

