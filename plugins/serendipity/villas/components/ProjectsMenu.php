<?php namespace Serendipity\Villas\Components;

use Cms\Classes\ComponentBase;
use Serendipity\Villas\Models\Project;

class ProjectsMenu extends ComponentBase
{
    public $current;
    public $previous;

    public function componentDetails()
    {
        return [
            'name'        => 'Projects Menu',
            'description' => 'Provides project lists for navigation menus.'
        ];
    }

    public function onRun()
    {
        $this->current = Project::select('title', 'slug')
            ->current()
            ->orderBy('title')
            ->get();
        $this->previous = Project::select('title', 'slug')
            ->previous()
            ->orderBy('title')
            ->get();
        // Expose to page for easy access from partials
        $this->page['projectsMenuCurrent'] = $this->current;
        $this->page['projectsMenuPrevious'] = $this->previous;
    }
}

