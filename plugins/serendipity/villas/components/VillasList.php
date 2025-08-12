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
        $this->villas = Villa::query()
            ->where(function($q){
                $q->whereNull('project_id')->orWhere('visible_in_catalog', true);
            })
            ->orderByDesc('featured_in_catalog')
            ->orderByDesc('id')
            ->take(12)
            ->get();
    }
}

