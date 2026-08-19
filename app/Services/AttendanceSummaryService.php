<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\AttendanceRecord;
use App\Models\Employee;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;


class AttendanceSummaryService
{
    /**
     * @param array{employee_id?: int|string, status?: string, date?: string, date_from?: string, date_to?: string, month?: string, year?: int|string} $filters
     * @return Collection<int, object>
     */
    public function summarize(array $filters = []): Collection
    {
        $computed = $this->computeFromEvents($filters);
        $overrides = $this->loadOverrides($filters);

        $merged = $computed->keyBy('id');

        foreach ($overrides as $override) {
            $merged->put($override->id, $override);
        }

        $summaries = $merged->values();

        if (!empty($filters['status'])) {
            $summaries = $summaries->where('status', $filters['status'])->values();
        }

        return $summaries->sortByDesc('attendance_date')->values();
    }

    /**
     * Finds a single day's summary for one employee, or null if they have
     * no scan events and no manual correction that day.
     */
    public function forEmployeeOnDate(int $employeeId, string $date): ?object
    {
        return $this->summarize(['employee_id' => $employeeId, 'date' => $date])->first();
    }

    /**
     * Creates or updates the admin-entered correction for one employee's
     * day, superseding whatever the raw scan events say for that date.
     */
    public function upsertOverride(
        Employee $employee,
        string $date,
        ?string $checkInTime,
        ?string $checkOutTime,
        ?int $attendancePointId,
        ?string $note
    ): AttendanceRecord {
        $checkInAt = $checkInTime ? Carbon::parse($date . ' ' . $checkInTime) : null;
        [$status, $lateMinutes] = $this->resolveStatus($employee, $checkInAt);

        return AttendanceRecord::updateOrCreate(
            ['employee_id' => $employee->id, 'attendance_date' => $date],
            [
                'attendance_point_id' => $attendancePointId,
                'check_in_time' => $checkInTime,
                'check_out_time' => $checkOutTime,
                'status' => $status,
                'late_minutes' => $lateMinutes,
                'note' => $note,
            ]
        );
    }

    /**
     * @return Collection<int, object>
     */
    private function computeFromEvents(array $filters): Collection
    {
        $query = Attendance::query()->with(['user.employee.department', 'user.employee.shift', 'attendancePoint']);

        if (!empty($filters['employee_id'])) {
            $employee = Employee::find($filters['employee_id']);
            $query->where('user_id', $employee?->user_id ?? 0);
        }

        if (!empty($filters['date'])) {
            $query->whereDate('scanned_at', $filters['date']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('scanned_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('scanned_at', '<=', $filters['date_to']);
        }

        if (!empty($filters['month'])) {
            $parts = explode('-', $filters['month']);
            if (count($parts) === 2) {
                $query->whereYear('scanned_at', $parts[0])->whereMonth('scanned_at', $parts[1]);
            }
        }

        if (!empty($filters['year'])) {
            $query->whereYear('scanned_at', $filters['year']);
        }

        $events = $query->orderBy('scanned_at')->get()
            ->filter(fn ($event) => $event->user?->employee);

        return $events
            ->groupBy(fn ($event) => $event->user->employee->id . '|' . $event->scanned_at->toDateString())
            ->map(function (Collection $dayEvents) {
                $first = $dayEvents->first();
                $employee = $first->user->employee;

                $checkIn = $dayEvents->firstWhere('type', 'in');
                $checkOut = $dayEvents->where('type', 'out')->last();

                [$status, $lateMinutes] = $this->resolveStatus($employee, $checkIn?->scanned_at);

                return (object) [
                    'id' => $employee->id . '|' . $first->scanned_at->toDateString(),
                    'employee_id' => $employee->id,
                    'employee' => $employee,
                    'attendance_date' => $first->scanned_at->toDateString(),
                    'check_in_time' => $checkIn?->scanned_at->format('H:i:s'),
                    'check_out_time' => $checkOut?->scanned_at->format('H:i:s'),
                    'attendancePoint' => ($checkIn ?? $first)->attendancePoint,
                    'status' => $status,
                    'late_minutes' => $lateMinutes,
                    'note' => null,
                    'is_override' => false,
                    'created_at' => $first->scanned_at,
                ];
            })
            ->values();
    }

    /**
     * @return Collection<int, object>
     */
    private function loadOverrides(array $filters): Collection
    {
        $query = AttendanceRecord::with(['employee.department', 'employee.shift', 'attendancePoint']);

        if (!empty($filters['employee_id'])) {
            $query->where('employee_id', $filters['employee_id']);
        }

        if (!empty($filters['date'])) {
            $query->whereDate('attendance_date', $filters['date']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('attendance_date', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('attendance_date', '<=', $filters['date_to']);
        }

        if (!empty($filters['month'])) {
            $parts = explode('-', $filters['month']);
            if (count($parts) === 2) {
                $query->whereYear('attendance_date', $parts[0])->whereMonth('attendance_date', $parts[1]);
            }
        }

        if (!empty($filters['year'])) {
            $query->whereYear('attendance_date', $filters['year']);
        }

        return $query->get()
            ->filter(fn ($record) => $record->employee)
            ->map(function (AttendanceRecord $record) {
                $date = Carbon::parse($record->attendance_date)->toDateString();

                return (object) [
                    'id' => $record->employee_id . '|' . $date,
                    'employee_id' => $record->employee_id,
                    'employee' => $record->employee,
                    'attendance_date' => $date,
                    'check_in_time' => $record->check_in_time,
                    'check_out_time' => $record->check_out_time,
                    'attendancePoint' => $record->attendancePoint,
                    'status' => $record->status,
                    'late_minutes' => $record->late_minutes,
                    'note' => $record->note,
                    'is_override' => true,
                    'created_at' => Carbon::parse($record->attendance_date . ' ' . ($record->check_in_time ?? '00:00:00')),
                ];
            })
            ->values();
    }

    /**
     * @return array{0: string, 1: int} [status, late_minutes]
     */
    private function resolveStatus(Employee $employee, ?Carbon $checkInAt): array
    {
        if (!$checkInAt) {
            return ['present', 0];
        }

        $lateAfter = $employee->shift?->late_after;

        if (!$lateAfter) {
            return ['present', 0];
        }

        $threshold = Carbon::parse($checkInAt->toDateString() . ' ' . $lateAfter);

        if ($checkInAt->gt($threshold)) {
            return ['late', $threshold->diffInMinutes($checkInAt)];
        }

        return ['present', 0];
    }
}
