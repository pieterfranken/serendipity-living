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
            $this->projectsCurrent = Project::with(['villas', 'villas.gallery', 'villas.thumbnail'])
                ->current()
                ->orderBy('id', 'desc')
                ->get();
            $this->projectsCurrent = $this->sortProjectsVillas($this->projectsCurrent);
        }
        if ($this->property('showPrevious')) {
            $this->projectsPrevious = Project::with(['villas', 'villas.gallery', 'villas.thumbnail'])
                ->previous()
                ->orderBy('id', 'desc')
                ->get();
            $this->projectsPrevious = $this->sortProjectsVillas($this->projectsPrevious);
        }
    }

    protected function sortProjectsVillas($projects)
    {
        return $projects->map(function($project){
            if ($project->villas) {
                $project->villas = $project->villas->sortByDesc(function($v){
                    return $this->villaHasRealThumb($v) ? 1 : 0;
                })->values();
            }
            return $project;
        });
    }

    protected function villaHasRealThumb($villa): bool
    {
        try {
            if ($villa->thumbnail) return true;
        } catch (\Throwable $e) {}
        $url = (string) ($villa->thumbnail_url ?? '');
        if ($url && stripos($url, 'placeholder-villa.svg') === false) return true;
        return false;
    }
}


