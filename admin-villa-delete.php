<!DOCTYPE html>
<html>
<head>
    <title>Villa Management - Delete Villas</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .villa-item { padding: 10px; border: 1px solid #ddd; margin: 5px 0; border-radius: 3px; display: flex; align-items: center; }
        .villa-item:hover { background: #f9f9f9; }
        .villa-checkbox { margin-right: 15px; }
        .villa-info { flex: 1; }
        .villa-title { font-weight: bold; color: #333; }
        .villa-details { color: #666; font-size: 14px; margin-top: 5px; }
        .btn { padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; font-size: 14px; margin: 5px; }
        .btn-danger { background: #d9534f; color: white; }
        .btn-danger:hover { background: #c9302c; }
        .btn-success { background: #5cb85c; color: white; }
        .btn-primary { background: #337ab7; color: white; }
        .alert { padding: 15px; margin: 10px 0; border-radius: 4px; }
        .alert-success { background: #dff0d8; border: 1px solid #d6e9c6; color: #3c763d; }
        .alert-danger { background: #f2dede; border: 1px solid #ebccd1; color: #a94442; }
        .header { text-align: center; margin-bottom: 30px; }
        .actions { text-align: center; margin: 20px 0; }
        .select-all { margin-bottom: 20px; }
    </style>
</head>
<body>

<?php
// Include October CMS bootstrap
require_once 'bootstrap/app.php';

use Illuminate\Support\Facades\DB;

$message = '';
$messageType = '';

// Handle deletion
if ($_POST && isset($_POST['delete_villas'])) {
    $selectedIds = $_POST['villa_ids'] ?? [];
    
    if (!empty($selectedIds)) {
        try {
            $deleted = DB::table('xc_12345678123412341234123456789abcc')
                ->whereIn('id', $selectedIds)
                ->delete();
            
            $message = "Successfully deleted {$deleted} villa(s)!";
            $messageType = 'success';
        } catch (Exception $e) {
            $message = "Error deleting villas: " . $e->getMessage();
            $messageType = 'danger';
        }
    } else {
        $message = "Please select villas to delete.";
        $messageType = 'danger';
    }
}

// Get all villas
try {
    $villas = DB::table('xc_12345678123412341234123456789abcc')
        ->orderBy('created_at', 'desc')
        ->get();
} catch (Exception $e) {
    $villas = [];
    $message = "Error loading villas: " . $e->getMessage();
    $messageType = 'danger';
}
?>

<div class="container">
    <div class="header">
        <h1>Villa Management - Delete Villas</h1>
        <p>Select villas to permanently delete from the database</p>
        <a href="/admin" class="btn btn-primary">← Back to Admin</a>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-<?php echo $messageType; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <?php if (count($villas) > 0): ?>
        <form method="POST" onsubmit="return confirmDeletion()">
            <div class="select-all">
                <label>
                    <input type="checkbox" id="select-all"> Select All Villas
                </label>
            </div>

            <?php foreach ($villas as $villa): ?>
                <div class="villa-item">
                    <input type="checkbox" name="villa_ids[]" value="<?php echo $villa->id; ?>" class="villa-checkbox">
                    <div class="villa-info">
                        <div class="villa-title"><?php echo htmlspecialchars($villa->title ?? 'Untitled Villa'); ?></div>
                        <div class="villa-details">
                            ID: <?php echo $villa->id; ?> | 
                            Price: €<?php echo number_format($villa->price ?? 0); ?> | 
                            Status: <?php echo htmlspecialchars($villa->status ?? 'Unknown'); ?> |
                            Bedrooms: <?php echo $villa->bedrooms ?? 'N/A'; ?> |
                            Enabled: <?php echo $villa->is_enabled ? 'Yes' : 'No'; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

            <div class="actions">
                <button type="submit" name="delete_villas" class="btn btn-danger">
                    🗑️ DELETE SELECTED VILLAS
                </button>
                <button type="button" onclick="window.location.reload()" class="btn btn-success">
                    🔄 Refresh List
                </button>
            </div>
        </form>
    <?php else: ?>
        <div class="alert alert-success">
            <h3>No villas found!</h3>
            <p>All villas have been deleted or there are no villas in the database.</p>
        </div>
    <?php endif; ?>

    <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #ddd; color: #666; font-size: 12px;">
        <p><strong>Note:</strong> This tool permanently deletes villas from the database. This action cannot be undone.</p>
        <p><strong>Total Villas:</strong> <?php echo count($villas); ?></p>
    </div>
</div>

<script>
// Select all functionality
document.getElementById('select-all').addEventListener('change', function() {
    var checkboxes = document.querySelectorAll('input[name="villa_ids[]"]');
    for (var i = 0; i < checkboxes.length; i++) {
        checkboxes[i].checked = this.checked;
    }
});

// Confirmation dialog
function confirmDeletion() {
    var selected = document.querySelectorAll('input[name="villa_ids[]"]:checked');
    if (selected.length === 0) {
        alert('Please select at least one villa to delete.');
        return false;
    }
    
    return confirm('Are you sure you want to permanently delete ' + selected.length + ' villa(s)? This action cannot be undone!');
}
</script>

</body>
</html>
