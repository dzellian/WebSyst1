<?php
$page_title = "Activity Logs";
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../includes/functions.php';

requireRole('admin');

$database = new Database();
$conn = $database->getConnection();

$logs = $conn->query("
    SELECT a.*, u.username, u.first_name, u.last_name 
    FROM activity_logs a 
    LEFT JOIN users u ON a.user_id = u.user_id 
    ORDER BY a.created_at DESC 
    LIMIT 500
")->fetchAll();

require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<div class="container mt-4">
    <h2><i class="fas fa-history"></i> Activity Logs</h2>
    <hr>
    
    <div class="card">
        <div class="card-body">
            <table class="table table-sm" id="logsTable">
                <thead>
                    <tr>
                        <th>Time</th>
                        <th>User</th>
                        <th>Action</th>
                        <th>Description</th>
                        <th>IP</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $log): ?>
                        <tr>
                            <td><?php echo date('M d, Y H:i', strtotime($log['created_at'])); ?></td>
                            <td><?php echo $log['username'] ?? 'System'; ?></td>
                            <td><span class="badge bg-info"><?php echo $log['action']; ?></span></td>
                            <td><?php echo htmlspecialchars($log['description']); ?></td>
                            <td><?php echo $log['ip_address']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#logsTable').DataTable({
        order: [[0, 'desc']]
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>