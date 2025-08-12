<?php namespace Serendipity\Villas\Updates;

use Seeder;
use Serendipity\Villas\Models\Project;
use Serendipity\Villas\Models\Villa;
use Illuminate\Support\Str;

class SeedBeniuseraVillas extends Seeder
{
    public function run()
    {
        $project = Project::where('slug', 'beniusera')->first();
        if (!$project) return;

        $specs = [
            ['title' => 'Villa Franco',  'interior' => 370, 'plot' => 1138, 'rooms' => 4, 'baths' => 4, 'price' => null, 'on_request' => true],
            ['title' => 'Villa Nessura', 'interior' => 298, 'plot' => 1149, 'rooms' => 4, 'baths' => 4, 'price' => null, 'on_request' => true],
            ['title' => 'Villa Colea',   'interior' => 300, 'plot' => 1147, 'rooms' => 4, 'baths' => 4, 'price' => null, 'on_request' => true],
            ['title' => 'Villa Decorto', 'interior' => 234, 'plot' =>  413, 'rooms' => 4, 'baths' => 4, 'price' => 1495000, 'on_request' => false],
        ];

        foreach ($specs as $s) {
            if (Villa::where('slug', Str::slug($s['title']))->exists()) continue;
            $v = new Villa();
            $v->title = $s['title'];
            $v->slug = Str::slug($s['title']);
            $v->interior_area_m2 = $s['interior'];
            $v->plot_area_m2 = $s['plot'];
            $v->bedrooms = $s['rooms'];
            $v->bathrooms = $s['baths'];
            $v->price = $s['price'];
            $v->currency = 'EUR';
            $v->price_on_request = $s['on_request'];
            $v->project_id = $project->id;
            $v->visible_in_catalog = false; // listed under project only by default
            $v->description = 'A refined villa within the Beniusera enclave.';
            $v->thumbnail_url = '/themes/serendipity-living/assets/images/placeholder-villa.svg';
            // bypass validation (price is required by model rules)
            $v->forceSave();
        }
    }
}

