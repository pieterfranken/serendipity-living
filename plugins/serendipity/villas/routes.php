<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;
use Serendipity\Villas\Models\Villa;
use Serendipity\Villas\Classes\RenderZipService;

Route::group(['middleware' => []], function() {
    Route::get('/download/villa-renders/{villaId}/{signature}', function(Request $request, $villaId, $signature) {
        $key = sprintf('villa-renders:%s:%s', $villaId, $request->ip());
        $max = (int) config('serendipity.villas::renders.rate_limit.max', 10);
        $decay = (int) config('serendipity.villas::renders.rate_limit.decay_minutes', 1);

        // Simple rate limit; if RateLimiter facade not present, skip
        try {
            if (class_exists(RateLimiter::class) && !RateLimiter::tooManyAttempts($key, $max)) {
                RateLimiter::hit($key, $decay * 60);
            } elseif (class_exists(RateLimiter::class)) {
                return Response::make('Too many requests', 429);
            }
        } catch (\Throwable $e) {
            // ignore
        }

        $villa = Villa::find($villaId);
        if (!$villa || !$villa->enable_renders_download) {
            return Response::make('Not found', 404);
        }

        // Verify signature and expiry
        $expires = $request->query('expires');
        if (!$expires || time() > (int)$expires) {
            return Response::make('Link expired', 403);
        }
        $expected = hash_hmac('sha256', $villaId.'|'.$expires.'|'.$villa->id, app('encrypter')->getKey());
        if (!hash_equals($expected, $signature)) {
            return Response::make('Invalid signature', 403);
        }

        // Ensure zip available
        $service = new RenderZipService();
        $meta = $service->ensureZip($villa);
        if (!$meta) {
            return Response::make('No renders available', 404);
        }
        $path = $service->getZipAbsolutePath($meta);
        if (!$path) {
            return Response::make('File missing', 404);
        }

        $downloadName = $meta['filename'] ?? ('villa-'.$villa->id.'-renders.zip');
        return response()->download($path, $downloadName, [
            'Content-Type' => 'application/zip'
        ]);
    });
});

