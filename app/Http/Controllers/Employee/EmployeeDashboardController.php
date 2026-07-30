<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\AttendanceRecord as Attendance;

class EmployeeDashboardController extends Controller
{
    public function index()
    {
        $employee = Auth::user()->employee;

        if (!$employee) {
            abort(403, 'Employee record not found for this user.');
        }

        $todayAttendance = Attendance::where('employee_id', $employee->id)
            ->whereDate('created_at', today())
            ->first();

        $totalAttendance = Attendance::where('employee_id', $employee->id)->count();

        $presentDays = Attendance::where('employee_id', $employee->id)
            ->whereMonth('created_at', now()->month)
            ->count();

        $lateDays = Attendance::where('employee_id', $employee->id)
            ->where('status', 'late')
            ->whereMonth('created_at', now()->month)
            ->count();

        return view('employee.dashboard', compact(
            'todayAttendance',
            'totalAttendance',
            'presentDays',
            'lateDays'
        ));
    }
}
