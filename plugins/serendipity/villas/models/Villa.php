<?php namespace Serendipity\Villas\Models;

use Model;
use Str;

class Villa extends Model
{
    use \October\Rain\Database\Traits\Validation;
    use \October\Rain\Database\Traits\Sluggable;

    public $table = 'ser_villas';

    // Automatically generate a unique slug from the title
    public $slugs = [
        'slug' => 'title'
    ];

    // Image attachment for thumbnail
    public $attachOne = [
        'thumbnail' => 'System\\Models\\File',
    ];

    // Multiple gallery images
    public $attachMany = [
        'gallery' => 'System\\Models\\File',
    ];

    protected $fillable = [
        'title','slug','price','currency','bedrooms','bathrooms','interior_area_m2','plot_area_m2','description'
    ];

    public $rules = [
        'title' => 'required',
        'slug' => 'required|unique:ser_villas',
        'price' => 'required|numeric',
        'bedrooms' => 'required|integer|min:0',
        'bathrooms' => 'required|integer|min:0',
    ];

    public function beforeValidate()
    {
        if (empty($this->slug) && !empty($this->title)) {
            $this->slug = Str::slug($this->title);
        }
    }

    // Backward compatibility: expose a thumbnail_url attribute from the attached file if present
    public function getThumbnailUrlAttribute($value)
    {
        if ($this->thumbnail) {
            return $this->thumbnail->getPath();
        }
        return $value;
    }
}

