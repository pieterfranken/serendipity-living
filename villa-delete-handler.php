<?php
/**
 * Direct Villa Deletion Handler
 */

// Set JSON header
header('Content-Type: application/json');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

try {
    // Include October CMS bootstrap
    require_once 'bootstrap/app.php';

    // Get the villa IDs to delete
    $checkedIds = $_POST['checked'] ?? [];

    if (empty($checkedIds) || !is_array($checkedIds)) {
        echo json_encode(['success' => false, 'error' => 'No villas selected']);
        exit;
    }

    // Validate that all IDs are numeric
    $validIds = array_filter($checkedIds, function($id) {
        return is_numeric($id) && $id > 0;
    });

    if (empty($validIds)) {
        echo json_encode(['success' => false, 'error' => 'Invalid villa IDs']);
        exit;
    }

    // Delete villas from the database
    $deleted = DB::table('xc_12345678123412341234123456789abcc')
        ->whereIn('id', $validIds)
        ->delete();

    echo json_encode([
        'success' => true,
        'deleted' => $deleted,
        'message' => "Successfully deleted {$deleted} villa(s)"
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
