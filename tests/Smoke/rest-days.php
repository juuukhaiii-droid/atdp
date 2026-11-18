<?php

require dirname(__DIR__, 2) . '/vendor/autoload.php';
$app = require dirname(__DIR__, 2) . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\{User, Employee, Department, Shift, Attendance, EmployeeRestDay};
use App\Services\AttendanceSummaryService;
use App\Http\Controllers\Admin\{EmployeeController, DashboardController};
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\{DB, Artisan, Auth};
use Illuminate\Validation\ValidationException;

config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:',
    'database.connections.sqlite.url' => null, 'session.driver' => 'array', 'cache.default' => 'array']);
DB::purge('sqlite');
if (DB::connection()->getDatabaseName() !== ':memory:') throw new RuntimeException('Unsafe test database');
Artisan::call('migrate', ['--force' => true]);
session()->start();
view()->share('errors', new Illuminate\Support\ViewErrorBag());
$passed = 0;
$check = function ($condition, $label) use (&$passed) {
    if (!$condition) throw new RuntimeException('FAIL: ' . $label);
    $passed++; echo 'PASS: ' . $label . PHP_EOL;
};
try {
    Carbon::setTestNow(Carbon::parse('2026-11-18 15:00', config('app.timezone')));
    $dept = Department::create(['name' => 'Test']);
    $employees = [];
    foreach ([1, 2] as $number) {
        $user = User::create(['name' => 'Test ' . $number, 'email' => "rest{$number}@example.test", 'password' => password_hash('test', PASSWORD_BCRYPT), 'role' => 'employee']);
        $employees[] = Employee::create(['employee_code' => 'REST-' . $number, 'full_name' => $user->name,
            'pin' => password_hash('1234', PASSWORD_BCRYPT), 'department_id' => $dept->id, 'shift_id' => Shift::first()->id,
            'user_id' => $user->id, 'status' => 'active']);
    }
    [$first, $second] = $employees;
    $admin = User::create(['name' => 'Admin', 'email' => 'admin@example.test', 'password' => password_hash('test', PASSWORD_BCRYPT), 'role' => 'admin']);
    Auth::login($admin);
    $controller = app(EmployeeController::class);
    $save = fn ($employee, $month, $dates) => $controller->updateRestDays(Request::create('/', 'PUT', ['month' => $month, 'rest_days' => $dates]), $employee);
    $save($first, '2026-11', ['2026-11-18', '2026-11-20']);
    $save($second, '2026-11', ['2026-11-19']);
    $save($first, '2026-12', ['2026-12-02']);
    $check($first->restDays()->count() === 3 && $second->restDays()->count() === 1, 'Different employees and months keep independent rest dates');
    $summary = app(AttendanceSummaryService::class);
    $check($summary->forEmployeeOnDate($first->id, '2026-11-18')->status === 'day_off', 'Scheduled date without a scan is a rest day');
    $check($summary->forEmployeeOnDate($second->id, '2026-11-18') === null, 'Other employee does not inherit the rest day');
    $dashboard = app(DashboardController::class)->index(Request::create('/', 'GET', ['date' => '2026-11-18']), $summary);
    $check($dashboard->getData()['absentToday'] === 1, 'Admin absent count excludes the employee on leave');
    $check(str_contains($dashboard->render(), 'day_off'), 'Admin dashboard renders rest status');
    $detail = $controller->show(Request::create('/', 'GET', ['month' => '2026-11']), $first, $summary)->render();
    $check(str_contains($detail, 'name="rest_days[]"') && str_contains($detail, 'name="month"'), 'Monthly calendar renders rest-day editor');
    try { $save($first, '2026-11', ['2026-12-05']); throw new RuntimeException('Cross-month date accepted'); }
    catch (ValidationException $error) { $check($first->restDays()->count() === 3, 'Cross-month input rejected without changing existing dates'); }
    try { $save($first, '2026-11', ['2026-11-20', '2026-11-20']); throw new RuntimeException('Duplicates accepted'); }
    catch (ValidationException $error) { $check(true, 'Duplicate dates rejected'); }
    Attendance::create(['user_id' => $first->user_id, 'type' => 'in', 'scanned_at' => '2026-11-18 09:00:00', 'ip_address' => '127.0.0.1']);
    $check(in_array($summary->forEmployeeOnDate($first->id, '2026-11-18')->status, ['present', 'late']), 'Real attendance takes precedence when working on a rest day');
    $save($first, '2026-11', ['2026-11-17', '2026-11-20']);
    $check($first->restDays()->whereDate('rest_date', '2026-12-02')->exists() && $second->restDays()->whereDate('rest_date', '2026-11-19')->exists(), 'Replacing one month preserves other months and employees');
    Auth::login($first->user);
    $employeeDashboard = app(App\Http\Controllers\Employee\EmployeeDashboardController::class)->index($summary);
    $check($employeeDashboard->getData()['restDaysElapsed'] === 1, 'Employee attendance rate excludes elapsed rest days');
    $employeeDashboard->render();
    $request = Request::create('/', 'GET', ['month' => '2026-11', 'status' => 'day_off']);
    $request->setUserResolver(fn () => $first->user);
    $history = app(App\Http\Controllers\Employee\EmployeeAttendanceController::class)->history($request, $summary);
    $check($history->getData()['records']->total() === 2 && str_contains($history->render(), 'is-day_off'), 'Employee history supports rest-day filtering');
    $save($first, '2026-11', []);
    $check(!$first->restDays()->whereBetween('rest_date', ['2026-11-01', '2026-11-30'])->exists() && Attendance::count() === 1, 'Clearing rest dates never deletes attendance');
    echo 'RESULT: ' . $passed . ' checks passed on isolated SQLite. No live data or Telegram changed.' . PHP_EOL;
} finally { Carbon::setTestNow(); }
