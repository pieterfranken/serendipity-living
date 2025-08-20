<?php namespace Serendipity\Villas\Components;

use Cms\Classes\ComponentBase;
use Serendipity\Villas\Models\Villa;

class VillaDetail extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name'        => 'Villa Detail',
            'description' => 'Displays a single villa by slug.'
        ];
    }

    public function defineProperties()
    {
        return [
            'slug' => [
                'title'       => 'Slug',
                'description' => 'URL slug used to find the villa',
                'type'        => 'string',
                'default'     => '{{ :slug }}'
            ],
        ];
    }

    public function onRun()
    {
        $slug = $this->property('slug');
        $villa = Villa::where('slug', $slug)->first();
        if (!$villa) {
            return \Response::make('Villa not found', 404);
        }
        $this->page['villa'] = $villa;
    }

    public function renderDownloadUrl($villa)
    {
        if (!$villa || !$villa->enable_renders_download || !$villa->renders || !$villa->renders->count()) {
            return null;
        }
        $ttl = (int) \Config::get('serendipity.villas::renders.signed_url_ttl_minutes', 30);
        $expires = time() + ($ttl * 60);
        $data = $villa->id.'|'.$expires.'|'.$villa->id;
        $signature = hash_hmac('sha256', $data, app('encrypter')->getKey());
        $query = http_build_query(['expires' => $expires]);
        return url('/download/villa-renders/'.$villa->id.'/'.$signature.'?'.$query);
    }
}

