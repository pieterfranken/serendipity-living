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

    // Multiple gallery images and optional renders
    public $attachMany = [
        'gallery' => 'System\\Models\\File',
        'renders' => 'System\\Models\\File',
    ];

    public $belongsTo = [
        'project' => [\Serendipity\Villas\Models\Project::class],
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        // Listen to attach/detach events for renders to trigger rebuild
        $this->bindEvent('model.relation.attach', function($relationName, $related, $data) {
            if ($relationName === 'renders') {
                $this->maybeQueueZipBuild();
            }
        });
        $this->bindEvent('model.relation.detach', function($relationName, $related) {
            if ($relationName === 'renders') {
                $this->maybeQueueZipBuild();
            }
        });
    }

    protected $fillable = [
        'title','slug','price','currency','bedrooms','bathrooms','interior_area_m2','plot_area_m2','description','project_id','visible_in_catalog','featured_in_catalog','price_on_request','enable_renders_download'
    ];

    protected $casts = [
        'enable_renders_download' => 'boolean',
    ];

    public $rules = [
        'title' => 'required',
        'slug' => 'required|unique:ser_villas',
        'bedrooms' => 'required|integer|min:0',
        'bathrooms' => 'required|integer|min:0',
    ];

    public function beforeValidate()
    {
        if (empty($this->slug) && !empty($this->title)) {
            $this->slug = Str::slug($this->title);
        }
    }

    public function afterSave()
    {
        $this->maybeQueueZipBuild();

        // Optionally purge zip when disabled
        if (!$this->enable_renders_download && \Config::get('serendipity.villas::renders.remove_zip_when_disabled', false)) {
            try {
                $service = new \Serendipity\Villas\Classes\RenderZipService();
                $service->purge($this);
            } catch (\Throwable $e) {
                // ignore
            }
        }
    }

    public function afterDelete()
    {
        // No-op for now; could cleanup zip files if desired
    }

    protected function maybeQueueZipBuild(): void
    {
        try {
            if ($this->enable_renders_download && $this->renders && $this->renders->count()) {
                \Serendipity\Villas\Jobs\BuildVillaRendersZip::dispatch($this->id);
            }
        } catch (\Throwable $e) {
            // Fallback to synchronous build if queues not configured
            try {
                $service = new \Serendipity\Villas\Classes\RenderZipService();
                $service->ensureZip($this);
            } catch (\Throwable $inner) {
                // swallow
            }
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

