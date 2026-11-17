<?php

namespace App\Console\Commands;

use App\Models\AttendancePoint;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckAttendanceSetup extends Command
{
    protected $signature = 'attendance:check {--production : Require production HTTPS and cookie settings}';
    protected $description = 'Check attendance configuration without sending alerts or modifying data';

    public function handle(): int
    {
        $failed = false;
        $check = function (string $label, bool $ready, string $help = '') use (&$failed) {
            $ready ? $this->info('PASS: ' . $label) : $this->error('FAIL: ' . $label . ($help ? ' — ' . $help : ''));
            $failed = $failed || !$ready;
        };
        try {
            DB::connection()->getPdo();
            $check('Database connection', true);
            $ran = DB::table('migrations')->pluck('migration')->all();
            $pending = array_filter(glob(database_path('migrations/*.php')), fn ($file) => !in_array(pathinfo($file, PATHINFO_FILENAME), $ran));
            $check('Database migrations', count($pending) === 0, 'Run php artisan migrate before using the app.');
            $check('Administrator account', User::where('role', 'admin')->exists());
            $check('Active employee accounts', Employee::where('status', 'active')->whereNotNull('user_id')->exists());
            $check('Configured GPS checkpoint', AttendancePoint::where('status', 'active')->whereNotNull('latitude')->whereNotNull('longitude')->exists(), 'Configure a checkpoint under Admin > QR checkpoints.');
        } catch (\Throwable $error) {
            $check('Database and schema', false, 'Check database configuration and run migrations.');
        }
        $check('Telegram bot and chat configured', filled(config('services.telegram.bot_token')) && filled(config('services.telegram.chat_id')), 'Set TELEGRAM_BOT_TOKEN and TELEGRAM_CHAT_ID. Delivery is not tested by this command.');
        $check('Public upload storage writable', is_writable(storage_path('app/public')));
        $https = parse_url(config('app.url'), PHP_URL_SCHEME) === 'https';
        if ($this->option('production')) {
            $check('Production environment', app()->environment('production'), 'Set APP_ENV=production.');
            $check('Application key configured', filled(config('app.key')), 'Keep the existing APP_KEY when migrating an existing installation.');
            $check('Database PHP driver', extension_loaded('pdo_' . (config('database.default') === 'mariadb' ? 'mysql' : config('database.default'))));
            $check('Frontend assets built', file_exists(public_path('compiled-assets/manifest.json')) && !file_exists(public_path('hot')), 'Run npm run build and remove a stale public/hot file.');
            $check('HTTPS application URL', $https, 'Phone camera and GPS do not work on ordinary HTTP LAN addresses.');
            $check('Debug disabled', !config('app.debug'), 'Set APP_DEBUG=false.');
            $check('Secure session cookies', (bool) config('session.secure'), 'Set SESSION_SECURE_COOKIE=true on the HTTPS website.');
        } elseif (!$https) {
            $this->warn('Phone camera/GPS require HTTPS. Loopback HTTP works only on the device running the development server.');
        }
        return $failed ? self::FAILURE : self::SUCCESS;
    }
}
