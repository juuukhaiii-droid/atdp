<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'attendance_point_id',
        'type',
        'scanned_at',
        'ip_address',
        'work_summary',
        'latitude',
        'longitude',
        'location_accuracy',
        'distance_meters',
        'location_captured_at',
    ];

    protected $casts = [
        'scanned_at' => 'datetime',
        'location_captured_at' => 'datetime',
        'latitude' => 'float',
        'longitude' => 'float',
        'location_accuracy' => 'float',
        'distance_meters' => 'float',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function attendancePoint(): BelongsTo
    {
        return $this->belongsTo(AttendancePoint::class);
    }
}
