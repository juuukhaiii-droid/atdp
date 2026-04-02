<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Shift;
use Illuminate\Http\Request;
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
        $validated = $request->validate([
            'employee_code' => ['required', 'string', 'max:255', 'unique:employees,employee_code'],
            'full_name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', 'unique:employees,email'],
            'pin' => ['required', 'digits:4'],
            'department_id' => ['required', 'exists:departments,id'],
            'shift_id' => ['required', 'exists:shifts,id'],
            'position' => ['nullable', 'string', 'max:255'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $validated['pin'] = bcrypt($validated['pin']);

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('employees', 'public');
        }

        Employee::create($validated);

        return redirect()->route('admin.employees.index')
            ->with('success', 'Employee created successfully.');
    }
    public function show(Request $request, Employee $employee)
    {
        $employee->load(['department', 'shift']);

        $attendanceQuery = AttendanceRecord::with(['attendancePoint'])
            ->where('employee_id', $employee->id);

        if ($request->filled('date')) {
            $attendanceQuery->whereDate('attendance_date', $request->date);
        }

        if ($request->filled('month')) {
            $monthParts = explode('-', $request->month);
            if (count($monthParts) === 2) {
                $attendanceQuery->whereYear('attendance_date', $monthParts[0])
                    ->whereMonth('attendance_date', $monthParts[1]);
            }
        }

        if ($request->filled('year')) {
            $attendanceQuery->whereYear('attendance_date', $request->year);
        }

        $attendanceRecords = $attendanceQuery->latest('attendance_date')->get();

        $presentCount = $attendanceRecords->where('status', 'present')->count();
        $lateCount = $attendanceRecords->where('status', 'late')->count();
        $totalAttendance = $attendanceRecords->count();

        // calendar month and year
        $calendarMonth = now()->month;
        $calendarYear = now()->year;

        if ($request->filled('month')) {
            $monthParts = explode('-', $request->month);
            if (count($monthParts) === 2) {
                $calendarYear = (int) $monthParts[0];
                $calendarMonth = (int) $monthParts[1];
            }
        } elseif ($request->filled('year')) {
            $calendarYear = (int) $request->year;
        }

        $daysInMonth = \Carbon\Carbon::create($calendarYear, $calendarMonth, 1)->daysInMonth;

        $monthlyAttendance = AttendanceRecord::where('employee_id', $employee->id)
            ->whereYear('attendance_date', $calendarYear)
            ->whereMonth('attendance_date', $calendarMonth)
            ->get()
            ->keyBy(function ($record) {
                return \Carbon\Carbon::parse($record->attendance_date)->day;
            });

        return view('admin.employees.show', compact(
            'employee',
            'attendanceRecords',
            'presentCount',
            'lateCount',
            'totalAttendance',
            'calendarMonth',
            'calendarYear',
            'daysInMonth',
            'monthlyAttendance'
        ));
    }
    public function edit(Employee $employee)
    {
        $departments = Department::all();
        $shifts = Shift::all();

        return view('admin.employees.edit', compact('employee', 'departments', 'shifts'));
    }

  public function update(Request $request, Employee $employee)
{
    $validated = $request->validate([
        'employee_code' => ['required', 'string', 'max:255', 'unique:employees,employee_code,' . $employee->id],
        'full_name' => ['required', 'string', 'max:255'],
        'phone' => ['nullable', 'string', 'max:255'],
        'email' => ['nullable', 'email', 'max:255', 'unique:employees,email,' . $employee->id],
        'pin' => ['nullable', 'digits:4'],
        'department_id' => ['required', 'exists:departments,id'],
        'shift_id' => ['required', 'exists:shifts,id'],
        'position' => ['nullable', 'string', 'max:255'],
        'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        'status' => ['required', 'in:active,inactive'],
    ]);

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

    $employee->update($validated);

    return redirect()->route('admin.employees.index')
        ->with('success', 'Employee updated successfully.');
}

    public function destroy(Employee $employee)
    {
        $employee->delete();

        return redirect()->route('admin.employees.index')
            ->with('success', 'Employee deleted successfully.');
    }
}
