<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Services\AttendanceSummaryService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request, AttendanceSummaryService $attendanceSummary): View
    {
        $selectedDate = $this->resolveDate($request->query('date'));

        $totalEmployees = Employee::where('status', 'active')->count();

        $daySummaries = $attendanceSummary->summarize(['date' => $selectedDate]);

        $presentToday = $daySummaries->count();

        $lateToday = $daySummaries->where('status', 'late')->count();

        $absentToday = max($totalEmployees - $presentToday, 0);

        $todayRecords = $daySummaries;

        return view('admin.dashboard', compact(
            'totalEmployees',
            'presentToday',
            'lateToday',
            'absentToday',
            'todayRecords',
            'selectedDate'
        ));
    }

    /**
     * Falls back to today whenever the query param is missing or isn't a
     * real date, rather than letting an invalid ?date= value 500 the page.
     */
    private function resolveDate(?string $date): string
    {
        if (!$date) {
            return now()->toDateString();
        }

        try {
            return Carbon::parse($date)->toDateString();
        } catch (\Throwable $e) {
            return now()->toDateString();
        }
    }
}
