<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Department;
use App\Models\Shift;
use App\Models\Employee;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create Departments
        $deptIT = Department::create(['name' => 'IT']);
        $deptHR = Department::create(['name' => 'Human Resources']);
        $deptFinance = Department::create(['name' => 'Finance']);
        $deptOps = Department::create(['name' => 'Operations']);

        // Create Shifts
        $shiftFullDay = Shift::create([
            'name' => 'Full Day',
            'start_time' => '08:00:00',
            'end_time' => '17:00:00',
            'late_after' => '08:30:00'
        ]);
        $shiftMorning = Shift::create([
            'name' => 'Morning',
            'start_time' => '08:00:00',
            'end_time' => '12:00:00',
            'late_after' => '08:30:00'
        ]);
        $shiftEvening = Shift::create([
            'name' => 'Evening',
            'start_time' => '14:00:00',
            'end_time' => '18:00:00',
            'late_after' => '14:30:00'
        ]);
        $shiftNight = Shift::create([
            'name' => 'Night',
            'start_time' => '18:00:00',
            'end_time' => '06:00:00',
            'late_after' => '18:30:00'
        ]);

        // Get existing users and create employee records for them
        $users = User::all();

        foreach ($users as $user) {
            // If user doesn't already have an employee record, create one
            if (!$user->employee) {
                Employee::create([
                    'user_id' => $user->id,
                    'employee_code' => 'EMP' . str_pad($user->id, 4, '0', STR_PAD_LEFT),
                    'full_name' => $user->name,
                    'email' => $user->email,
                    'phone' => '0900000000',
                    'pin' => str_pad($user->id, 4, '0', STR_PAD_LEFT),
                    'department_id' => $user->role === 'admin' ? $deptIT->id : $deptOps->id,
                    'shift_id' => $shiftMorning->id,
                    'position' => $user->role === 'admin' ? 'Manager' : 'Staff',
                    'status' => 'active',
                ]);
            }
        }
    }
}

