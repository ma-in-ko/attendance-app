<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AdminAttendanceController;
use App\Http\Controllers\AdminStaffController;
use App\Http\Controllers\AdminAttendanceCorrectionRequestController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/


Route::get('/admin/login', function () {
    return view('admin.login');
});

Route::post('/admin/login', [AdminAuthController::class, 'login'])
->name('admin.login');

Route::middleware('auth')->group(function (){

    Route::get('/attendance', [AttendanceController::class, 'create'])->name('attendance.create');

    Route::post('/attendance/clock-in', [AttendanceController::class, 'clockIn'])->name('attendance.clock-in');

    Route::post('/attendance/clock-out', [AttendanceController::class, 'clockOut'])->name('attendance.clock-out');

    Route::post('/attendance/break-start', [AttendanceController::class, 'breakStart'])->name('attendance.break-start');

    Route::post('/attendance/break-end', [AttendanceController::class, 'breakEnd'])->name('attendance.break-end');

    Route::get('/attendance/list', [AttendanceController::class, 'index'])->name('attendance.index');

    Route::get('/attendance/detail/{attendance}', [AttendanceController::class, 'show'])->name('attendance.show');

    Route::post('/attendance/{attendance}/request',[AttendanceController::class, 'store'])->name('attendance.request');

    });

Route::middleware(['auth', 'admin'])->group(function () {

    Route::get('/admin/attendance/list', [AdminAttendanceController::class, 'index'])->name('admin.attendance.list');

    Route::get('/admin/attendance/{attendance}', [AdminAttendanceController::class, 'detail'])
    ->name('admin.attendance.detail');

    Route::put('/admin/attendance/{attendance}', [AdminAttendanceController::class, 'update'])
        ->name('admin.attendance.update');

    Route::get('/admin/staff/list', [AdminStaffController::class, 'index'])
    ->name('admin.staff.list');

    Route::get('/admin/attendance/staff/{user}', [AdminStaffController::class, 'attendance'])
    ->name('admin.staff.attendance');

    Route::get('/stamp_correction_request/list', [AdminAttendanceCorrectionRequestController::class, 'index'])
    ->name('admin.request.index');

    Route::get('/stamp_correction_request/approve/{id}', [AdminAttendanceCorrectionRequestController::class, 'show'])
    ->name('admin.request.show');

    Route::post('/stamp_correction_request/approve/{id}', [AdminAttendanceCorrectionRequestController::class, 'approve'])
    ->name('admin.request.approve');
});