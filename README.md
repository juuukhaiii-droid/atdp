# PizzaHappyFamily Attendance

Laravel attendance management with an admin workspace, employee PIN login, QR scanning, GPS checkpoints, and Telegram alerts.

## Local setup

Requires PHP 8.2+, Composer, MySQL/MariaDB, and Node.js 22.12+.

1. Run `composer install` and `npm ci`.
2. On a new installation, copy `.env.example` to `.env` and configure the database. Keep an existing environment and application key unchanged.
3. On a new installation only, run `php artisan key:generate`.
4. Run `php artisan migrate` and `npm run build`.
5. Optional development data: `php artisan db:seed --class=DemoAttendanceSeeder`. It prints demo administrator credentials and employee PIN information. Do not seed demo accounts on a live staff system.
6. Start `php artisan serve --host=127.0.0.1 --port=8000`.

## Accounts and attendance

- Administrators use their full email address and password. Employees use their full name and four-digit PIN. No role selector is needed.
- Administrators create employees, departments, shifts, and QR checkpoints. Public registration is disabled.
- Configure each checkpoint's latitude, longitude, and permitted radius, then download or print its QR.
- Employees scan with the website camera, choose a QR image, or scan with the phone's Camera app. Camera permission is requested after pressing Open camera.
- Scanning a checkpoint automatically captures fresh GPS, validates distance on the server, and saves the next check-in or check-out without a Confirm button. It does not track employees in the background.
- Successful attendance is saved before its Telegram alert. Denied or duplicate submissions send no alerts. Delivery failures are logged without bot credentials and do not erase attendance.
- Admin reports show GPS evidence and manual corrections. Corrections do not fabricate GPS evidence. History-bearing employees/checkpoints and assigned departments/shifts cannot be deleted; deactivate or reassign them instead.
- Overnight shifts retain one workday across midnight. An 18:00-06:00 shift rolls over at noon to accommodate morning overtime. Monthly reports follow the workday.

## Phones, Telegram, and HTTPS

**An ordinary HTTP LAN address such as http://192.168.100.232:8000 cannot use phone browser camera/GPS APIs.** This is a browser restriction, not a WiFi setting. Loopback HTTP works only on the device running the development server.

Use a stable HTTPS domain with a trusted certificate. When Telegram's embedded browser blocks permissions, use its menu to open the page in Safari or Chrome. The scanner displays useful errors instead of an empty preview and can decode QR photos. Photo scanning does not bypass GPS verification, and the photo is decoded locally rather than uploaded to the server.

Set APP_URL to the final HTTPS address before printing deployment QR codes. Printed codes with old hostnames can be read by the website scanner, which maps the token to the current site; scanning them with the phone's Camera app still opens the printed address.

## Deployment

1. Back up the database. Deploy source and built public/compiled-assets, public/css, public/js, and public/images assets.
2. Set `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://your-attendance-domain`, and `SESSION_SECURE_COOKIE=true`. Preserve APP_KEY and database credentials.
3. Configure TELEGRAM_BOT_TOKEN and TELEGRAM_CHAT_ID. Configure SMTP for real password-reset email; the local log mailer does not deliver email.
4. Behind a reverse proxy, set TRUSTED_PROXIES to its IPs. Only use a wildcard when direct origin access is blocked and the edge replaces forwarded headers.
5. Run `composer install --no-dev --optimize-autoloader`, `php artisan migrate --force`, and `php artisan optimize`. Make storage and bootstrap/cache writable by PHP.
6. Prefer public/ as the document root. The root .htaccess supports hosts pinned to the project root and blocks private source/hidden directories. Never expose source archives or credentials.
7. Run `php artisan attendance:check --production`. It checks configuration without changing data or sending alerts; it does not prove external delivery or real-device GPS accuracy.
8. Confirm a physical employee scan over HTTPS, an outside-radius rejection, and Telegram delivery.

Migrations run from the deployment command line; there is no web migration endpoint.

## Verification

- `php artisan test`: PHP checks using an isolated SQLite database.
- `npm run test:frontend`: scanner and location-control tests.
- `npm run build`: production browser assets.
- `php artisan attendance:check`: configured local installation checks.

See [GPS setup](docs/gps-attendance.md). Browser GPS can be spoofed; it is a proximity check rather than proof of physical presence.
