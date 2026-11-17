<?php

namespace App\Services;

use App\Models\AttendancePoint;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class AttendanceLocationVerifier
{
    public function verify(AttendancePoint $point, array $coordinates): array
    {
        if ($point->status !== 'active' || $point->latitude === null || $point->longitude === null) {
            throw ValidationException::withMessages([
                'location' => 'This checkpoint has no active GPS location. Please ask your administrator to configure it.',
            ]);
        }

        $capturedAt = Carbon::parse($coordinates['location_captured_at']);
        if ($capturedAt->lt(now()->subMinutes(2)) || $capturedAt->gt(now()->addSeconds(30))) {
            throw ValidationException::withMessages(['location' => 'Your location has expired. Please capture a fresh GPS location.']);
        }

        $accuracy = (float) $coordinates['location_accuracy'];
        if ($accuracy > min(100, $point->radius_meters)) {
            throw ValidationException::withMessages(['location' => 'GPS accuracy is too low. Move near a window or outside and try again.']);
        }

        $distance = $this->distance(
            (float) $point->latitude, (float) $point->longitude,
            (float) $coordinates['latitude'], (float) $coordinates['longitude'],
        );
        // Do not expand the permitted area using the device-reported accuracy.
        if ($distance > $point->radius_meters) {
            throw ValidationException::withMessages([
                'location' => sprintf('You are %s m from this checkpoint. Attendance is allowed within %s m.', number_format($distance), $point->radius_meters),
            ]);
        }

        return [
            'latitude' => $coordinates['latitude'],
            'longitude' => $coordinates['longitude'],
            'location_accuracy' => $accuracy,
            'distance_meters' => round($distance, 2),
            'location_captured_at' => $capturedAt,
        ];
    }

    public function distance(float $fromLatitude, float $fromLongitude, float $toLatitude, float $toLongitude): float
    {
        $latitudeDelta = deg2rad($toLatitude - $fromLatitude);
        $longitudeDelta = deg2rad($toLongitude - $fromLongitude);
        $a = sin($latitudeDelta / 2) ** 2
            + cos(deg2rad($fromLatitude)) * cos(deg2rad($toLatitude)) * sin($longitudeDelta / 2) ** 2;

        return 6371000 * 2 * asin(sqrt(max(0, min(1, $a))));
    }
}
