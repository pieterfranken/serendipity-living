<?php namespace Serendipity\AboutUs;

use System\Classes\PluginBase;

class Plugin extends PluginBase
{
    public function pluginDetails()
    {
        return [
            'name' => 'Serendipity About Us',
            'description' => 'Manage About Us page content.',
            'author' => 'Serendipity Living',
            'icon' => 'icon-info'
        ];
    }

    public function registerComponents()
    {
        return [
            \Serendipity\AboutUs\Components\AboutUs::class => 'aboutUs',
        ];
    }

    public function registerPermissions()
    {
        return [
            'serendipity.aboutus.manage' => [
                'tab' => 'Serendipity',
                'label' => 'Manage About Us content'
            ],
        ];
    }

    public function registerSettings()
    {
        return [
            'aboutus' => [
                'label' => 'About Us',
                'description' => 'Edit About Us page content',
                'category' => 'Serendipity',
                'icon' => 'icon-info',
                'class' => \Serendipity\AboutUs\Models\Settings::class,
                'permissions' => ['serendipity.aboutus.manage'],
                'order' => 600
            ]
        ];
    }
}

