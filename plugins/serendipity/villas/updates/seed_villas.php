<?php namespace Serendipity\Villas\Updates;

use Seeder;
use Serendipity\Villas\Models\Villa;
use Illuminate\Support\Str;

class SeedVillas extends Seeder
{
    public function run()
    {
        if (Villa::count() > 0) return;

        $items = [
            ['title' => 'Azure Crest Residence', 'bedrooms' => 5, 'bathrooms' => 6, 'interior_area_m2' => 780, 'price' => 8200000, 'currency' => 'EUR'],
            ['title' => 'Golden Dune Villa', 'bedrooms' => 6, 'bathrooms' => 7, 'interior_area_m2' => 920, 'price' => 11200000, 'currency' => 'EUR'],
            ['title' => 'Ivory Horizon Estate', 'bedrooms' => 4, 'bathrooms' => 5, 'interior_area_m2' => 650, 'price' => 6100000, 'currency' => 'EUR'],
        ];

        foreach ($items as $i) {
            $villa = new Villa();
            $villa->title = $i['title'];
            $villa->slug = Str::slug($i['title']);
            $villa->bedrooms = $i['bedrooms'];
            $villa->bathrooms = $i['bathrooms'];
            $villa->interior_area_m2 = $i['interior_area_m2'];
            $villa->price = $i['price'];
            $villa->currency = $i['currency'];
            $villa->description = 'A meticulously crafted sanctuary of modern luxury and privacy.';
            // Only set thumbnail_url if the column exists (fresh installs run this after migrations, but be safe)
            if (\Schema::hasColumn('ser_villas', 'thumbnail_url')) {
                $villa->thumbnail_url = '/themes/serendipity-living/assets/images/placeholder-villa.svg';
            }
            $villa->save();
        }
    }
}

