<?php namespace Serendipity\AboutUs\Components;

use Cms\Classes\ComponentBase;
use Serendipity\AboutUs\Models\Settings as AboutSettings;

class AboutUs extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name' => 'About Us Renderer',
            'description' => 'Outputs About Us page content from backend settings'
        ];
    }

    public function onRun()
    {
        $this->page['about'] = AboutSettings::instance();
    }
}

