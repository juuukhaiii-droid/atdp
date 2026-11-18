<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\AttendancePoint;
use App\Models\User;
use App\Services\AttendanceLocationVerifier;
use App\Services\AttendanceWorkday;
use App\Services\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class EmployeeAttendanceController extends Controller
{
    private function requireActiveEmployee(Request $request): void
    {
        abort_unless($request->user()?->role === 'employee' && $request->user()->employee?->status === 'active', 403, 'An active employee account is required.');
    }

    private function nextType(): string
    {
        $workday = app(AttendanceWorkday::class);
        $employee = Auth::user()->employee;
        [$start, $end] = $workday->bounds($employee, $workday->date($employee, now()));
        $last = Attendance::where('user_id', Auth::id())->where('scanned_at', '>=', $start)->where('scanned_at', '<', $end)
            ->orderByDesc('scanned_at')->orderByDesc('id')->first();
        return $last?->type === 'in' ? 'out' : 'in';
    }

    public function checkinForm(Request $request)
    {
        $this->requireActiveEmployee($request);
        return view('employee.attendance.checkin-form', [
            'nextAction' => $this->nextType(),
            'attendancePoints' => AttendancePoint::where('status', 'active')
                ->whereNotNull('latitude')->whereNotNull('longitude')->orderBy('name')->get(),
        ]);
    }

    public function checkin(Request $request, AttendanceLocationVerifier $verifier, TelegramService $telegram)
    {
        return $this->directAttendance($request, $verifier, $telegram, 'in');
    }

    public function checkout(Request $request, AttendanceLocationVerifier $verifier, TelegramService $telegram)
    {
        return $this->directAttendance($request, $verifier, $telegram, 'out');
    }

    private function directAttendance(Request $request, AttendanceLocationVerifier $verifier, TelegramService $telegram, string $type)
    {
        $this->requireActiveEmployee($request);
        $request->validate(['attendance_point_id' => ['required', 'integer', 'exists:attendance_points,id']]);
        $point = AttendancePoint::findOrFail($request->input('attendance_point_id'));
        return $this->recordAttendance($request, $point, $verifier, $telegram, $type);
    }

    private function recordAttendance(Request $request, AttendancePoint $point, AttendanceLocationVerifier $verifier, TelegramService $telegram, string $type, ?string $scanId = null)
    {
        $this->requireActiveEmployee($request);
        $validated = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'location_accuracy' => ['required', 'numeric', 'min:0', 'max:100000'],
            'location_captured_at' => ['required', 'date'],
            'work_summary' => ['nullable', 'string', 'max:2000'],
        ]);
        $location = $verifier->verify($point, $validated);
        $attendance = DB::transaction(function () use ($request, $point, $type, $validated, $location) {
            // Serialize submissions for the same employee, including rapid double taps.
            User::whereKey(Auth::id())->lockForUpdate()->firstOrFail();
            if ($this->nextType() !== $type) {
                throw ValidationException::withMessages(['attendance' => 'Attendance has already changed. Refresh this page before trying again.']);
            }
            return Attendance::create(array_merge($location, [
                'user_id' => Auth::id(),
                'attendance_point_id' => $point->id,
                'type' => $type,
                'scanned_at' => now(),
                'ip_address' => $request->ip(),
                'work_summary' => $type === 'out' ? (trim($validated['work_summary'] ?? '') ?: null) : null,
            ]));
        });
        if ($scanId) {
            session()->put('attendance_scan_intents.' . $scanId . '.event_id', $attendance->id);
        }
        $this->notifyTelegram($telegram, $this->buildAttendanceCaption(
            $request->user(),
            $type,
            $attendance->scanned_at,
            $attendance->ip_address,
            $point->name,
            $attendance->work_summary,
            $attendance
        ));
        return $this->showRecordedAttendance($attendance);
    }

    private function showRecordedAttendance(Attendance $attendance)
    {
        session()->flash('attendance_result', [
            'type' => $attendance->type,
            'label' => $attendance->type === 'in' ? 'Check-In' : 'Check-Out',
            'checkpoint' => $attendance->attendancePoint?->name,
            'time' => $attendance->scanned_at->format('d M Y H:i:s'),
            'name' => Auth::user()->name,
        ]);
        return redirect()->route('employee.attendance.success');
    }

    public function history(Request $request, \App\Services\AttendanceSummaryService $summary)
    {
        $this->requireActiveEmployee($request);
        $request->validate([
            'month' => ['nullable', 'date_format:Y-m'],
            'status' => ['nullable', 'in:present,late,absent'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);
        $month = $request->input('month') ?: now()->format('Y-m');
        $status = $request->input('status');
        $days = $summary->summarize(['employee_id' => $request->user()->employee->id, 'month' => $month]);
        $days->each(function ($day) {
            $day->span_minutes = null;
            if ($day->check_in_time && $day->check_out_time) {
                $in = \Illuminate\Support\Carbon::parse($day->attendance_date . ' ' . $day->check_in_time);
                $out = \Illuminate\Support\Carbon::parse($day->attendance_date . ' ' . $day->check_out_time);
                if ($out->lt($in) && $day->employee->shift?->end_time < $day->employee->shift?->start_time)
                    $out->addDay();
                if ($out->gte($in))
                    $day->span_minutes = (int) floor($in->diffInMinutes($out));
            }
        });
        $stats = [
            'present' => $days->whereIn('status', ['present', 'late'])->count(),
            'late' => $days->where('status', 'late')->count(),
            'late_minutes' => $days->sum('late_minutes'),
            'span_minutes' => $days->sum('span_minutes'),
        ];
        if ($status)
            $days = $days->where('status', $status)->values();
        $page = max(1, (int) $request->query('page', 1));
        $records = new \Illuminate\Pagination\LengthAwarePaginator($days->forPage($page, 15)->values(), $days->count(), 15, $page, [
            'path' => $request->url(),
            'query' => $request->only(['month', 'status']),
        ]);
        return view('employee.attendance.history', compact('records', 'month', 'status', 'stats'));
    }

    public function success(Request $request)
    {
        $result = session('attendance_result');
        return $result ? view('employee.attendance.success', ['result' => $result])
            : redirect()->route('employee.attendance.checkin.form');
    }

    public function scan()
    {
        return view('employee.attendance.scan');
    }

    public function login(Request $request, $token)
    {
        $point = AttendancePoint::where('qr_token', $token)->where('status', 'active')->firstOrFail();
        if (!Auth::check()) {
            session(['attendance_intended_token' => $token]);
            return redirect()->route('login')->withErrors(['login' => 'Please log in to record your attendance.']);
        }
        $this->requireActiveEmployee($request);
        if ($request->has('scan') && (!is_string($request->query('scan')) || !Str::isUuid($request->query('scan')))) {
            return redirect()->route('attendance.show.qr')->withErrors(['scan' => 'This scan is invalid. Please scan the QR again.']);
        }
        $intents = session('attendance_scan_intents', []);
        $scanId = $request->query('scan');
        if ($scanId) {
            $intent = $intents[$scanId] ?? null;
            if (!$intent || $intent['user_id'] !== Auth::id() || $intent['point_id'] !== $point->id) {
                return redirect()->route('attendance.show.qr')->withErrors(['scan' => 'This scan has expired. Please scan the QR again.']);
            }
            if (!empty($intent['event_id'])) {
                return $this->showRecordedAttendance(Attendance::where('user_id', Auth::id())->findOrFail($intent['event_id']));
            }
            if ($intent['expires_at'] < now()->timestamp) {
                return redirect()->route('attendance.show.qr')->withErrors(['scan' => 'This scan has expired. Please scan the QR again.']);
            }
        } else {
            $scanId = (string) Str::uuid();
            $intent = ['user_id' => Auth::id(), 'point_id' => $point->id, 'type' => $this->nextType(), 'expires_at' => now()->addMinutes(5)->timestamp];
            $intents = array_slice($intents, -19, null, true);
            $intents[$scanId] = $intent;
            session(['attendance_scan_intents' => $intents]);
        }
        return view('employee.attendance.confirm', [
            'attendancePoint' => $point,
            'token' => $token,
            'nextAction' => $intent['type'],
            'scanId' => $scanId,
        ]);
    }

    public function submit(Request $request, $token, AttendanceLocationVerifier $verifier, TelegramService $telegram)
    {
        $this->requireActiveEmployee($request);
        $validated = $request->validate(['attendance_type' => ['required', 'in:in,out'], 'scan_id' => ['nullable', 'uuid']]);
        $point = AttendancePoint::where('qr_token', $token)->where('status', 'active')->firstOrFail();
        $scanId = $validated['scan_id'] ?? null;
        if ($scanId) {
            $intent = session('attendance_scan_intents.' . $scanId);
            if (!$intent || $intent['user_id'] !== Auth::id() || $intent['point_id'] !== $point->id || $intent['type'] !== $validated['attendance_type']) {
                throw ValidationException::withMessages(['scan' => 'This scan is invalid. Please scan the QR again.']);
            }
            if (!empty($intent['event_id'])) {
                return $this->showRecordedAttendance(Attendance::where('user_id', Auth::id())->findOrFail($intent['event_id']));
            }
            if ($intent['expires_at'] < now()->timestamp) {
                throw ValidationException::withMessages(['scan' => 'This scan has expired. Please scan the QR again.']);
            }
        }
        return $this->recordAttendance($request, $point, $verifier, $telegram, $validated['attendance_type'], $scanId);
    }

    /**
     * Sends the HTML-formatted attendance alert.
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
        ?string $workSummary = null,
        ?Attendance $event = null
    ): string {
        $employee = $user->employee;
        $label = $type === 'in' ? 'ចូលធ្វើការ' : 'ចេញពីធ្វើការ';
        $emoji = $type === 'in' ? '🟢' : '🔴';

        $details = ['👤 ឈ្មោះ៖ ' . e($employee?->full_name ?? $user->name)];

        if ($employee) {
            $details[] = '🪪 លេខកូដបុគ្គលិក៖ ' . e($employee->employee_code);
        }

        if ($employee) {
            $role = trim(implode(' · ', array_filter([
                $employee->department->name ?? null,
                $employee->position ?? null,
            ])));

            if ($role !== '') {
                $details[] = '🏢 ផ្នែក / តួនាទី៖ ' . e($role);
            }
        }

        $details[] = '🕒 ថ្ងៃទី / ម៉ោង៖ ' . $scannedAt->format('d/m/Y H:i');

        if ($location) {
            $details[] = '📍 ទីតាំង៖ ' . e($location);
        }

        // if ($event && $event->latitude !== null && $event->longitude !== null) {
        //     // $details[] = 'ចម្ងាយពីទីតាំងការងារ៖ ' . number_format($event->distance_meters, 1) . ' ម៉ែត្រ';
        //     // $details[] = 'ភាពត្រឹមត្រូវ GPS៖ ±' . number_format($event->location_accuracy, 1) . ' ម៉ែត្រ';
        //     $details[] = '<a href="https://www.google.com/maps?q=' . $event->latitude . ',' . $event->longitude . '">មើលទីតាំងបុគ្គលិកលើផែនទី</a>';
        // }

        // <blockquote> is the only HTML tag Telegram's Bot API renders with
        // a colored accent bar - the closest thing to "color" available in
        // plain-text messages, so the details (and, on checkout, the work
        // summary) each live inside their own one.
        $lines = [
            '<b>PizzaHappyFamily · វត្តមានបុគ្គលិក</b>',
            "{$emoji} <b>{$label}</b>",
            '',
            '<blockquote>' . implode("\n", $details) . '</blockquote>',
        ];

        if ($workSummary) {
            $lines[] = '';
            $lines[] = '📝 <b>សង្ខេបការងារ</b>';
            // Telegram's HTML mode doesn't support <br> - a plain newline in
            // the text itself is what it renders as a line break.
            $lines[] = '<blockquote>' . e($workSummary) . '</blockquote>';
        }

        $lines[] = '';
        $lines[] = '🌐 អាសយដ្ឋាន IP៖ <code>' . e($ip) . '</code>';

        return implode("\n", $lines);
    }
}
