<?php namespace Serendipity\Villas\Components;

use Cms\Classes\ComponentBase;
use Serendipity\Villas\Models\Villa;

class VillaDetail extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name'        => 'Villa Detail',
            'description' => 'Displays a single villa by slug.'
        ];
    }

    public function defineProperties()
    {
        return [
            'slug' => [
                'title'       => 'Slug',
                'description' => 'URL slug used to find the villa',
                'type'        => 'string',
                'default'     => '{{ :slug }}'
            ],
        ];
    }

    public function onRun()
    {
        $slug = $this->property('slug');
        $villa = Villa::where('slug', $slug)->first();
        if (!$villa) {
            return \Response::make('Villa not found', 404);
        }
        $this->page['villa'] = $villa;
    }
}

