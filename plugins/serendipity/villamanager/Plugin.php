<?php namespace Serendipity\VillaManager;

use System\Classes\PluginBase;
use Event;
use DB;
use Flash;
use Tailor\Classes\BlueprintIndexer;
use Backend\Facades\Backend;
use Backend\Models\User;
use Log;

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
            'description' => 'Enhanced villa management with proper deletion, bulk operations, and export functionality',
            'author'      => 'Serendipity Living',
            'icon'        => 'icon-home',
            'version'     => '2.0.0',
            'homepage'    => 'https://serendipityliving.com'
        ];
    }

    public function registerPermissions()
    {
        return [
            'serendipity.villamanager.access' => [
                'tab' => 'Villa Manager',
                'label' => 'Access Villa Manager'
            ],
            'serendipity.villamanager.delete' => [
                'tab' => 'Villa Manager',
                'label' => 'Delete Villas'
            ],
            'serendipity.villamanager.export' => [
                'tab' => 'Villa Manager',
                'label' => 'Export Villa Data'
            ]
        ];
    }

    public function boot()
    {
        // Override the default delete behavior for villa entries
        Event::listen('tailor.blueprint.extendModel', function($blueprint, $model) {
            if ($blueprint->uuid === '12345678-1234-1234-1234-123456789abc') {
                // Override the delete method to force hard delete
                $model->addDynamicMethod('delete', function() use ($model) {
                    try {
                        // Use the model's table name instead of hardcoded value
                        $tableName = $model->getTable();
                        $deleted = DB::table($tableName)
                            ->where('id', $model->id)
                            ->delete();

                        Log::info("Villa deleted successfully", ['id' => $model->id, 'table' => $tableName]);
                        return $deleted;
                    } catch (\Exception $e) {
                        Log::error("Error deleting villa", ['id' => $model->id, 'error' => $e->getMessage()]);
                        throw $e;
                    }
                });
            }
        });

        // Intercept bulk delete operations for villas
        Event::listen('tailor.blueprint.beforeBulkDelete', function($blueprint, $recordIds) {
            if ($blueprint->uuid === '12345678-1234-1234-1234-123456789abc') {
                try {
                    // Validate input
                    if (empty($recordIds) || !is_array($recordIds)) {
                        Flash::error('No valid villa IDs provided for deletion.');
                        return false;
                    }

                    // Sanitize IDs - ensure they are numeric
                    $validIds = array_filter($recordIds, function($id) {
                        return is_numeric($id) && $id > 0;
                    });

                    if (empty($validIds)) {
                        Flash::error('No valid villa IDs provided for deletion.');
                        return false;
                    }

                    // Get the table name from the blueprint
                    $tableName = $blueprint->getContentTableName();

                    // Perform hard delete directly on database
                    $deleted = DB::table($tableName)
                        ->whereIn('id', $validIds)
                        ->delete();

                    Flash::success(sprintf('Successfully deleted %d villa(s).', $deleted));
                    Log::info("Bulk villa deletion completed", ['deleted_count' => $deleted, 'ids' => $validIds]);

                    // Return false to prevent the default soft delete
                    return false;
                } catch (\Exception $e) {
                    Log::error("Error in bulk villa deletion", ['error' => $e->getMessage(), 'ids' => $recordIds]);
                    Flash::error('Error deleting villas: ' . $e->getMessage());
                    return false;
                }
            }
        });

        // Add admin menu item for enhanced villa management
        Event::listen('backend.menu.extendItems', function($manager) {
            $manager->addSideMenuItems('Tailor.Tailor', 'content', [
                'villa_management' => [
                    'label' => 'Enhanced Villa Manager',
                    'icon' => 'icon-home',
                    'url' => Backend::url('serendipity/villamanager/villamanager'),
                    'permissions' => ['tailor.entries.villa_villa.*'],
                ],
                'villa_standard' => [
                    'label' => 'Standard Villa Manager',
                    'icon' => 'icon-list',
                    'url' => Backend::url('tailor/entries/villa_villa'),
                    'permissions' => ['tailor.entries.villa_villa.*'],
                ]
            ]);
        });
    }
}
