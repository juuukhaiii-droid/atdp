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
        if (!Storage::disk('public')->exists($path)) {
            abort(404);
        }

        return Storage::disk('public')->response($path);
    }
}
