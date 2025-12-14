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

Route::get('/attendance/detail/{id}', function ($id) {
    return view('user.attendance_detail', [
        'name' => '西 伶奈',
        'date' => '2023年6月1日',
        'start_time' => '09:00',
        'end_time' => '18:00',
        'break1' => '12:00',
        'break2' => null,
        'note' => '電車遅延のため',
    ]);
})->name('attendance.detail');

// 一般ユーザー用・管理者共通、後程修正
Route::get('/stamp_correction_request_list', function () {
    return view('stamp_correction_request_list');
})->name('stamp.correction.request.list');

Route::get('/admin/login', function () {
    return view('admin.admin_login');
})->name('admin.login');

Route::get('/admin/attendance/list', function () {
    return view('admin_attendance_list');
})->name('admin.attendance.list');

Route::get('/admin/attendance/{id}', function ($id) {
    return view('admin.admin_attendance_detail', [
        'name' => '西 伶奈',
        'date' => '2023年6月1日',
        'start_time' => '09:00',
        'end_time' => '18:00',
        'break1' => '12:00',
        'break2' => null,
        'note' => '電車遅延のため',
    ]);
})->name('admin.attendance.detail');

Route::get('/admin/staff/list', function () {
    return view('admin.admin_staff_list');
})->name('admin.staff.list');

Route::get('/admin/attendance/staff/{id}', function ($id) {
    $records = [
        [
            'date' => '2023-06-01',
            'start_time' => '09:00',
            'end_time' => '18:00',
            'break_time' => '1:00',
            'total_time' => '8:00',
        ],
        [
            'date' => '2023-06-02',
            'start_time' => '09:00',
            'end_time' => '18:00',
            'break_time' => '1:00',
            'total_time' => '8:00',
        ],
        [
            'date' => '2023-06-03',
            'start_time' => null,
            'end_time' => null,
            'break_time' => null,
            'total_time' => null,
        ],
        // ...必要な日数分追加
    ];

    return view('admin.admin_attendance_staff', [
        'staff_name' => '西 伶奈',
        'records' => $records,
    ]);
})->name('admin.attendance.staff');

Route::get('/stamp_correction_request/approve/{attendance_correct_request_id}', function ($id) {
    return view('admin.stamp_correction_request_approve', [
        'id' => $id,
        'name' => '西 伶奈',
        'date' => '2023年6月1日',
        'start_time' => '09:00',
        'end_time' => '18:00',
        'break1' => '12:00',
        'break2' => null,
        'note' => '電車遅延のため ',
    ]);
    })->name('stamp.correction.request.approve');
