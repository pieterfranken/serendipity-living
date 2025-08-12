<?php namespace Serendipity\Villas\Models;

use Model;
use File;

class Project extends Model
{
    use \October\Rain\Database\Traits\Validation;
    use \October\Rain\Database\Traits\Sluggable;

    public $table = 'ser_projects';

    public $slugs = [
        'slug' => 'title'
    ];

    protected $fillable = [
        'title', 'slug', 'description', 'is_previous'
    ];

    public $rules = [
        'title' => 'required',
        'slug' => 'required|unique:ser_projects',
    ];

    public $attachOne = [
        'hero' => 'System\\Models\\File',
    ];

    public $hasMany = [
        'villas' => [\Serendipity\Villas\Models\Villa::class, 'key' => 'project_id']
    ];

    public function scopeCurrent($query)
    {
        return $query->where('is_previous', false);
    }

    public function scopePrevious($query)
    {
        return $query->where('is_previous', true);
    }

    protected function getPagePath($slug)
    {
        $theme = 'serendipity-living';
        return base_path('themes' . DIRECTORY_SEPARATOR . $theme . DIRECTORY_SEPARATOR . 'pages' . DIRECTORY_SEPARATOR . 'projects' . DIRECTORY_SEPARATOR . $slug . '.htm');
    }

    public function afterSave()
    {
        // Ensure a CMS page exists for this project
        $path = $this->getPagePath($this->slug);
        $dir = dirname($path);
        if (!File::exists($dir)) {
            File::makeDirectory($dir, 0755, true);
        }
        $title = $this->title ?: 'Project';
        $frontMatter = "url = \"/projects/{$this->slug}\"\nlayout = \"default\"\ntitle = \"{$title} — Serendipity Living\"\n\n[projectDetail]\nslug = \"{$this->slug}\"\n==\n";
        $body = "{% component 'projectDetail' %}\n";
        File::put($path, $frontMatter . $body);
    }

    public function afterDelete()
    {
        $path = $this->getPagePath($this->slug);
        if (File::exists($path)) {
            File::delete($path);
        }
    }
}

