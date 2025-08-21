<?php namespace Serendipity\Villas\Components;

use Cms\Classes\ComponentBase;
use Serendipity\Villas\Models\Project;

class ProjectDetail extends ComponentBase
{
    public $project;

    public function componentDetails()
    {
        return [
            'name'        => 'Project Detail',
            'description' => 'Displays a single project and its villas.'
        ];
    }

    public function defineProperties()
    {
        return [
            'slug' => [
                'title'       => 'Slug',
                'description' => 'URL slug used to find the project',
                'type'        => 'string',
                'default'     => '{{ :slug }}'
            ],
        ];
    }

    public function onRun()
    {
        $slug = $this->property('slug');
        $project = Project::with(['villas', 'villas.gallery', 'villas.thumbnail'])->where('slug', $slug)->first();
        if (!$project) {
            return \Response::make('Project not found', 404);
        }
        // Sort villas: with real thumbnails first
        if ($project->villas) {
            $project->villas = $project->villas->sortByDesc(function($v){
                try { if ($v->thumbnail) return 1; } catch (\Throwable $e) {}
                $url = (string) ($v->thumbnail_url ?? '');
                return ($url && stripos($url, 'placeholder-villa.svg') === false) ? 1 : 0;
            })->values();
        }
        $this->page['project'] = $project;
    }
}

