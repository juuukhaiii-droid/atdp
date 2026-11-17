<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        foreach ([
            ['name' => 'Full Day', 'start_time' => '08:00:00', 'end_time' => '17:00:00', 'late_after' => '08:30:00'],
            ['name' => 'Morning', 'start_time' => '08:00:00', 'end_time' => '12:00:00', 'late_after' => '08:30:00'],
            ['name' => 'Evening', 'start_time' => '14:00:00', 'end_time' => '18:00:00', 'late_after' => '14:30:00'],
            ['name' => 'Night', 'start_time' => '18:00:00', 'end_time' => '06:00:00', 'late_after' => '18:30:00'],
        ] as $shift) {
            if (!DB::table('shifts')->where('name', $shift['name'])->exists()) {
                DB::table('shifts')->insert(array_merge($shift, ['created_at' => now(), 'updated_at' => now()]));
            }
        }
    }

    public function down(): void
    {
        // Keep assigned shifts: rolling back seed data must not orphan employee records.
    }
};
