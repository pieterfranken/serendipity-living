<?php namespace Serendipity\Villas\Jobs;

use Serendipity\Villas\Classes\LayoutZipService;
use Serendipity\Villas\Models\Villa;
use Log;

class BuildVillaLayoutsZip implements \Illuminate\Contracts\Queue\ShouldQueue
{
    use \Illuminate\Bus\Queueable, \Illuminate\Queue\SerializesModels, \Illuminate\Queue\InteractsWithQueue;

    public function __construct(public int $villaId, public bool $force = false) {}

    public static function dispatch(int $villaId, bool $force = false)
    {
        try { if (class_exists(\Illuminate\Support\Facades\Bus::class)) { return \Illuminate\Support\Facades\Bus::dispatch(new static($villaId, $force)); } } catch (\Throwable $e) {}
        try { if (class_exists(\Illuminate\Support\Facades\Queue::class)) { return \Illuminate\Support\Facades\Queue::push(new static($villaId, $force)); } } catch (\Throwable $e) {}
        (new static($villaId, $force))->handle(new LayoutZipService());
        return null;
    }

    public function handle(LayoutZipService $service)
    {
        $villa = Villa::with('layouts')->find($this->villaId);
        if (!$villa) return;
        if (!$villa->enable_layouts_download || !$villa->layouts || !$villa->layouts->count()) return;
        try {
            $meta = $service->ensureZip($villa, $this->force);
            Log::info('Villa layouts zip ensured', ['villa_id' => $villa->id, 'meta' => $meta]);
        } catch (\Exception $e) {
            Log::warning('Failed to build layouts zip for villa', ['villa_id' => $villa->id, 'error' => $e->getMessage()]);
        }
    }
}

