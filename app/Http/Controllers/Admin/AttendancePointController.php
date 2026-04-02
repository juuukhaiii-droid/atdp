<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendancePoint;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AttendancePointController extends Controller
{
    public function index()
    {
        $attendancePoints = AttendancePoint::latest()->get();
        return view('admin.attendance-points.index', compact('attendancePoints'));
    }

    public function create()
    {
        return view('admin.attendance-points.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:255', 'unique:attendance_points,code'],
            'location' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $validated['qr_token'] = Str::random(32);

        AttendancePoint::create($validated);

        return redirect()->route('admin.attendance-points.index')
            ->with('success', 'Attendance point created successfully.');
    }

    public function edit(AttendancePoint $attendancePoint)
    {
        return view('admin.attendance-points.edit', compact('attendancePoint'));
    }

    public function update(Request $request, AttendancePoint $attendancePoint)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:255', 'unique:attendance_points,code,' . $attendancePoint->id],
            'location' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $attendancePoint->update($validated);

        return redirect()->route('admin.attendance-points.index')
            ->with('success', 'Attendance point updated successfully.');
    }

    public function destroy(AttendancePoint $attendancePoint)
    {
        $attendancePoint->delete();

        return redirect()->route('admin.attendance-points.index')
            ->with('success', 'Attendance point deleted successfully.');
    }
}
