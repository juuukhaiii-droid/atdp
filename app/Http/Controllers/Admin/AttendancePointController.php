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

    public function qr(AttendancePoint $attendancePoint)
    {
        abort_unless(filled($attendancePoint->qr_token), 404);
        $url = rtrim(config('app.url'), '/') . '/attendance/' . $attendancePoint->qr_token;
        return response((string) QrCode::format('svg')->size(400)->margin(4)->generate($url), 200, [
            'Content-Type' => 'image/svg+xml',
            'Content-Disposition' => 'inline; filename="qr_' . $attendancePoint->id . '.svg"',
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
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
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius_meters' => 'required|integer|between:25,1000',
        ]);

        $token = Str::random(32);

        $attendancePoint = AttendancePoint::create([
            'name' => $validated['name'],
            'code' => $validated['code'],
            'location' => $validated['location'] ?? null,
            'department_id' => $validated['department_id'] ?? null,
            'status' => $validated['status'],
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
            'radius_meters' => $validated['radius_meters'],
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
            'qr_image' => 'files/qrcodes/' . $filename
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
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'radius_meters' => ['required', 'integer', 'between:25,1000'],
        ]);

        $attendancePoint->update($validated);

        return redirect()->route('admin.attendance-points.index')
            ->with('success', 'Attendance point updated successfully.');
    }

    public function destroy(AttendancePoint $attendancePoint)
    {
        if ($attendancePoint->attendanceRecords()->exists() || \App\Models\Attendance::where('attendance_point_id', $attendancePoint->id)->exists()) {
            return back()->withErrors(['checkpoint' => 'This checkpoint has attendance history. Set it inactive instead of deleting it.']);
        }
        $attendancePoint->delete();

        return redirect()->route('admin.attendance-points.index')
            ->with('success', 'Attendance point deleted successfully.');
    }
}
