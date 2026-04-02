<?php

namespace App\Http\Controllers;

use App\Models\AttendancePoint;
use App\Models\AttendanceRecord;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function show($token)
    {
        $attendancePoint = AttendancePoint::where('qr_token', $token)
            ->where('status', 'active')
            ->firstOrFail();

        return view('attendance.show', compact('attendancePoint'));
    }

    public function submit(Request $request, $token)
    {
        $request->validate([
            'employee_code' => ['required', 'string'],
            'pin'           => ['required', 'digits:4'],
            'latitude'      => ['required', 'numeric'],
            'longitude'     => ['required', 'numeric'],
        ]);

        // ✅ GPS Check
        if (!$this->isWithinOffice($request->latitude, $request->longitude)) {
            return back()->with('error', '❌ You are too far from the office. Attendance rejected.');
        }

        $attendancePoint = AttendancePoint::where('qr_token', $token)
            ->where('status', 'active')
            ->first();

        if (!$attendancePoint) {
            return back()->with('error', 'Attendance point not found or inactive.');
        }

        $employee = Employee::with('shift')
            ->where('employee_code', $request->employee_code)
            ->where('status', 'active')
            ->first();

        if (!$employee) {
            return back()->with('error', 'Employee not found or inactive.');
        }

        if (!password_verify($request->pin, $employee->pin)) {
            return back()->with('error', 'Invalid PIN.');
        }

        $today = now()->toDateString();
        $currentTime = now()->format('H:i:s');

        $attendance = AttendanceRecord::where('employee_id', $employee->id)
            ->where('attendance_date', $today)
            ->first();

        if (!$attendance) {
            $lateMinutes = 0;
            $status = 'present';

            $lateAfter = Carbon::parse($today . ' ' . $employee->shift->late_after);
            $now = Carbon::now();

            if ($now->gt($lateAfter)) {
                $status = 'late';
                $lateMinutes = $lateAfter->diffInMinutes($now);
            }

            AttendanceRecord::create([
                'employee_id'         => $employee->id,
                'attendance_point_id' => $attendancePoint->id,
                'attendance_date'     => $today,
                'check_in_time'       => $currentTime,
                'status'              => $status,
                'late_minutes'        => $lateMinutes,
                'note'                => 'Check-in recorded',
            ]);

            return back()->with('success', 'Check-in successful for ' . $employee->full_name . '.');
        }

        if ($attendance->check_in_time && !$attendance->check_out_time) {
            $attendance->update([
                'check_out_time'      => $currentTime,
                'attendance_point_id' => $attendancePoint->id,
                'note'                => 'Check-out recorded',
            ]);

            return back()->with('success', 'Check-out successful for ' . $employee->full_name . '.');
        }

        return back()->with('error', 'Attendance already completed for today.');
    }

    // ✅ GPS Distance Calculator
    private function isWithinOffice(float $lat, float $lng): bool
    {
        $officeLat = (float) env('OFFICE_LATITUDE');
        $officeLng = (float) env('OFFICE_LONGITUDE');
        $maxMeters = (int)   env('OFFICE_RADIUS_METERS', 100);

        $earthRadius = 6371000; // meters

        $latDiff = deg2rad($lat - $officeLat);
        $lngDiff = deg2rad($lng - $officeLng);

        $a = sin($latDiff / 2) ** 2 +
             cos(deg2rad($officeLat)) * cos(deg2rad($lat)) *
             sin($lngDiff / 2) ** 2;

        $distance = $earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $distance <= $maxMeters;
    }
}
