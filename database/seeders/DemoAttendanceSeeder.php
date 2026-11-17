<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\AttendancePoint;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class DemoAttendanceSeeder extends Seeder
{
    public function run(): void
    {
        $password = Str::password(18, symbols: false);
        $adminCreated = false;
        $counts = ['employees' => 0, 'attendance_events' => 0];

        DB::transaction(function () use ($password, &$adminCreated, &$counts) {
            $admin = User::firstOrCreate(['email' => 'admin.demo@example.test'], [
                'name' => 'PizzaHappyFamily Demo Admin',
                'password' => Hash::make($password),
                'role' => 'admin',
            ]);
            $adminCreated = $admin->wasRecentlyCreated;

            $departments = collect(['Kitchen', 'Service', 'Delivery', 'Management'])
                ->mapWithKeys(fn ($name) => [$name => Department::firstOrCreate(['name' => $name])]);
            $shifts = collect([
                ['name' => 'Demo Lunch', 'start_time' => '08:00:00', 'end_time' => '16:00:00', 'late_after' => '08:15:00'],
                ['name' => 'Demo Dinner', 'start_time' => '14:00:00', 'end_time' => '22:00:00', 'late_after' => '14:15:00'],
            ])->map(fn ($data) => Shift::firstOrCreate(['name' => $data['name']], $data));

            $points = $departments->map(function ($department, $name) {
                $point = AttendancePoint::firstOrCreate(['code' => 'DEMO-' . strtoupper($name)], [
                    'name' => $name . ' Demo Checkpoint',
                    'department_id' => $department->id,
                    'location' => 'PizzaHappyFamily - ' . $name,
                    'qr_token' => Str::random(32),
                    'status' => 'active',
                ]);
                $path = 'qrcodes/demo_' . $point->id . '.svg';
                if (!$point->qr_image) {
                    Storage::disk('public')->put($path, (string) QrCode::format('svg')->size(300)->margin(1)
                        ->generate(url('/attendance/' . $point->qr_token)));
                    $point->update(['qr_image' => 'files/' . $path]);
                }
                return $point;
            });

            $names = [
                ['Dara Demo', 'Kitchen', 'Pizza Chef'], ['Sokha Demo', 'Kitchen', 'Kitchen Assistant'],
                ['Vanna Demo', 'Kitchen', 'Prep Cook'], ['Sophea Demo', 'Service', 'Cashier'],
                ['Nita Demo', 'Service', 'Server'], ['Rachana Demo', 'Service', 'Server'],
                ['Piseth Demo', 'Delivery', 'Delivery Driver'], ['Visal Demo', 'Delivery', 'Delivery Driver'],
                ['Bopha Demo', 'Delivery', 'Dispatcher'], ['Sreyneang Demo', 'Management', 'Shift Supervisor'],
                ['Chenda Demo', 'Management', 'Stock Controller'], ['Sovan Demo', 'Management', 'Assistant Manager'],
            ];

            foreach ($names as $index => [$name, $department, $position]) {
                $number = $index + 1;
                $email = sprintf('employee%02d.demo@example.test', $number);
                $user = User::firstOrCreate(['email' => $email], [
                    'name' => $name, 'password' => Hash::make(Str::random(40)), 'role' => 'employee',
                ]);
                $shift = $shifts[$index % 2];
                $employee = Employee::firstOrCreate(['employee_code' => sprintf('DEMO-%03d', $number)], [
                    'user_id' => $user->id, 'full_name' => $name, 'email' => $email,
                    'pin' => Hash::make('2468'), 'department_id' => $departments[$department]->id,
                    'shift_id' => $shift->id, 'position' => $position, 'status' => 'active',
                ]);
                if ($employee->user_id !== $user->id || $user->role !== 'employee') {
                    throw new \RuntimeException('Demo account identifier is already in use. No database changes committed.');
                }
                $counts['employees'] += (int) $employee->wasRecentlyCreated;

                // Complete daytime shifts only; rotate a weekly day off and occasional absence.
                $end = Carbon::parse('2026-11-13', config('app.timezone'))->startOfDay();
                for ($offset = 30; $offset >= 1; $offset--) {
                    $date = $end->copy()->subDays($offset);
                    if ($date->dayOfWeek === $index % 7 || ($offset + $index) % 19 === 0) {
                        continue;
                    }
                    // Leave any existing attendance for this demo employee/day untouched.
                    if (Attendance::where('user_id', $user->id)->whereDate('scanned_at', $date)->exists()) {
                        continue;
                    }
                    $late = ($offset + $index) % 5 === 0;
                    $checkIn = Carbon::parse($date->toDateString() . ' ' . $shift->start_time)
                        ->addMinutes($late ? 20 + $index % 15 : $index % 10);
                    $checkOut = Carbon::parse($date->toDateString() . ' ' . $shift->end_time)->addMinutes($index % 12);
                    foreach (['in' => $checkIn, 'out' => $checkOut] as $type => $time) {
                        Attendance::create([
                            'user_id' => $user->id, 'attendance_point_id' => $points[$department]->id,
                            'type' => $type, 'scanned_at' => $time, 'ip_address' => '192.0.2.10',
                            'work_summary' => $type === 'out' ? '[DEMO] Completed ' . strtolower($position) . ' duties, cleaned work area, and handed over to the next shift.' : null,
                        ]);
                        $counts['attendance_events']++;
                    }
                }
            }
        });

        $this->command?->info('Demo data added: ' . json_encode($counts));
        if ($adminCreated) {
            $this->command?->line('Admin email: admin.demo@example.test');
            $this->command?->line('Admin password: ' . $password);
        }
        $this->command?->line('Employee login example: Dara Demo / PIN 2468');
        $this->command?->line('Attendance dates: 2026-10-14 through 2026-11-12. No Telegram messages sent.');
    }
}
