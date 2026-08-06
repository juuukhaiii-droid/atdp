<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Services\AttendanceSummaryService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceReportController extends Controller
{
    public function index(Request $request, AttendanceSummaryService $attendanceSummary): View
    {
        $employees = Employee::where('status', 'active')
            ->orderBy('full_name')
            ->get();

        $filters = $request->only(['employee_id', 'date', 'month', 'year']);

        $records = $attendanceSummary->summarize($filters);

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
