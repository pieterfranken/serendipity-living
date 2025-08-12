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
            $p->description = 'An intimate collection of four villas in an elegantly planned enclave.';
            $p->is_previous = false;
            $p->save(); // afterSave will generate a CMS page at /projects/beniusera
        }
    }
}

