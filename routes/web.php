<?php

use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\ShiftController;
use App\Http\Controllers\Admin\AttendancePointController;
use App\Http\Controllers\Admin\AttendanceController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Employee\EmployeeDashboardController;
use App\Http\Controllers\Employee\EmployeeAttendanceController;
use App\Http\Controllers\Employee\EmployeeProfileController;
use App\Http\Controllers\PublicFileController;


Route::get('/', function () {
    return redirect()->route('login');
});

// Serves the public disk in place of the public/files symlink - Hostinger's
// PHP config has symlink() in disable_functions, so `storage:link` can never
// succeed on this host (not just "hasn't been run yet").
Route::get('/files/{path}', [PublicFileController::class, 'show'])
    ->where('path', '.*')
    ->name('files.show');

require __DIR__ . '/auth.php';

Route::get('/dashboard', function () {
    return redirect()->route(auth()->user()->role === 'admin' ? 'admin.dashboard' : 'employee.dashboard');
})->middleware('auth')->name('dashboard');
Route::get('/profile', function () {
    return redirect()->route(auth()->user()->role === 'admin' ? 'admin.dashboard' : 'employee.profile');
})->middleware('auth')->name('profile.edit');


Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        // Dashboard
        Route::get('/dashboard', [App\Http\Controllers\Admin\DashboardController::class, 'index'])
            ->name('dashboard');

        // Employees
        Route::resource('employees', App\Http\Controllers\Admin\EmployeeController::class);

        // Manual attendance correction for one employee's day
        Route::post('/employees/{employee}/attendance', [App\Http\Controllers\Admin\EmployeeController::class, 'updateAttendance'])
            ->name('employees.attendance.update');

        // Departments
        Route::resource('departments', App\Http\Controllers\Admin\DepartmentController::class)->except('show');

        // Shifts
        Route::resource('shifts', App\Http\Controllers\Admin\ShiftController::class)->except('show');

        // Attendance Points
        Route::resource('attendance-points', App\Http\Controllers\Admin\AttendancePointController::class)->except('show');

        // Attendance Records
        // Custom (not resourceful) because there's no real numeric id to
        // bind to anymore - "records" are aggregated on the fly from raw
        // scan events, keyed by employee + date instead.
        Route::get('/attendances', [App\Http\Controllers\Admin\AttendanceController::class, 'index'])
            ->name('attendances.index');
        Route::get('/attendances/{employee}/{date}', [App\Http\Controllers\Admin\AttendanceController::class, 'show'])
            ->name('attendances.show');

        // Attendance History
        Route::get('/attendance-history', [App\Http\Controllers\Admin\AttendanceReportController::class, 'index'])
            ->name('attendance.history');
    });


Route::middleware(['auth', 'role:employee'])
    ->prefix('employee')
    ->name('employee.')
    ->group(function () {

        Route::get('/dashboard', [EmployeeDashboardController::class, 'index'])
            ->name('dashboard');

        Route::get('/attendance', [EmployeeAttendanceController::class, 'checkinForm'])
            ->name('attendance.index');

        Route::get('/attendance/checkin', [EmployeeAttendanceController::class, 'checkinForm'])
            ->name('attendance.checkin.form');

        // Each submission validates GPS coordinates on the server.
        Route::post('/attendance/checkin', [EmployeeAttendanceController::class, 'checkin'])->name('attendance.checkin');
        Route::post('/attendance/checkout', [EmployeeAttendanceController::class, 'checkout'])->name('attendance.checkout');

        // New: success page after a check-in/out
        Route::get('/attendance/success', [EmployeeAttendanceController::class, 'success'])
            ->name('attendance.success');

        Route::get('/attendance/history', [EmployeeAttendanceController::class, 'history'])
            ->name('attendance.history');

        Route::get('/profile', [EmployeeProfileController::class, 'index'])
            ->name('profile');
    });

Route::get('/attendance/{token}', [EmployeeAttendanceController::class, 'login'])
    ->name('attendance.show');

// Public QR Scanner
Route::get('/attendance/scan/qr', [EmployeeAttendanceController::class, 'scan'])
    ->middleware(['auth', 'role:employee'])
    ->name('attendance.show.qr');

Route::post('/attendance/{token}', [EmployeeAttendanceController::class, 'submit'])
    ->middleware(['auth', 'role:employee'])
    ->name('attendance.submit');
