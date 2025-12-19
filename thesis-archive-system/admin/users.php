<?php
$page_title = "Manage Users";
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../includes/functions.php';

requireRole('admin');

$database = new Database();
$conn = $database->getConnection();

// Handle delete
if (isset($_GET['delete'])) {
    $delete_id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
    $stmt->execute([$delete_id]);
    logActivity($conn, getUserId(), 'USER_DELETE', 'users', $delete_id, 'Deleted user');
    header("Location: users.php");
    exit();
}

// Get all users
$users = $conn->query("SELECT u.*, d.department_name, p.program_name FROM users u 
    LEFT JOIN departments d ON u.department_id = d.department_id 
    LEFT JOIN programs p ON u.program_id = p.program_id 
    ORDER BY u.user_id DESC")->fetchAll();

require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between mb-3">
        <h2><i class="fas fa-users"></i> Manage Users</h2>
        <a href="index.php" class="btn btn-secondary">Back to Dashboard</a>
    </div>
    
    <div class="card">
        <div class="card-body">
            <table class="table table-striped" id="usersTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Department</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo $user['user_id']; ?></td>
                            <td><?php echo $user['first_name'] . ' ' . $user['last_name']; ?></td>
                            <td><?php echo $user['username']; ?></td>
                            <td><?php echo $user['email']; ?></td>
                            <td><span class="badge bg-primary"><?php echo ucfirst($user['role']); ?></span></td>
                            <td><?php echo $user['department_name'] ?? 'N/A'; ?></td>
                            <td><span class="badge bg-<?php echo $user['status']=='active'?'success':'danger'; ?>">
                                <?php echo ucfirst($user['status']); ?></span></td>
                            <td>
                                <a href="?delete=<?php echo $user['user_id']; ?>" 
                                   class="btn btn-sm btn-danger" 
                                   onclick="return confirm('Delete this user?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#usersTable').DataTable();
});
</script>

<?php require_once '../includes/footer.php'; ?>