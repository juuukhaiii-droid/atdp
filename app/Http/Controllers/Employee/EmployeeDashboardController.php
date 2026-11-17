<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Services\AttendanceSummaryService;
use App\Services\AttendanceWorkday;
use Illuminate\Support\Facades\Auth;

class EmployeeDashboardController extends Controller
{
    public function index(AttendanceSummaryService $summary)
    {
        $user = Auth::user();
        $employee = $user->employee;

        if (!$employee) {
            abort(403, 'Employee record not found for this user.');
        }

        $workday = app(AttendanceWorkday::class);
        $workDate = $workday->date($employee, now());
        [$start, $end] = $workday->bounds($employee, $workDate);

        $todayIn = Attendance::where('user_id', $user->id)
            ->where('type', 'in')
            ->where('scanned_at', '>=', $start)->where('scanned_at', '<', $end)
            ->oldest('scanned_at')
            ->first();

        $todayOut = Attendance::where('user_id', $user->id)
            ->where('type', 'out')
            ->where('scanned_at', '>=', $start)->where('scanned_at', '<', $end)
            ->latest('scanned_at')
            ->first();

        $summaries = $summary->summarize(['employee_id' => $employee->id]);
        $totalAttendance = $summaries->whereIn('status', ['present', 'late'])->count();
        $monthly = $summaries->filter(fn ($record) => str_starts_with($record->attendance_date, now()->format('Y-m')));
        $presentDays = $monthly->whereIn('status', ['present', 'late'])->count();
        $lateDays = $monthly->where('status', 'late')->count();
        $monthlyDays = $monthly->count();
        $todaySummary = $summaries->firstWhere('attendance_date', $workDate);
        if ($todaySummary?->is_override) {
            $todayIn = $todaySummary->check_in_time ? (object) ['scanned_at' => \Illuminate\Support\Carbon::parse($workDate . ' ' . $todaySummary->check_in_time)] : null;
            $todayOut = $todaySummary->check_out_time ? (object) ['scanned_at' => \Illuminate\Support\Carbon::parse($workDate . ' ' . $todaySummary->check_out_time)] : null;
        }

        if ($todayIn && $todayOut && $todayOut->scanned_at->lt($todayIn->scanned_at) && $employee->shift?->end_time < $employee->shift?->start_time) {
            $todayOut->scanned_at->addDay();
        }

        $recentActivity = $summaries->take(5);

        return view('employee.dashboard', compact(
            'employee',
            'todayIn',
            'todayOut',
            'totalAttendance',
            'presentDays',
            'lateDays',
            'monthlyDays',
            'recentActivity'
        ));
    }
}
