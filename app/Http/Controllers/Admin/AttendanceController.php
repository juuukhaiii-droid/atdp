<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Employee;
use App\Services\AttendanceSummaryService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class AttendanceController extends Controller
{
    public function index(Request $request, AttendanceSummaryService $attendanceSummary)
    {
        $filters = $request->only(['employee_id', 'status', 'date_from', 'date_to']);
        $request->validate([
            'employee_id' => ['nullable', 'integer', 'exists:employees,id'],
            'status' => ['nullable', 'in:present,late,absent'],
            'date_from' => ['nullable', 'date_format:Y-m-d'],
            'date_to' => ['nullable', 'date_format:Y-m-d', \Illuminate\Validation\Rule::when($request->filled('date_from'), 'after_or_equal:date_from')],
        ]);

        $all = $attendanceSummary->summarize($filters);

        $perPage = 20;
        $page = LengthAwarePaginator::resolveCurrentPage();

        $records = new LengthAwarePaginator(
            $all->forPage($page, $perPage)->values(),
            $all->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $employees = Employee::orderBy('full_name')->get();
        return view('admin.attendances.index', compact('records', 'employees'));
    }

    public function show(int $employee, string $date, AttendanceSummaryService $attendanceSummary)
    {
        $attendance = $attendanceSummary->forEmployeeOnDate($employee, $date);

        if (!$attendance) {
            abort(404, 'No attendance found for that employee on that date.');
        }

        $attendance->employee->load('department', 'shift');

        [$start, $end] = app(\App\Services\AttendanceWorkday::class)->bounds($attendance->employee, $date);
        $locationEvents = Attendance::with('attendancePoint')
            ->where('user_id', $attendance->employee->user_id)
            ->where('scanned_at', '>=', $start)->where('scanned_at', '<', $end)
            ->orderBy('scanned_at')->orderBy('id')->get();

        return view('admin.attendances.show', compact('attendance', 'locationEvents'));
    }
}
