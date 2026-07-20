<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Shift;

return new class extends Migration
{
    public function up(): void
    {
        // Disable foreign key checks temporarily
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // Clear existing shifts
        Shift::truncate();

        // Create new shifts for Asia/Phnom_Penh timezone
        Shift::create([
            'name' => 'Full Day',
            'start_time' => '08:00:00',
            'end_time' => '17:00:00',
            'late_after' => '08:30:00'
        ]);

        Shift::create([
            'name' => 'Morning',
            'start_time' => '08:00:00',
            'end_time' => '12:00:00',
            'late_after' => '08:30:00'
        ]);

        Shift::create([
            'name' => 'Evening',
            'start_time' => '14:00:00',
            'end_time' => '18:00:00',
            'late_after' => '14:30:00'
        ]);

        Shift::create([
            'name' => 'Night',
            'start_time' => '18:00:00',
            'end_time' => '06:00:00',
            'late_after' => '18:30:00'
        ]);

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    public function down(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        Shift::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
};

