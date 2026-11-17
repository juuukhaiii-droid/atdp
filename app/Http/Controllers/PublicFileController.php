<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;

class PublicFileController extends Controller
{
    // Serves the public disk directly instead of relying on `storage:link` -
    // Hostinger disables PHP's symlink() function, so the symlink the
    // artisan command depends on can never be created on this host.
    public function show(string $path)
    {
        abort_unless(preg_match('#\A(?:employees/[a-zA-Z0-9_-]+\.(?:jpg|jpeg|png|webp)|qrcodes/qr_[0-9]+\.svg)\z#i', $path), 404);
        if (!Storage::disk('public')->exists($path)) {
            abort(404);
        }

        return Storage::disk('public')->response($path, null, ['X-Content-Type-Options' => 'nosniff']);
    }
}
