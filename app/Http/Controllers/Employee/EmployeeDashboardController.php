<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;

class EmployeeDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $employee = $user->employee;

        if (!$employee) {
            abort(403, 'Employee record not found for this user.');
        }

        $todayIn = Attendance::where('user_id', $user->id)
            ->where('type', 'in')
            ->whereDate('scanned_at', today())
            ->oldest('scanned_at')
            ->first();

        $todayOut = Attendance::where('user_id', $user->id)
            ->where('type', 'out')
            ->whereDate('scanned_at', today())
            ->latest('scanned_at')
            ->first();

        $totalAttendance = Attendance::where('user_id', $user->id)
            ->where('type', 'in')
            ->count();

        $monthlyIns = Attendance::where('user_id', $user->id)
            ->where('type', 'in')
            ->whereYear('scanned_at', now()->year)
            ->whereMonth('scanned_at', now()->month)
            ->orderBy('scanned_at')
            ->get()
            ->unique(fn ($record) => $record->scanned_at->toDateString());

        $presentDays = $monthlyIns->count();

        $lateAfter = $employee->shift?->late_after;
        $lateDays = $lateAfter
            ? $monthlyIns->filter(fn ($record) => $record->scanned_at->format('H:i:s') > $lateAfter)->count()
            : 0;

        $recentActivity = Attendance::with('attendancePoint')
            ->where('user_id', $user->id)
            ->latest('scanned_at')
            ->take(5)
            ->get();

        return view('employee.dashboard', compact(
            'employee',
            'todayIn',
            'todayOut',
            'totalAttendance',
            'presentDays',
            'lateDays',
            'recentActivity'
        ));
    }
}
