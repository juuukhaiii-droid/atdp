<?php

namespace App\Services;

use App\Models\Employee;
use Illuminate\Support\Carbon;

class AttendanceWorkday
{
    private function cutoffSeconds(Employee $employee): int
    {
        if ($employee->attendance_mode === 'flexible') return 0;
        $shift = $employee->shift;
        if (!$shift || $shift->end_time >= $shift->start_time) {
            return 0;
        }
        $seconds = fn (string $time) => Carbon::parse($time)->secondsSinceMidnight();
        // An overnight workday rolls over halfway between shift end and next start.
        // For 18:00–06:00 this is noon, allowing normal post-midnight overtime.
        return (int) floor(($seconds($shift->end_time) + $seconds($shift->start_time)) / 2);
    }

    public function date(Employee $employee, Carbon $time): string
    {
        return $time->copy()->subSeconds($this->cutoffSeconds($employee))->toDateString();
    }

    public function bounds(Employee $employee, string $date): array
    {
        $start = Carbon::parse($date)->startOfDay()->addSeconds($this->cutoffSeconds($employee));
        return [$start, $start->copy()->addDay()];
    }
}
