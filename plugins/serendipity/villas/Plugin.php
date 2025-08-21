<?php namespace Serendipity\Villas;

use System\Classes\PluginBase;
use Cms\Classes\Theme;

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

    public function boot()
    {
        // Load plugin routes
        if (file_exists(__DIR__.'/routes.php')) {
            require_once __DIR__.'/routes.php';
        }
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

    /**
     * Register Twig markup tags (filters/functions)
     * Adds a `|bust` filter that appends a file modification timestamp to URLs
     * to force cache refresh when assets change.
     */
    public function registerMarkupTags()
    {
        return [
            'filters' => [
                'bust' => function ($url) {
                    if (!$url) return $url;

                    // Skip external or data URLs
                    if (preg_match('#^(?:https?:)?//#i', $url) || str_starts_with((string)$url, 'data:')) {
                        return $url;
                    }

                    // Resolve a filesystem path for the URL
                    $path = parse_url($url, PHP_URL_PATH);
                    $fsPath = base_path(ltrim((string)$path, '/'));

                    // If not found, attempt theme-relative resolution
                    if (!is_file($fsPath)) {
                        $theme = Theme::getActiveTheme();
                        if ($theme) {
                            $candidate = $theme->getPath().DIRECTORY_SEPARATOR.ltrim((string)$url, '/');
                            if (is_file($candidate)) {
                                $fsPath = $candidate;
                            }
                        }
                    }

                    if (is_file($fsPath)) {
                        $ver = (int) @filemtime($fsPath);
                        $sep = (strpos($url, '?') !== false) ? '&' : '?';
                        return $url.$sep.'v='.$ver;
                    }

                    return $url; // Fallback unchanged
                },
            ],
        ];
    }
}

