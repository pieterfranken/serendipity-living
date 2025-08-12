<?php namespace Serendipity\Villas;

use System\Classes\PluginBase;

class Plugin extends PluginBase
{
    public function pluginDetails()
    {
        return [
            'name'        => 'Serendipity Villas',
            'description' => 'Luxury villas models and components.',
            'author'      => 'Serendipity Living',
            'icon'        => 'icon-home'
        ];
    }

    public function registerComponents()
    {
        return [
            \Serendipity\Villas\Components\VillasList::class   => 'villasList',
            \Serendipity\Villas\Components\VillaDetail::class  => 'villaDetail',
            \Serendipity\Villas\Components\InquiryForm::class  => 'inquiryForm',
            \Serendipity\Villas\Components\ProjectsList::class => 'projectsList',
            \Serendipity\Villas\Components\ProjectDetail::class => 'projectDetail',
            \Serendipity\Villas\Components\ProjectsMenu::class => 'projectsMenu',
        ];
    }

    public function registerPermissions()
    {
        return [
            'serendipity.villas.manage' => [
                'tab' => 'Serendipity',
                'label' => 'Manage Villas'
            ]
        ];
    }

    public function registerNavigation()
    {
        return [
            'villas' => [
                'label'       => 'Villas',
                'url'         => \Backend::url('serendipity/villas/villas'),
                'icon'        => 'icon-home',
                'permissions' => ['serendipity.villas.manage'],
                'order'       => 500,
                'sideMenu'    => [
                    'villas' => [
                        'label' => 'Villas',
                        'icon' => 'icon-home',
                        'url'  => \Backend::url('serendipity/villas/villas'),
                        'permissions' => ['serendipity.villas.manage'],
                    ],
                    'currentprojects' => [
                        'label' => 'Current Projects',
                        'icon' => 'icon-sitemap',
                        'url'  => \Backend::url('serendipity/villas/currentprojects'),
                        'permissions' => ['serendipity.villas.manage'],
                    ],
                    'previousprojects' => [
                        'label' => 'Previous Projects',
                        'icon' => 'icon-archive',
                        'url'  => \Backend::url('serendipity/villas/previousprojects'),
                        'permissions' => ['serendipity.villas.manage'],
                    ],
                    'inquiries' => [
                        'label' => 'Inquiries',
                        'icon' => 'icon-envelope',
                        'url'  => \Backend::url('serendipity/villas/inquiries'),
                        'permissions' => ['serendipity.villas.manage'],
                    ],
                ],
            ],
        ];
    }
}

