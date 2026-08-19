<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\AttendancePoint;
use App\Models\User;
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

        $workSummary = $type === 'out' ? trim((string) $request->input('work_summary')) : null;

        $attendance = Attendance::create([
            'user_id' => Auth::id(),
            'type' => $type,
            'scanned_at' => now(),
            'ip_address' => $request->ip(),
            'work_summary' => $workSummary ?: null,
        ]);

        $user = Auth::user();
        $label = $type === 'in' ? 'Check-In' : 'Check-Out';

        $this->notifyTelegram($telegram, $this->buildAttendanceCaption(
            $user, $type, $attendance->scanned_at, $attendance->ip_address, null, $attendance->work_summary
        ));

        session()->flash('attendance_result', [
            'type' => $type,
            'label' => $label,
            'time' => $attendance->scanned_at->format('d M Y H:i:s'),
            'name' => $user->name,
        ]);

        return redirect()->route('employee.attendance.success');
    }

    /**
     * GET /employee/attendance/history
     */
    public function history(Request $request)
    {
        $records = Attendance::with('attendancePoint')
            ->where('user_id', Auth::id())
            ->latest('scanned_at')
            ->paginate(15);

        return view('employee.attendance.history', [
            'records' => $records,
        ]);
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
            // Always included (even on failure) so this endpoint can be hit
            // directly to see what IP the server actually detected - the
            // one thing you need to diagnose a WiFi-gate mismatch, which
            // network_info alone doesn't reveal when verification fails.
            'detected_ip' => $ip,
        ]);
    }
    public function scan()
    {
        return view('employee.attendance.scan');
    }

    /**
     * GET /attendance/{token}
     * Landing page when an employee scans the physical QR code with
     * their phone camera. Not behind auth middleware because a scan
     * can arrive from a logged-out session — we bounce those to login
     * and bring them right back here afterwards.
     */
    public function login(Request $request, $token)
    {
        $attendancePoint = AttendancePoint::where('qr_token', $token)
            ->where('status', 'active')
            ->first();

        if (!$attendancePoint) {
            abort(404, 'Invalid or inactive QR code.');
        }

        if (!Auth::check()) {
            session(['attendance_intended_token' => $token]);

            return redirect()->route('login')
                ->withErrors(['login' => 'Please log in to record your attendance.']);
        }

        $lastToday = Attendance::where('user_id', Auth::id())
            ->whereDate('scanned_at', now()->toDateString())
            ->latest('scanned_at')
            ->first();

        $nextAction = $lastToday?->type === 'in' ? 'out' : 'in';

        return view('employee.attendance.confirm', [
            'attendancePoint' => $attendancePoint,
            'token' => $token,
            'nextAction' => $nextAction,
        ]);
    }

    public function submit(
        Request $request,
        $token,
        OfficeNetworkChecker $checker,
        TelegramService $telegram
    ) {
        if (!Auth::check()) {
            session(['attendance_intended_token' => $token]);

            return redirect()->route('login')
                ->withErrors(['login' => 'Please log in to record your attendance.']);
        }

        // Verify office network
        if (!$checker->passes($request->ip())) {
            return redirect()
                ->route('employee.attendance.checkin.form')
                ->withErrors([
                    'wifi' => 'You must be connected to the office WiFi.'
                ]);
        }

        $attendancePoint = AttendancePoint::where('qr_token', $token)->first();

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

        $workSummary = $type === 'out' ? trim((string) $request->input('work_summary')) : null;

        // Save attendance
        $attendance = Attendance::create([
            'user_id' => Auth::id(),
            'attendance_point_id' => $attendancePoint->id,
            'type' => $type,
            'scanned_at' => now(),
            'ip_address' => $request->ip(),
            'work_summary' => $workSummary ?: null,
        ]);

        // Telegram notification
        $user = Auth::user();

        $this->notifyTelegram($telegram, $this->buildAttendanceCaption(
            $user, $type, $attendance->scanned_at, $attendance->ip_address, $attendancePoint->name, $attendance->work_summary
        ));

        // Success session
        session()->flash('attendance_result', [
            'type' => $type,
            'label' => strtoupper($type),
            'time' => $attendance->scanned_at->format('d M Y H:i:s'),
            'name' => $user->name,
        ]);

        return redirect()->route('employee.attendance.success');
    }

    /**
     * Sends the attendance alert as a plain text message.
     */
    private function notifyTelegram(TelegramService $telegram, string $caption): void
    {
        $telegram->send($caption);
    }

    /**
     * Builds the shared HTML-formatted Telegram caption used by both the
     * QR-scan flow and the direct check-in/out buttons, so the two paths
     * no longer produce differently-shaped messages for the same event.
     */
    private function buildAttendanceCaption(
        User $user,
        string $type,
        \Illuminate\Support\Carbon $scannedAt,
        string $ip,
        ?string $location = null,
        ?string $workSummary = null
    ): string {
        $employee = $user->employee;
        $label = $type === 'in' ? 'CHECK IN' : 'CHECK OUT';
        $emoji = $type === 'in' ? '🟢' : '🔴';

        $details = ['👤 ' . e($user->name)];

        if ($employee) {
            $role = trim(implode(' · ', array_filter([
                $employee->department->name ?? null,
                $employee->position ?? null,
            ])));

            if ($role !== '') {
                $details[] = '🏢 ' . e($role);
            }
        }

        $details[] = '🕒 ' . $scannedAt->format('d M Y, h:i A');

        if ($location) {
            $details[] = '📍 ' . e($location);
        }

        // <blockquote> is the only HTML tag Telegram's Bot API renders with
        // a colored accent bar - the closest thing to "color" available in
        // plain-text messages, so the details (and, on checkout, the work
        // summary) each live inside their own one.
        $lines = [
            "{$emoji} <b>{$label}</b>",
            '',
            '<blockquote>' . implode("\n", $details) . '</blockquote>',
        ];

        if ($workSummary) {
            $lines[] = '';
            $lines[] = '📝 <b>Work Summary</b>';
            // Telegram's HTML mode doesn't support <br> - a plain newline in
            // the text itself is what it renders as a line break.
            $lines[] = '<blockquote>' . e($workSummary) . '</blockquote>';
        }

        $lines[] = '';
        $lines[] = '🌐 <code>' . e($ip) . '</code>';

        return implode("\n", $lines);
    }
}
