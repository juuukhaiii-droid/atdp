<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Services\OfficeNetworkChecker;
use App\Services\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class EmployeeAttendanceController extends Controller
{
    /**
     * GET /employee/attendance/checkin
     * Shows the check-in/out button, already knowing which action
     * (check-in or check-out) is next for today.
     */
    public function checkinForm(Request $request)
    {
        $lastToday = Attendance::where('user_id', Auth::id())
            ->whereDate('scanned_at', now()->toDateString())
            ->latest('scanned_at')
            ->first();

        $nextAction = $lastToday?->type === 'in' ? 'checkout' : 'checkin';

        return view('employee.attendance.checkin-form', [
            'nextAction' => $nextAction,
        ]);
    }

    /**
     * POST /employee/attendance/checkin
     */
    public function checkin(Request $request, OfficeNetworkChecker $checker, TelegramService $telegram)
    {
        return $this->recordAttendance($request, $checker, $telegram, 'in');
    }

    /**
     * POST /employee/attendance/checkout
     */
    public function checkout(Request $request, OfficeNetworkChecker $checker, TelegramService $telegram)
    {
        return $this->recordAttendance($request, $checker, $telegram, 'out');
    }

    private function recordAttendance(Request $request, OfficeNetworkChecker $checker, TelegramService $telegram, string $type)
    {
        // Real enforcement — the office.network middleware on the route
        // already blocks this, this is just belt-and-suspenders.
        if (!$checker->passes($request->ip())) {
            abort(403, 'You must be connected to office WiFi to ' . ($type === 'in' ? 'check in' : 'check out') . '.');
        }

        $attendance = Attendance::create([
            'user_id' => Auth::id(),
            'type' => $type,
            'scanned_at' => now(),
            'ip_address' => $request->ip(),
        ]);

        $user = Auth::user();
        $label = $type === 'in' ? 'Check-In' : 'Check-Out';

        $telegram->send(
            "✅ <b>{$label}</b>\n" .
            "Name: {$user->name}\n" .
            "Time: {$attendance->scanned_at->format('d M Y H:i:s')}\n" .
            "IP: {$attendance->ip_address}"
        );

        session()->flash('attendance_result', [
            'type' => $type,
            'label' => $label,
            'time' => $attendance->scanned_at->format('d M Y H:i:s'),
            'name' => $user->name,
        ]);

        return redirect()->route('employee.attendance.success');
    }

    /**
     * GET /employee/attendance/success
     */
    public function success(Request $request)
    {
        $result = session('attendance_result');

        if (!$result) {
            return redirect()->route('employee.attendance.checkin.form');
        }

        return view('employee.attendance.success', ['result' => $result]);
    }

    /**
     * GET /api/verify-network
     * Called by checkin-form.blade.php's polling JS.
     */
    public function verifyNetwork(Request $request, OfficeNetworkChecker $checker)
    {
        $ip = $request->ip();
        $verified = $checker->passes($ip);

        return response()->json([
            'verified' => $verified,
            'network_info' => $verified ? "Connected via {$ip}" : null,
        ]);
    }
    public function scan()
    {
        return view('employee.attendance.scan');
    }
    public function submit(
        Request $request,
        $token,
        OfficeNetworkChecker $checker,
        TelegramService $telegram
    ) {
        // Verify office network
        if (!$checker->passes($request->ip())) {
            return redirect()
                ->route('employee.attendance.checkin.form')
                ->withErrors([
                    'wifi' => 'You must be connected to the office WiFi.'
                ]);
        }

  $attendancePoint = AttendancePoint::where('token', $token)->first();

        if (!$attendancePoint) {
            return redirect()
                ->back()
                ->withErrors([
                    'qr' => 'Invalid QR Code.'
                ]);
        }

        // Determine next action
        $lastAttendance = Attendance::where('user_id', Auth::id())
            ->whereDate('scanned_at', today())
            ->latest('scanned_at')
            ->first();

        $type = ($lastAttendance && $lastAttendance->type === 'in')
            ? 'out'
            : 'in';

        // Save attendance
        $attendance = Attendance::create([
            'user_id' => Auth::id(),
            'attendance_point_id' => $attendancePoint->id,
            'type' => $type,
            'scanned_at' => now(),
            'ip_address' => $request->ip(),
        ]);

        // Telegram notification
        $user = Auth::user();

        $telegram->send(
            "✅ Attendance\n" .
            "Employee : {$user->name}\n" .
            "Type : " . strtoupper($type) . "\n" .
            "Location : {$attendancePoint->name}\n" .
            "Time : {$attendance->scanned_at->format('d M Y H:i:s')}"
        );

        // Success session
        session()->flash('attendance_result', [
            'type' => $type,
            'label' => strtoupper($type),
            'time' => $attendance->scanned_at->format('d M Y H:i:s'),
            'name' => $user->name,
        ]);

        return redirect()->route('employee.attendance.success');
    }
}
