<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendancePoint;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Shift;
use App\Models\User;
use App\Services\AttendanceSummaryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class EmployeeController extends Controller
{
    public function index()
    {
        $employees = Employee::with(['department', 'shift'])->latest()->get();
        return view('admin.employees.index', compact('employees'));
    }

    public function create()
    {
        $departments = Department::all();
        $shifts = Shift::all();

        return view('admin.employees.create', compact('departments', 'shifts'));
    }

    public function store(Request $request)
{
    $request->mergeIfMissing(['attendance_mode' => 'fixed']);
    $validated = $request->validate([
        'employee_code' => ['required', 'string', 'max:255', 'unique:employees,employee_code'],
        'full_name' => ['required', 'string', 'max:255'],
        'phone' => ['nullable', 'string', 'max:255'],
        'email' => ['required', 'email', 'max:255', 'unique:users,email'],
        'pin' => ['required', 'digits:4'],
        'department_id' => ['required', 'exists:departments,id'],
        'attendance_mode' => ['required', 'in:fixed,flexible'],
        'shift_id' => ['nullable', 'required_if:attendance_mode,fixed', 'exists:shifts,id'],
        'position' => ['nullable', 'string', 'max:255'],
        'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
        'status' => ['required', 'in:active,inactive'],
    ]);

    if ($validated['attendance_mode'] === 'flexible') $validated['shift_id'] = null;
    DB::transaction(function () use ($request, $validated) {

        $user = User::create([
            'name' => $validated['full_name'],
            'email' => $validated['email'],
            'password' => Hash::make(\Illuminate\Support\Str::random(40)),
            'role' => 'employee',
        ]);

        $employee = $validated;

        $employee['user_id'] = $user->id;
        $employee['pin'] = bcrypt($employee['pin']);

        if ($request->hasFile('photo')) {
            $employee['photo'] = $request->file('photo')->store('employees', 'public');
        }

        Employee::create($employee);
    });

    return redirect()
        ->route('admin.employees.index')
        ->with('success', 'Employee created successfully.');
}
    public function show(Request $request, Employee $employee, AttendanceSummaryService $attendanceSummary)
    {
        $request->validate(['date' => ['nullable', 'date_format:Y-m-d']]);
        $employee->load(['department', 'shift']);

        $filters = ['employee_id' => $employee->id];

        if ($request->filled('date')) {
            $filters['date'] = $request->date;
        }

        $attendanceRecords = $attendanceSummary->summarize($filters);

        $presentCount = $attendanceRecords->where('status', 'present')->count();
        $lateCount = $attendanceRecords->where('status', 'late')->count();
        $totalAttendance = $attendanceRecords->count();

        // Calendar follows the selected day's month, so picking a date both
        // filters the table to that day and lets you browse its month -
        // without needing separate Month/Year controls.
        if ($request->filled('date')) {
            $selected = \Carbon\Carbon::parse($request->date);
            $calendarYear = $selected->year;
            $calendarMonth = $selected->month;
        } else {
            $calendarMonth = now()->month;
            $calendarYear = now()->year;
        }

        $daysInMonth = \Carbon\Carbon::create($calendarYear, $calendarMonth, 1)->daysInMonth;

        $monthlyAttendance = $attendanceSummary
            ->summarize([
                'employee_id' => $employee->id,
                'month' => sprintf('%04d-%02d', $calendarYear, $calendarMonth),
            ])
            ->keyBy(function ($record) {
                return \Carbon\Carbon::parse($record->attendance_date)->day;
            });

        $attendancePoints = AttendancePoint::where('status', 'active')->orderBy('name')->get();

        return view('admin.employees.show', compact(
            'employee',
            'attendanceRecords',
            'presentCount',
            'lateCount',
            'totalAttendance',
            'calendarMonth',
            'calendarYear',
            'daysInMonth',
            'monthlyAttendance',
            'attendancePoints'
        ));
    }

    /**
     * Admin correction for a single day - creates/updates the
     * attendance_records override that AttendanceSummaryService prefers
     * over the raw scan-event computation for that employee+date.
     */
    public function updateAttendance(Request $request, Employee $employee, AttendanceSummaryService $attendanceSummary)
    {
        $validated = $request->validate([
            'date' => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
            'check_in_time' => ['nullable', 'date_format:H:i'],
            'check_out_time' => ['nullable', 'date_format:H:i'],
            'attendance_point_id' => ['required', 'exists:attendance_points,id'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        if (!empty($validated['check_out_time']) && empty($validated['check_in_time'])) {
            throw \Illuminate\Validation\ValidationException::withMessages(['check_in_time' => 'Enter check-in before adding check-out.']);
        }
        $overnight = $employee->shift && $employee->shift->end_time < $employee->shift->start_time;
        if (!$overnight && !empty($validated['check_out_time']) && $validated['check_out_time'] < $validated['check_in_time']) {
            throw \Illuminate\Validation\ValidationException::withMessages(['check_out_time' => 'Check-out must be after check-in for this shift.']);
        }

        $attendanceSummary->upsertOverride(
            $employee,
            $validated['date'],
            $validated['check_in_time'] ?? null,
            $validated['check_out_time'] ?? null,
            $validated['attendance_point_id'],
            $validated['note'] ?? null
        );

        return redirect()
            ->route('admin.employees.show', [
                $employee,
                'date' => $validated['date'],
            ])
            ->with('success', 'Attendance updated for ' . $validated['date'] . '.');
    }
    public function destroyAttendance(Request $request, Employee $employee)
    {
        $validated = $request->validate(['date' => ['required', 'date_format:Y-m-d', 'before_or_equal:today']]);
        [$start, $end] = app(\App\Services\AttendanceWorkday::class)->bounds($employee, $validated['date']);
        DB::transaction(function () use ($employee, $validated, $start, $end) {
            if ($employee->user_id) User::whereKey($employee->user_id)->lockForUpdate()->firstOrFail();
            $events = \App\Models\Attendance::where('user_id', $employee->user_id ?? 0)
                ->where('scanned_at', '>=', $start)->where('scanned_at', '<', $end);
            $overrides = $employee->attendanceRecords()->whereDate('attendance_date', $validated['date']);
            abort_unless($events->exists() || $overrides->exists(), 404);
            $events->delete();
            $overrides->delete();
        });
        return redirect()->route('admin.employees.show', ['employee' => $employee])
            ->with('success', 'Attendance deleted for ' . $validated['date'] . '.');
    }

    public function edit(Employee $employee)
    {
        $departments = Department::all();
        $shifts = Shift::all();

        return view('admin.employees.edit', compact('employee', 'departments', 'shifts'));
    }

  public function update(Request $request, Employee $employee)
{
    $request->mergeIfMissing(['attendance_mode' => $employee->attendance_mode ?? 'fixed']);
    $validated = $request->validate([
        'employee_code' => ['required', 'string', 'max:255', 'unique:employees,employee_code,' . $employee->id],
        'full_name' => ['required', 'string', 'max:255'],
        'phone' => ['nullable', 'string', 'max:255'],
        'email' => [
            'nullable', 'email', 'max:255',
            'unique:employees,email,' . $employee->id,
            'unique:users,email,' . $employee->user_id,
        ],
        'pin' => ['nullable', 'digits:4'],
        'department_id' => ['required', 'exists:departments,id'],
        'attendance_mode' => ['required', 'in:fixed,flexible'],
        'shift_id' => ['nullable', 'required_if:attendance_mode,fixed', 'exists:shifts,id'],
        'position' => ['nullable', 'string', 'max:255'],
        'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
        'status' => ['required', 'in:active,inactive'],
    ]);

    if ($validated['attendance_mode'] === 'flexible') $validated['shift_id'] = null;
    if (!empty($validated['pin'])) {
        $validated['pin'] = bcrypt($validated['pin']);
    } else {
        unset($validated['pin']);
    }

    if ($request->hasFile('photo')) {
        if ($employee->photo && Storage::disk('public')->exists($employee->photo)) {
            Storage::disk('public')->delete($employee->photo);
        }

        $validated['photo'] = $request->file('photo')->store('employees', 'public');
    }

    // employees.email is a separate column from the linked users.email
    // (the one actually checked at login) - keep them in sync so editing
    // an employee's email here doesn't silently lock them out.
    DB::transaction(function () use ($employee, $validated) {
        $employee->user?->update([
            'name' => $validated['full_name'],
            'email' => ($validated['email'] ?? null) ?: $employee->user->email,
        ]);
        $employee->update($validated);
    });

    return redirect()->route('admin.employees.index')
        ->with('success', 'Employee updated successfully.');
}

    public function destroy(Employee $employee)
    {
        if ($employee->attendanceRecords()->withTrashed()->exists() || \App\Models\Attendance::withTrashed()->where('user_id', $employee->user_id)->exists()) {
            return back()->withErrors(['employee' => 'This employee has attendance history. Set their status inactive instead of deleting them.']);
        }
        DB::transaction(function () use ($employee) {
            $user = $employee->user;
            $employee->delete();
            $user?->delete();
        });

        return redirect()->route('admin.employees.index')
            ->with('success', 'Employee deleted successfully.');
    }
}
