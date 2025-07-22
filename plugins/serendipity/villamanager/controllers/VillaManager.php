<?php namespace Serendipity\VillaManager\Controllers;

use Backend\Classes\Controller;
use Backend\Facades\BackendMenu;
use Tailor\Models\EntryRecord;
use Flash;
use Redirect;
use Input;
use Log;

/**
 * Villa Manager Controller
 * Provides enhanced villa management functionality
 */
class VillaManager extends Controller
{
    public $implement = [
        \Backend\Behaviors\ListController::class,
        \Backend\Behaviors\FormController::class,
    ];

    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';

    public $requiredPermissions = ['tailor.entries.villa_villa.*'];

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Tailor.Tailor', 'content', 'villa_management');
    }

    /**
     * Index page - villa listing with enhanced management
     */
    public function index()
    {
        $this->asExtension('ListController')->index();
    }

    /**
     * Bulk delete villas with proper validation
     */
    public function index_onDelete()
    {
        try {
            $checkedIds = post('checked');
            
            if (!$checkedIds || !is_array($checkedIds)) {
                Flash::error('Please select villas to delete.');
                return $this->listRefresh();
            }

            // Validate IDs
            $validIds = array_filter($checkedIds, function($id) {
                return is_numeric($id) && $id > 0;
            });

            if (empty($validIds)) {
                Flash::error('No valid villas selected for deletion.');
                return $this->listRefresh();
            }

            // Delete villas using EntryRecord
            $deleted = 0;
            foreach ($validIds as $id) {
                $villa = EntryRecord::inSection('Villa\\Villa')->find($id);
                if ($villa) {
                    $villa->delete();
                    $deleted++;
                }
            }

            Flash::success(sprintf('Successfully deleted %d villa(s).', $deleted));
            Log::info('Bulk villa deletion completed', [
                'deleted_count' => $deleted, 
                'requested_ids' => $checkedIds,
                'valid_ids' => $validIds
            ]);

        } catch (\Exception $e) {
            Log::error('Error in bulk villa deletion', [
                'error' => $e->getMessage(),
                'ids' => $checkedIds ?? []
            ]);
            Flash::error('Error deleting villas: ' . $e->getMessage());
        }

        return $this->listRefresh();
    }

    /**
     * Toggle villa enabled status
     */
    public function index_onToggleEnabled()
    {
        try {
            $villaId = post('villa_id');
            
            if (!$villaId || !is_numeric($villaId)) {
                Flash::error('Invalid villa ID.');
                return $this->listRefresh();
            }

            $villa = EntryRecord::inSection('Villa\\Villa')->find($villaId);
            
            if (!$villa) {
                Flash::error('Villa not found.');
                return $this->listRefresh();
            }

            $villa->is_enabled = !$villa->is_enabled;
            $villa->save();

            $status = $villa->is_enabled ? 'enabled' : 'disabled';
            Flash::success("Villa '{$villa->title}' has been {$status}.");
            
            Log::info('Villa status toggled', [
                'villa_id' => $villaId,
                'title' => $villa->title,
                'new_status' => $villa->is_enabled
            ]);

        } catch (\Exception $e) {
            Log::error('Error toggling villa status', [
                'error' => $e->getMessage(),
                'villa_id' => $villaId ?? 'unknown'
            ]);
            Flash::error('Error updating villa status: ' . $e->getMessage());
        }

        return $this->listRefresh();
    }

    /**
     * Export villas to CSV
     */
    public function export()
    {
        try {
            $villas = EntryRecord::inSection('Villa\\Villa')
                ->orderBy('created_at', 'desc')
                ->get();

            $filename = 'villas_export_' . date('Y-m-d_H-i-s') . '.csv';
            
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            
            $output = fopen('php://output', 'w');
            
            // CSV headers
            fputcsv($output, [
                'ID', 'Title', 'Slug', 'Price', 'Status', 'Bedrooms', 'Bathrooms',
                'Plot Size', 'Built Area', 'Pool', 'Garage', 'Featured', 'Enabled',
                'Created At', 'Updated At'
            ]);
            
            // CSV data
            foreach ($villas as $villa) {
                fputcsv($output, [
                    $villa->id,
                    $villa->title,
                    $villa->slug,
                    $villa->price,
                    $villa->status,
                    $villa->bedrooms,
                    $villa->bathrooms,
                    $villa->plot_size,
                    $villa->built_area,
                    $villa->pool ? 'Yes' : 'No',
                    $villa->garage ? 'Yes' : 'No',
                    $villa->is_featured ? 'Yes' : 'No',
                    $villa->is_enabled ? 'Yes' : 'No',
                    $villa->created_at,
                    $villa->updated_at
                ]);
            }
            
            fclose($output);
            exit;

        } catch (\Exception $e) {
            Log::error('Error exporting villas', ['error' => $e->getMessage()]);
            Flash::error('Error exporting villas: ' . $e->getMessage());
            return Redirect::back();
        }
    }
}
