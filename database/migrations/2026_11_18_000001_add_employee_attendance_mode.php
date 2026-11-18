<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->string('attendance_mode', 20)->default('fixed');
            $table->foreignId('shift_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        if (DB::table('employees')->whereNull('shift_id')->exists()) {
            throw new RuntimeException('Assign a shift to all employees before rolling back attendance modes.');
        }
        Schema::table('employees', function (Blueprint $table) {
            $table->foreignId('shift_id')->nullable(false)->change();
            $table->dropColumn('attendance_mode');
        });
    }
};
