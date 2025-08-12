<?php namespace Serendipity\Villas\Components;

use Cms\Classes\ComponentBase;
use Serendipity\Villas\Models\Project;

class ProjectsList extends ComponentBase
{
    public $projectsCurrent;
    public $projectsPrevious;

    public function componentDetails()
    {
        return [
            'name'        => 'Projects List',
            'description' => 'Displays current and/or previous projects with their villas.'
        ];
    }

    public function defineProperties()
    {
        return [
            'showPrevious' => [
                'title' => 'Show Previous Projects',
                'type' => 'checkbox',
                'default' => true
            ],
            'showCurrent' => [
                'title' => 'Show Current Projects',
                'type' => 'checkbox',
                'default' => true
            ]
        ];
    }

    public function onRun()
    {
        if ($this->property('showCurrent')) {
            $this->projectsCurrent = Project::with(['villas', 'villas.gallery'])
                ->current()
                ->orderBy('id', 'desc')
                ->get();
        }
        if ($this->property('showPrevious')) {
            $this->projectsPrevious = Project::with(['villas', 'villas.gallery'])
                ->previous()
                ->orderBy('id', 'desc')
                ->get();
        }
    }
}

