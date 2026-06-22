<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;

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

Route::get('/', function () {
    return redirect('/login');
});

Route::get('/admin/login', function () {
    return view('admin.login');
});


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