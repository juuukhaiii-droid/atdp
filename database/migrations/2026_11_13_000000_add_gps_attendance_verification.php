<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendance_points', function (Blueprint $table) {
            // Existing checkpoints stay unconfigured until an admin sets the real location.
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->unsignedInteger('radius_meters')->default(100);
        });
        Schema::table('attendances', function (Blueprint $table) {
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->decimal('location_accuracy', 10, 2)->nullable();
            $table->decimal('distance_meters', 10, 2)->nullable();
            $table->timestamp('location_captured_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('attendances', fn (Blueprint $table) => $table->dropColumn([
            'latitude', 'longitude', 'location_accuracy', 'distance_meters', 'location_captured_at',
        ]));
        Schema::table('attendance_points', fn (Blueprint $table) => $table->dropColumn([
            'latitude', 'longitude', 'radius_meters',
        ]));
    }
};
