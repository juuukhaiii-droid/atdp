<?php

use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\ShiftController;
use App\Http\Controllers\Admin\AttendancePointController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\AttendanceController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Employee\EmployeeDashboardController;
use App\Http\Controllers\Employee\EmployeeAttendanceController;
use App\Http\Controllers\Employee\EmployeeProfileController;
use App\Http\Controllers\Employee\EmployeeReportController;
use App\Http\Middleware\EnsureOfficeNetwork;


Route::get('/', function () {
    return redirect()->route('login');
});

require __DIR__ . '/auth.php';


Route::middleware(['auth'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        // Dashboard
        Route::get('/dashboard', [App\Http\Controllers\Admin\DashboardController::class, 'index'])
            ->name('dashboard');

        // Employees
        Route::resource('employees', App\Http\Controllers\Admin\EmployeeController::class);

        // Departments
        Route::resource('departments', App\Http\Controllers\Admin\DepartmentController::class);

        // Shifts
        Route::resource('shifts', App\Http\Controllers\Admin\ShiftController::class);

        // Attendance Points
        Route::resource('attendance-points', App\Http\Controllers\Admin\AttendancePointController::class);

        // Attendance Records
        Route::resource('attendances', App\Http\Controllers\Admin\AttendanceController::class)
            ->only(['index', 'show']);

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

        Route::get('/attendance', [EmployeeAttendanceController::class, 'index'])
            ->name('attendance.index');

        Route::get('/attendance/checkin', [EmployeeAttendanceController::class, 'checkinForm'])
            ->name('attendance.checkin.form');

        // WiFi-gated: these two are the actual security boundary.
        Route::middleware(EnsureOfficeNetwork::class)->group(function () {
            Route::post('/attendance/checkin', [EmployeeAttendanceController::class, 'checkin'])
                ->name('attendance.checkin');

            Route::post('/attendance/checkout', [EmployeeAttendanceController::class, 'checkout'])
                ->name('attendance.checkout');
        });

        // New: success page after a check-in/out
        Route::get('/attendance/success', [EmployeeAttendanceController::class, 'success'])
            ->name('attendance.success');

        Route::get('/attendance/history', [EmployeeAttendanceController::class, 'history'])
            ->name('attendance.history');

        Route::get('/profile', [EmployeeProfileController::class, 'index'])
            ->name('profile');
    });

// WiFi Network Verification (for check-in)
Route::get('/api/verify-network', [EmployeeAttendanceController::class, 'verifyNetwork'])
    ->middleware('auth')
    ->name('api.verify-network');

Route::get('/attendance/{token}', [EmployeeAttendanceController::class, 'login'])
    ->name('attendance.show');

// Route::post('/attendance/{token}', [EmployeeAttendanceController::class, 'submit'])
//     ->name('attendance.submit');

// Public QR Scanner
Route::get('/attendance/scan/qr', [EmployeeAttendanceController::class, 'scan'])
    ->name('attendance.show.qr');

Route::post('/attendance/{token}', [EmployeeAttendanceController::class, 'submit'])
    ->name('attendance.submit');