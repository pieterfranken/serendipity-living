<?php namespace Serendipity\Villas\Components;

use Cms\Classes\ComponentBase;
use Serendipity\Villas\Models\Villa;

class VillasList extends ComponentBase
{
    public $villas;

    public function componentDetails()
    {
        return [
            'name'        => 'Villas List',
            'description' => 'Displays a list of villas with basic filtering.'
        ];
    }

    public function onRun()
    {
        $this->villas = Villa::query()->orderByDesc('id')->take(12)->get();
    }
}

