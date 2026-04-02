<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceReportController extends Controller
{
    public function index(Request $request): View
    {
        $employees = Employee::where('status', 'active')
            ->orderBy('full_name')
            ->get();

        $query = AttendanceRecord::with(['employee.department', 'attendancePoint']);

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->filled('date')) {
            $query->whereDate('attendance_date', $request->date);
        }

        if ($request->filled('month')) {
            $monthParts = explode('-', $request->month);
            if (count($monthParts) === 2) {
                $query->whereYear('attendance_date', $monthParts[0])
                      ->whereMonth('attendance_date', $monthParts[1]);
            }
        }

        if ($request->filled('year')) {
            $query->whereYear('attendance_date', $request->year);
        }

        $records = $query->latest('attendance_date')->get();

        $totalRecords = $records->count();
        $presentCount = $records->where('status', 'present')->count();
        $lateCount = $records->where('status', 'late')->count();
        $totalLateMinutes = $records->sum('late_minutes');

        return view('admin.reports.attendance', compact(
            'employees',
            'records',
            'totalRecords',
            'presentCount',
            'lateCount',
            'totalLateMinutes'
        ));
    }
}
