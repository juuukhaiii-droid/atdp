<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendance_points', function (Blueprint $table) {
            $table->foreignId('department_id')->nullable()->after('code')->constrained()->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('attendance_points', function (Blueprint $table) {
            $table->dropForeignIdFor('Department');
        });
    }
};
