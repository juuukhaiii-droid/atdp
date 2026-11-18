# Employee rest dates

Admin > Employees > Details > choose a calendar month > tick rest days > Save rest days.

Dates belong to one employee and one calendar month. Saving replaces only that employee's selected month's rest dates. Clearing all checkboxes clears that month's schedule; it never deletes scans or corrections. Other months and employees remain unchanged.

Rest dates without a check-in are shown as `day_off`, not absent, in calendars, dashboards, history and reports. Actual check-in evidence takes precedence when someone works on a rest day. Existing lateness rules remain in force for actual scans; flexible employees still have zero late minutes.

Elapsed rest dates with no attendance are excluded from the employee's monthly attendance-rate denominator. Future dates without scans are displayed as pending in the calendar rather than absent.

Deploy migration `2026_11_18_000002_create_employee_rest_days.php` before the new code queries the schedule. Run `php artisan migrate --force` and `php artisan optimize:clear` on the target. Do not use `migrate:fresh`.

Verification: `php tests/Smoke/rest-days.php` uses a guarded in-memory SQLite database and no external notifications. It does not change the configured application database.
