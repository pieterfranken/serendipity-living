<?php namespace Serendipity\Villas\Models;

use Model;

class ProjectVilla extends Model
{
    use \October\Rain\Database\Traits\Validation;
    use \October\Rain\Database\Traits\Sluggable;

    public $table = 'ser_project_villas';

    public $slugs = [
        'slug' => 'name'
    ];

    protected $fillable = [
        'project_id', 'name', 'slug', 'build_size', 'plot_size', 'rooms', 'bathrooms', 'price', 'currency', 'on_request'
    ];

    public $rules = [
        'name' => 'required',
        'slug' => 'required|unique:ser_project_villas',
        'project_id' => 'required|integer'
    ];

    public $attachMany = [
        'images' => 'System\\Models\\File',
    ];

    public $belongsTo = [
        'project' => [Project::class]
    ];
}

