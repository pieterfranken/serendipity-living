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
        $project = Project::with(['villas', 'villas.gallery'])->where('slug', $slug)->first();
        if (!$project) {
            return \Response::make('Project not found', 404);
        }
        $this->page['project'] = $project;
    }
}

