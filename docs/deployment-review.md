# Pre-deployment review - 17 November 2026

This is a repository review, not a certification of a running production deployment. No production database was migrated and no real attendance or Telegram messages were created during this review.

## Fixes in this review

- Block inactive employees on every employee route, including sessions established before an administrator disabled the account.
- Reject ambiguous employee name/PIN matches instead of selecting the first account.
- Send administrators to their dashboard when they log in after opening an employee QR.
- Limit the public file endpoint to employee photos and generated QR images, with a nosniff response header.
- Extend `attendance:check --production` to check the environment, application key, PHP database driver, built assets and stale Vite development-server files.
- Reject malformed shift times and handle an array-valued dashboard date without a server error.
- Detect the available PDO MySQL SSL constant instead of relying solely on PHP version during configuration loading.
- Update compatible dependencies and rerun security audits and application tests; see the final review result for any remaining advisories.

The updated Composer and npm audits reported zero security advisories. This reflects the advisory databases at review time and does not prove the absence of vulnerabilities.

Final verification: 62 PHP tests (290 assertions), 22 frontend tests, syntax checks for 91 PHP files, Vite production build, Blade caching and route caching passed. The production readiness check correctly fails for the current development environment, HTTP URL, enabled debug and disabled secure cookies. No physical-phone test or PostgreSQL integration test was performed in this review.

## Deployment blockers

The local configuration is for development: HTTP application URL, debug enabled and secure session cookies disabled. Set the following on the production host, without changing the local development environment:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-attendance-domain.example
SESSION_DRIVER=database
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax
```

Preserve the existing APP_KEY when migrating existing data. Set database and Telegram credentials through the host's secrets/environment configuration. Do not publish `.env` or database backups. Use the `public` directory as the document root; the root Apache fallback is not a substitute for configuring other web servers correctly.

## Neon / PostgreSQL

The current application database is still MySQL. The local PHP CLI does not currently load `pdo_pgsql`. PostgreSQL migration and data transfer have NOT been verified. Installing the PostgreSQL server alone does not enable the PHP driver.

For Neon, configure `DB_CONNECTION=pgsql`, `DB_URL` with the Neon PostgreSQL connection string and `DB_SSLMODE=require`. This repository reads **DB_URL**, not DATABASE_URL. Enable `pdo_pgsql` in the PHP runtime. Test migrations and the application against an isolated PostgreSQL database before migrating real data. A MySQL SQL dump cannot be imported directly as PostgreSQL SQL.

## Vercel

This repository is not yet configured for Vercel deployment. Its PHP application needs a working PHP runtime and routing; uploading only the Vite output does not deploy Laravel. Employee photo uploads and QR creation currently write to local persistent storage. Provide durable shared storage or adapt these paths for object storage before using an ephemeral runtime. Sessions and cache must use shared persistent backends. Do not treat a successful frontend build as a successful Laravel deployment.

## Release checks

1. Back up the database and uploaded files and verify a restore procedure. Keep an export of the current MySQL database before any engine conversion.
2. Install the locked production dependencies with `composer install --no-dev --prefer-dist --optimize-autoloader`. Build assets with `npm ci` and `npm run build` in a build environment.
3. Configure production secrets, persistent storage, trusted proxy addresses where applicable, and PHP upload/post limits sufficient for the 10 MB photo limit.
4. Run `php artisan migrate --force` on the intended deployment database. Do not run `migrate:fresh`, demo seeders or `key:generate` against the existing installation.
5. Run `php artisan config:cache`, `php artisan route:cache` and `php artisan view:cache` on the target runtime. Run `php artisan attendance:check --production`; every required check must pass.
6. Generate/print QR images using the new HTTPS host. Old host URLs opened with a phone's native camera still target the old host even though the in-app scanner remaps tokens.
7. Verify on a physical phone: login, allow camera/location, scan in, fresh scan out, success/history/dashboard consistency, outside-radius rejection and no duplicate records on refresh.
8. Verify Telegram delivery with clearly labelled test messages, and configure SMTP if administrator password recovery is required (MAIL_MAILER=log does not deliver email).

GPS coordinates are browser supplied and can be spoofed. Static QR codes and GPS checks are not proof of physical presence. Automated tests use SQLite and mocked Telegram; they do not establish PostgreSQL compatibility or physical-device permissions.
