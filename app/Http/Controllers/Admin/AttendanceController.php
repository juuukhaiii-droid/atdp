<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AttendanceSummaryService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class AttendanceController extends Controller
{
    public function index(Request $request, AttendanceSummaryService $attendanceSummary)
    {
        $filters = $request->only(['employee_id', 'status', 'date_from', 'date_to']);

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

        return view('admin.attendances.index', compact('records'));
    }

    public function show(int $employee, string $date, AttendanceSummaryService $attendanceSummary)
    {
        $attendance = $attendanceSummary->forEmployeeOnDate($employee, $date);

        if (!$attendance) {
            abort(404, 'No attendance found for that employee on that date.');
        }

        $attendance->employee->load('department', 'shift');

        return view('admin.attendances.show', compact('attendance'));
    }
}
