<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Admin\AdminLoginController;
use App\Http\Controllers\Admin\AdminAttendanceController;
use App\Http\Controllers\Admin\AdminStaffController;
use App\Http\Controllers\User\LoginController;
use App\Http\Controllers\User\RegisterController;
use App\Http\Controllers\User\VerifyEmailController;
use App\Http\Controllers\User\AttendanceListController;
use App\Http\Controllers\User\EmailVerificationNotificationController;
use App\Http\Controllers\User\AttendanceController;
use App\Http\Controllers\StampCorrectionRequestController;


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

Route::middleware('auth')->group(function () {

    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/attendance/clock-in', [AttendanceController::class, 'clockIn'])->name('attendance.clockIn');
    Route::post('/attendance/clock-out', [AttendanceController::class, 'clockOut'])->name('attendance.clockOut');
    Route::post('/attendance/break-in', [AttendanceController::class, 'breakIn'])->name('attendance.breakIn');
    Route::post('/attendance/break-out', [AttendanceController::class, 'breakOut'])->name('attendance.breakOut');

    Route::get('/attendance/list', [AttendanceListController::class, 'index'])->name('attendance.list');
    Route::get('/attendance/detail/{id}', [AttendanceController::class, 'detail'])->name('attendance.detail');

    Route::post('/attendance/{attendanceId}/correction', [StampCorrectionRequestController::class, 'store'])
        ->name('correction.store');
});



// 管理者側ルート
// 管理者ログイン
Route::get('/admin/login',  [AdminLoginController::class, 'showLoginForm'])->name('admin.login');
Route::post('/admin/login', [AdminLoginController::class, 'login'])->name('admin.login');
Route::post('/admin/logout', [AdminLoginController::class, 'logout'])->name('admin.logout');

// 管理者用トップ画面
Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {

    Route::get('/attendance/list', [AdminAttendanceController::class, 'index'])
        ->name('attendance.list');

    // 勤怠詳細 
    Route::get('/attendance/{id}', [AdminAttendanceController::class, 'detail'])->name('attendance.detail');

    // スタッフ一覧 
    Route::get('/staff/list', [AdminStaffController::class, 'index'])->name('staff.list');

    // スタッフ別勤怠一覧 
    Route::get('/attendance/staff/{id}/',[AdminAttendanceController::class, 'staffAttendance']) ->name('staff.attendance');
        
    //Route::get('/stamp_correction_request/approve/{attendance_correct_request_id}', function () {
    //    return view('admin.stamp_correction_request_approve');
    //})->name('stamp_correction.request.approve');

});


// 一般ユーザー用・管理者共通、後程修正
Route::get('/stamp_correction_request_list', function () {
    return view('common.stamp_correction_request_list');
})->name('stamp.correction.request.list');