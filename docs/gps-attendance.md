# GPS attendance

Attendance uses a fresh browser location when the employee scans for check-in or check-out. Office WiFi is no longer required. This records attendance locations only; it does not track employees in the background.

## Set up a checkpoint

1. Open **Admin → QR checkpoints → Edit**.
2. Enter the shop latitude, longitude, and allowed distance in meters. The initial shop location supplied for this installation is **11.594184, 104.810963**, with a **100 m** radius.
3. Save. Existing QR codes remain valid. New checkpoints require GPS configuration. Unconfigured and inactive checkpoints reject attendance.

The **Use my current location** button fills the coordinates of the device being used. Use it while at the shop, or enter the correct map coordinates manually.

## Employee flow

Scan the checkpoint QR and log in if needed. Allow location access when the browser asks. GPS verification and saving run automatically: the first scan checks in and the next scan checks out. No Confirm button is needed. Refreshing a completed scan shows its result without recording another event. If GPS fails, the employee can retry; attendance is saved only after valid GPS is submitted. Mobile data and other WiFi networks are supported. The direct attendance form also requires choosing a configured checkpoint and tapping its action button.

Phone browsers require **HTTPS** for location and camera access. Desktop `http://localhost` and `http://127.0.0.1` can be used for development; a phone accessing an ordinary HTTP LAN address will need HTTPS.

If permission is denied, enable location permission in the browser/site settings. If GPS accuracy is poor, move near a window or outside and retry. Location must be no older than two minutes, no more than thirty seconds in the future, and accurate to within the smaller of 100 m and the checkpoint radius. The server calculates distance and never increases the radius to compensate for poor accuracy.

## Review records

Open **Admin → Attendance records → View** to see each scan's coordinates, capture time, distance, accuracy, and map link. Historical events without GPS remain labelled as having no GPS evidence. Manual corrections do not create location evidence.

Coordinates come from the employee's browser and can be spoofed by a determined user. GPS checks reduce off-site scans; they are not proof of physical presence. QR codes are still static and this feature does not add rotating codes.

## Deploy and verify

Run the migration `2026_11_13_000000_add_gps_attendance_verification.php` before using the new code. Set real checkpoint coordinates in each environment; the migration does not invent or backfill locations. Existing attendance is preserved.

Run `php artisan test --filter=AttendanceLocationTest`. These tests use an isolated SQLite database and run the full migration chain; they do not modify the configured local or production database.
