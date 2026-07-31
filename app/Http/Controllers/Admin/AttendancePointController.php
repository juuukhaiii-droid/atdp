<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendancePoint;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class AttendancePointController extends Controller
{
    public function index()
    {
        $attendancePoints = AttendancePoint::with('department')->orderBy('department_id')->latest()->get();
        return view('admin.attendance-points.index', compact('attendancePoints'));
    }

    public function create()
    {
        $departments = Department::all();
        return view('admin.attendance-points.create', compact('departments'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:attendance_points',
            'location' => 'nullable|string|max:255',
            'department_id' => 'nullable|exists:departments,id',
            'status' => 'required|in:active,inactive',
        ]);

        $token = Str::random(32);

        $attendancePoint = AttendancePoint::create([
            'name' => $validated['name'],
            'code' => $validated['code'],
            'location' => $validated['location'],
            'department_id' => $validated['department_id'],
            'status' => $validated['status'],
            'qr_token' => $token,
        ]);

        $url = url('/attendance/' . $token);
        $filename = 'qr_' . $attendancePoint->id . '.svg';
        $directory = storage_path('app/public/qrcodes');

        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $path = $directory . '/' . $filename;

        // Generate SVG QR Code (doesn't require imagick)
        file_put_contents(
            $path,
            QrCode::format('svg')
                ->size(300)
                ->margin(0)
                ->generate($url)
        );

        $attendancePoint->update([
            'qr_image' => 'storage/qrcodes/' . $filename
        ]);

        return redirect()->route('admin.attendance-points.index')
            ->with('success', 'Attendance Point created successfully. QR Code is ready for printing!');
    }

    public function edit(AttendancePoint $attendancePoint)
    {
        $departments = Department::all();
        return view('admin.attendance-points.edit', compact('attendancePoint', 'departments'));
    }

    public function update(Request $request, AttendancePoint $attendancePoint)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:255', 'unique:attendance_points,code,' . $attendancePoint->id],
            'location' => ['nullable', 'string', 'max:255'],
            'department_id' => ['required', 'exists:departments,id'],
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
