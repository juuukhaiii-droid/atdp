<?php

// Runs without PHPUnit. Never connects to the configured application database.
require dirname(__DIR__, 2) . '/vendor/autoload.php';
$app = require dirname(__DIR__, 2) . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Employee\EmployeeAttendanceController;
use App\Models\Attendance;
use App\Models\AttendancePoint;
use App\Models\Department;
use App\Models\Employee;
use App\Services\AttendanceLocationVerifier;
use App\Services\AttendanceSummaryService;
use App\Services\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

config([
    'database.default' => 'sqlite',
    'database.connections.sqlite.database' => ':memory:',
    'database.connections.sqlite.url' => null,
    'session.driver' => 'array', 'cache.default' => 'array',
    'services.telegram.bot_token' => 'test-token', 'services.telegram.chat_id' => 'test-group',
]);
DB::purge('sqlite');
if (DB::connection()->getDriverName() !== 'sqlite' || DB::connection()->getDatabaseName() !== ':memory:') {
    throw new RuntimeException('Refusing to run outside the isolated in-memory database.');
}
Http::preventStrayRequests();
Http::fake(['*' => Http::response(['ok' => true], 200)]);
Artisan::call('migrate', ['--force' => true]);
session()->start();
view()->share('errors', new Illuminate\Support\ViewErrorBag());

$passed = 0;
$check = function (bool $condition, string $label) use (&$passed) {
    if (!$condition) throw new RuntimeException('FAIL: ' . $label);
    echo 'PASS: ' . $label . PHP_EOL;
    $passed++;
};

try {
    Carbon::setTestNow(Carbon::parse('2026-11-18 15:00:00', config('app.timezone')));
    $department = Department::create(['name' => 'Smoke test']);
    $admin = app(EmployeeController::class);
    $data = ['employee_code' => 'FLEX-TEST', 'full_name' => 'Flexible Test', 'email' => 'flex@example.test',
        'pin' => '2468', 'department_id' => $department->id, 'status' => 'active', 'attendance_mode' => 'flexible'];
    $admin->store(Request::create('/', 'POST', $data));
    $employee = Employee::where('employee_code', 'FLEX-TEST')->firstOrFail();
    $user = $employee->user;
    Auth::login($user);
    $check($employee->attendance_mode === 'flexible' && $employee->shift_id === null, 'Flexible employee can be created without a shift');

    $point = AttendancePoint::create(['name' => 'Test checkpoint', 'code' => 'FLEX-QR',
        'qr_token' => str_repeat('a', 32), 'status' => 'active', 'department_id' => $department->id,
        'latitude' => 11.594184, 'longitude' => 104.810963, 'radius_meters' => 100]);
    $controller = app(EmployeeAttendanceController::class);
    $request = function (array $data = [], string $method = 'GET') use ($user) {
        $request = Request::create('/attendance/' . str_repeat('a', 32), $method, $data);
        $request->setUserResolver(fn () => $user);
        return $request;
    };
    $coordinates = fn () => ['latitude' => 11.594184, 'longitude' => 104.810963,
        'location_accuracy' => 10, 'location_captured_at' => now()->toIso8601String()];
    $verifier = app(AttendanceLocationVerifier::class);
    $telegram = app(TelegramService::class);
    $view = $controller->login($request(), $point->qr_token);
    $inScan = $view->getData()['scanId'];
    $check($view->getData()['nextAction'] === 'in' && str_contains($view->render(), 'data-auto-start="true"'), 'First QR scan automatically starts check-in GPS');
    $payload = array_merge($coordinates(), ['attendance_type' => 'in', 'scan_id' => $inScan]);
    $controller->submit($request($payload, 'POST'), $point->qr_token, $verifier, $telegram);
    $event = Attendance::sole();
    $check($event->type === 'in' && $event->latitude === 11.594184 && $event->scanned_at->format('H:i') === '15:00', 'Check-in saves actual time and GPS');
    $summary = app(AttendanceSummaryService::class);
    $day = $summary->forEmployeeOnDate($employee->id, '2026-11-18');
    $check($day->status === 'present' && $day->late_minutes === 0, '15:00 flexible check-in is present with zero late minutes');
    $controller->submit($request($payload, 'POST'), $point->qr_token, $verifier, $telegram);
    $check(Attendance::count() === 1 && Http::recorded()->count() === 1, 'Replaying check-in does not duplicate attendance or Telegram alerts');

    Carbon::setTestNow(Carbon::parse('2026-11-18 17:30:00', config('app.timezone')));
    $view = $controller->login($request(), $point->qr_token);
    $html = $view->render();
    $check($view->getData()['nextAction'] === 'out' && str_contains($html, 'name="work_summary"') && str_contains($html, 'data-auto-start="false"'), 'Second scan shows checkout and allows work summary before saving');
    $controller->submit($request(array_merge($coordinates(), ['attendance_type' => 'out', 'scan_id' => $view->getData()['scanId'], 'work_summary' => 'Prepared orders']), 'POST'), $point->qr_token, $verifier, $telegram);
    $day = $summary->forEmployeeOnDate($employee->id, '2026-11-18');
    $check(Attendance::count() === 2 && $day->check_in_time === '15:00:00' && $day->check_out_time === '17:30:00' && $day->late_minutes === 0, 'Checkout completes the same day and retains zero late minutes');
    $check(Attendance::where('type', 'out')->sole()->work_summary === 'Prepared orders' && Http::recorded()->count() === 2, 'Checkout summary saved and exactly two Telegram requests mocked');

    $view = $controller->login($request(), $point->qr_token);
    foreach (['outside' => ['latitude' => 12.0], 'missing' => ['latitude' => null], 'stale' => ['location_captured_at' => now()->subMinutes(3)->toIso8601String()]] as $case => $override) {
        try {
            $controller->submit($request(array_merge($coordinates(), ['attendance_type' => 'in', 'scan_id' => $view->getData()['scanId']], $override), 'POST'), $point->qr_token, $verifier, $telegram);
            throw new RuntimeException('Invalid GPS accepted: ' . $case);
        } catch (ValidationException $error) {
            $check(Attendance::count() === 2, 'Flexible employee still rejects ' . $case . ' GPS');
        }
    }
    $profile = app(App\Http\Controllers\Employee\EmployeeProfileController::class)->index($request())->render();
    $history = $controller->history($request(['month' => '2026-11']), $summary)->render();
    $dashboard = app(App\Http\Controllers\Employee\EmployeeDashboardController::class)->index($summary)->render();
    $check(str_contains($profile, 'flexible') || str_contains($profile, 'ម៉ោងបត់បែន'), 'Profile renders flexible mode without a shift');
    $check(str_contains($history, '15:00') && str_contains($history, '17:30') && str_contains($dashboard, '17:30'), 'History and dashboard show actual flexible scan times');

    $fixed = array_merge($data, ['employee_code' => 'FIXED-TEST', 'email' => 'fixed@example.test', 'attendance_mode' => 'fixed']);
    try {
        $admin->store(Request::create('/', 'POST', $fixed));
        throw new RuntimeException('Fixed employee accepted without shift');
    } catch (ValidationException $error) {
        $check(isset($error->errors()['shift_id']), 'Fixed mode still requires a shift');
    }
    $check(Http::recorded()->count() === 2, 'Rejected GPS never creates additional alerts');
    echo 'RESULT: ' . $passed . ' checks passed. Isolated SQLite; Telegram mocked; no real attendance changed.' . PHP_EOL;
} finally {
    Carbon::setTestNow();
}
