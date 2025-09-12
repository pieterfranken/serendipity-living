<?php namespace Serendipity\Villas\Updates;

use Seeder;
use Serendipity\Villas\Models\Project;
use Illuminate\Support\Str;

class SeedDefaultProjects extends Seeder
{
    public function run()
    {
        // Seed a default "Beniusera" current project if not present
        $slug = 'beniusera';
        if (!Project::where('slug', $slug)->exists()) {
            $p = new Project();
            $p->title = 'Beniusera';
            $p->slug = $slug;
            $p->description = "An intimate collection with <strong>bold statements</strong> and _refined italics_.\n\nFeatures:\n- Thoughtful masterplan\n- Private amenities";
            $p->is_previous = false;
            $p->save(); // afterSave will generate a CMS page at /projects/beniusera
        }
    }
}

