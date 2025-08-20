<?php namespace Serendipity\Villas\Jobs;

use Serendipity\Villas\Classes\RenderZipService;
use Serendipity\Villas\Models\Villa;
use Log;

class BuildVillaRendersZip implements \Illuminate\Contracts\Queue\ShouldQueue
{
    use \Illuminate\Bus\Queueable, \Illuminate\Queue\SerializesModels, \Illuminate\Queue\InteractsWithQueue, \Illuminate\Bus\Dispatchable;

    public $villaId;
    public $force;

    public function __construct(int $villaId, bool $force = false)
    {
        $this->villaId = $villaId;
        $this->force = $force;
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

