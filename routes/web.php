<?php

use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\ShiftController;
use App\Http\Controllers\Admin\AttendancePointController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\AttendanceController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AttendanceReportController;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/attendance/{token}', [AttendanceController::class, 'show'])->name('attendance.show');
Route::post('/attendance/{token}', [AttendanceController::class, 'submit'])->name('attendance.submit');
Route::get('/reports/attendance', [AttendanceReportController::class, 'index'])->name('admin.reports.attendance');
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('departments', DepartmentController::class);
    Route::resource('shifts', ShiftController::class);
    Route::resource('employees', EmployeeController::class);
    Route::resource('attendance-points', AttendancePointController::class);
        
});

require __DIR__.'/auth.php';
