<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\Employee;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $today = now()->toDateString();

        $totalEmployees = Employee::where('status', 'active')->count();

        $presentToday = AttendanceRecord::where('attendance_date', $today)->count();

        $lateToday = AttendanceRecord::where('attendance_date', $today)
            ->where('status', 'late')
            ->count();

        $absentToday = max($totalEmployees - $presentToday, 0);

        $todayRecords = AttendanceRecord::with(['employee.department', 'attendancePoint'])
            ->where('attendance_date', $today)
            ->latest()
            ->get();

        return view('admin.dashboard', compact(
            'totalEmployees',
            'presentToday',
            'lateToday',
            'absentToday',
            'todayRecords'
        ));
    }
}
