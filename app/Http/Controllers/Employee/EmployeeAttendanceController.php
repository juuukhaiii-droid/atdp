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
use Illuminate\Support\Facades\Storage;


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

        $this->notifyTelegram($telegram, $user, $this->buildAttendanceCaption(
            $user, $type, $attendance->scanned_at, $attendance->ip_address
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

        $this->notifyTelegram($telegram, $user, $this->buildAttendanceCaption(
            $user, $type, $attendance->scanned_at, $attendance->ip_address, $attendancePoint->name
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
     * Sends the attendance alert with the employee's profile photo attached
     * when one exists, falling back to a plain text message otherwise.
     */
    private function notifyTelegram(TelegramService $telegram, User $user, string $caption): void
    {
        $photo = $user->employee?->photo;
        $photoPath = $photo ? Storage::disk('public')->path($photo) : null;

        if ($photoPath && file_exists($photoPath)) {
            $thumbnail = $this->makeTelegramThumbnail($photoPath);
            $telegram->sendPhoto($thumbnail ?? $photoPath, $caption);

            if ($thumbnail) {
                @unlink($thumbnail);
            }
        } else {
            $telegram->send($caption);
        }
    }

    /**
     * Downscales the employee's photo before sending it to Telegram.
     * Original profile photos are full-resolution portraits, which Telegram
     * renders as a large image dominating the chat - shrinking it first
     * makes the alert display as a compact thumbnail instead. Returns null
     * (falling back to the original file) if GD can't process the source.
     */
    private function makeTelegramThumbnail(string $sourcePath): ?string
    {
        try {
            $info = getimagesize($sourcePath);

            if (!$info) {
                return null;
            }

            [$width, $height, $type] = $info;

            $source = match ($type) {
                IMAGETYPE_JPEG => imagecreatefromjpeg($sourcePath),
                IMAGETYPE_PNG => imagecreatefrompng($sourcePath),
                IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? imagecreatefromwebp($sourcePath) : null,
                default => null,
            };

            if (!$source) {
                return null;
            }

            $maxSize = 320;
            $ratio = min($maxSize / $width, $maxSize / $height, 1);
            $newWidth = max(1, (int) round($width * $ratio));
            $newHeight = max(1, (int) round($height * $ratio));

            $thumb = imagecreatetruecolor($newWidth, $newHeight);
            imagecopyresampled($thumb, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

            $tmpPath = storage_path('app/tg_thumb_' . uniqid() . '.jpg');
            imagejpeg($thumb, $tmpPath, 85);

            imagedestroy($source);
            imagedestroy($thumb);

            return $tmpPath;
        } catch (\Throwable $e) {
            return null;
        }
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
        ?string $location = null
    ): string {
        $employee = $user->employee;
        $label = $type === 'in' ? 'Check In' : 'Check Out';
        $emoji = $type === 'in' ? '🟢' : '🔴';

        $lines = [
            '📋 <b>Attendance Update</b>',
            '',
            "{$emoji} <b>" . e($label) . '</b>',
            '👤 ' . e($user->name),
        ];

        if ($employee) {
            $role = trim(implode(' · ', array_filter([
                $employee->department->name ?? null,
                $employee->position ?? null,
            ])));

            if ($role !== '') {
                $lines[] = '🏢 ' . e($role);
            }
        }

        $lines[] = '🕒 ' . $scannedAt->format('d M Y, h:i A');

        if ($location) {
            $lines[] = '📍 ' . e($location);
        }

        $lines[] = '';
        $lines[] = '<code>IP ' . e($ip) . '</code>';

        return implode("\n", $lines);
    }
}
