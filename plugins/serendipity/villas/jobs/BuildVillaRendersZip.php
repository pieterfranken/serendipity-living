<?php namespace Serendipity\Villas\Jobs;

use Serendipity\Villas\Classes\RenderZipService;
use Serendipity\Villas\Models\Villa;
use Log;

class BuildVillaRendersZip implements \Illuminate\Contracts\Queue\ShouldQueue
{
    use \Illuminate\Bus\Queueable, \Illuminate\Queue\SerializesModels, \Illuminate\Queue\InteractsWithQueue;

    public $villaId;
    public $force;

    public function __construct(int $villaId, bool $force = false)
    {
        $this->villaId = $villaId;
        $this->force = $force;
    }

    public static function dispatch(int $villaId, bool $force = false)
    {
        // Try Laravel Bus first if available
        try {
            if (class_exists(\Illuminate\Support\Facades\Bus::class)) {
                return \Illuminate\Support\Facades\Bus::dispatch(new static($villaId, $force));
            }
        } catch (\Throwable $e) {
            // ignore and try Queue facade
        }
        try {
            if (class_exists(\Illuminate\Support\Facades\Queue::class)) {
                return \Illuminate\Support\Facades\Queue::push(new static($villaId, $force));
            }
        } catch (\Throwable $e) {
            // ignore and fall back
        }
        // Fallback: run synchronously
        try {
            $job = new static($villaId, $force);
            $job->handle(new RenderZipService());
        } catch (\Throwable $e) {
            // swallow
        }
        return null;
    }

    public function handle(RenderZipService $service)
    {
        $villa = Villa::with('renders')->find($this->villaId);
        if (!$villa) return;

        if (!$villa->enable_renders_download || !$villa->renders || !$villa->renders->count()) {
            // Nothing to do
            return;
        }

        try {
            $meta = $service->ensureZip($villa, $this->force);
            Log::info('Villa renders zip ensured', ['villa_id' => $villa->id, 'meta' => $meta]);
        } catch (\Exception $e) {
            Log::warning('Failed to build renders zip for villa', ['villa_id' => $villa->id, 'error' => $e->getMessage()]);
        }
    }
}

