<?php
/**
 * Villa Deletion Script
 * Use this script to manually delete villas when the admin interface isn't working
 */

// Include October CMS bootstrap
require_once 'bootstrap/app.php';

use Illuminate\Support\Facades\DB;

// Get villa IDs to delete (you can modify this array)
$villaIdsToDelete = [
    // Add the IDs of villas you want to delete here
    // Example: 9, 10, 11
];

if (empty($villaIdsToDelete)) {
    echo "No villa IDs specified. Please edit this script and add the IDs you want to delete.\n";
    echo "Example: \$villaIdsToDelete = [9, 10, 11];\n";
    exit;
}

try {
    // Delete villas from the database
    $deleted = DB::table('xc_12345678123412341234123456789abcc')
        ->whereIn('id', $villaIdsToDelete)
        ->delete();
    
    echo "Successfully deleted {$deleted} villa(s) with IDs: " . implode(', ', $villaIdsToDelete) . "\n";
    
} catch (Exception $e) {
    echo "Error deleting villas: " . $e->getMessage() . "\n";
}
?>
