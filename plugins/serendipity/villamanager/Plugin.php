<?php namespace Serendipity\VillaManager;

use System\Classes\PluginBase;
use Event;
use DB;
use Flash;

/**
 * Villa Manager Plugin
 * Handles proper villa deletion functionality
 */
class Plugin extends PluginBase
{
    public function pluginDetails()
    {
        return [
            'name'        => 'Villa Manager',
            'description' => 'Custom villa deletion management',
            'author'      => 'Serendipity Living',
            'icon'        => 'icon-home',
            'version'     => '1.0.0'
        ];
    }

    public function boot()
    {
        // No custom JavaScript needed - we're overriding the backend behavior directly

        // Remove the column-based delete buttons since we're replacing the main delete button

        // Override the default delete behavior for villa entries
        Event::listen('tailor.blueprint.extendModel', function($blueprint, $model) {
            if ($blueprint->uuid === '12345678-1234-1234-1234-123456789abc') {
                // Override the delete method to force hard delete
                $model->addDynamicMethod('delete', function() use ($model) {
                    // Force hard delete directly from database
                    return DB::table('xc_12345678123412341234123456789abcc')
                        ->where('id', $model->id)
                        ->delete();
                });
            }
        });

        // Intercept bulk delete operations for villas
        Event::listen('tailor.blueprint.beforeBulkDelete', function($blueprint, $recordIds) {
            if ($blueprint->uuid === '12345678-1234-1234-1234-123456789abc') {
                // Perform hard delete directly on database
                $deleted = DB::table('xc_12345678123412341234123456789abcc')
                    ->whereIn('id', $recordIds)
                    ->delete();

                Flash::success(sprintf('Successfully deleted %d villa(s).', $deleted));

                // Return false to prevent the default soft delete
                return false;
            }
        });
    }
}
