# Flexible attendance

Admin > Employees > Create or Edit > attendance mode:

- Fixed: shift required; existing late rules and overnight shift workday boundaries apply.
- Flexible: shift is cleared, QR and GPS remain required, actual scan times are saved, and late minutes are always zero.

Flexible workdays currently follow calendar dates in the application timezone (midnight to midnight). Cross-midnight shift grouping requires a fixed overnight shift. No exemption from GPS or attendance scanning is introduced.

Migration: `2026_11_18_000001_add_employee_attendance_mode.php`. Existing employees default to fixed and retain their shifts. Apply the migration before deploying the model/service changes. Do not use migrate:fresh.

Attendance summaries follow the employee's current mode, as they already follow the employee's current shift. Switching modes can therefore change how previous scan days are classified; raw scan timestamps are preserved.
