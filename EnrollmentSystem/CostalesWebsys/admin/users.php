<?php
$page_title = 'Manage Users';
require_once '../config/database.php';
require_once '../includes/header.php';
requireRole('admin');

$database = new Database();
$db = $database->getConnection();

$success = '';
$error = '';

// Handle user actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'toggle_status':
                $user_id = (int)$_POST['user_id'];
                $new_status = $_POST['current_status'] == 'active' ? 'inactive' : 'active';
                
                $query = "UPDATE users SET status = :status WHERE user_id = :id";
                $stmt = $db->prepare($query);
                if ($stmt->execute([':status' => $new_status, ':id' => $user_id])) {
                    $success = 'User status updated successfully!';
                }
                break;
                
            case 'delete':
                $user_id = (int)$_POST['user_id'];
                $query = "DELETE FROM users WHERE user_id = :id AND role != 'admin'";
                $stmt = $db->prepare($query);
                if ($stmt->execute([':id' => $user_id])) {
                    $success = 'User deleted successfully!';
                } else {
                    $error = 'Cannot delete admin users or user has related records.';
                }
                break;
        }
    }
}

// Get all users
$users_query = "SELECT * FROM users ORDER BY role, full_name";
$users = $db->query($users_query)->fetchAll();
?>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">
            <i class="fas fa-users"></i>
            User Management
        </h2>
    </div>
</div>

<?php if ($success): ?>
<div class="alert alert-success">
    <i class="fas fa-check-circle"></i>
    <?php echo $success; ?>
</div>
<?php endif; ?>

<?php if ($error): ?>
<div class="alert alert-danger">
    <i class="fas fa-exclamation-circle"></i>
    <?php echo $error; ?>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Profile</th>
                        <th>Full Name</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Signature</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                       <td>
    <?php 
    if ($user['profile_picture']): 
        $img_path = "../assets/uploads/profiles/" . $user['profile_picture'];
        if (file_exists($img_path)):
    ?>
        <img src="<?php echo $img_path; ?>" alt="Profile" class="profile-img">
    <?php else: ?>
        <div class="profile-placeholder profile-img">
            <i class="fas fa-user"></i>
        </div>
    <?php 
        endif;
    else: 
    ?>
        <div class="profile-placeholder profile-img">
            <i class="fas fa-user"></i>
        </div>
    <?php endif; ?>
</td>
                        <td><strong><?php echo htmlspecialchars($user['full_name']); ?></strong></td>
                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td>
                            <span class="badge badge-<?php echo $user['role']; ?>">
                                <?php echo ucfirst($user['role']); ?>
                            </span>
                        </td>
                        <td>
    <?php 
    if ($user['signature']): 
        $sig_path = "../assets/uploads/signatures/" . $user['signature'];
        if (file_exists($sig_path)):
    ?>
        <img src="<?php echo $sig_path; ?>" alt="Signature" class="signature-img">
    <?php else: ?>
        <span class="text-muted">None</span>
    <?php 
        endif;
    else: 
    ?>
        <span class="text-muted">None</span>
    <?php endif; ?>
</td>
                        <td>
                            <span class="badge badge-<?php echo $user['status'] == 'active' ? 'success' : 'danger'; ?>">
                                <?php echo ucfirst($user['status']); ?>
                            </span>
                        </td>
                        <td>
                            <div class="btn-group">
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="action" value="toggle_status">
                                    <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                    <input type="hidden" name="current_status" value="<?php echo $user['status']; ?>">
                                    <button type="submit" class="btn btn-sm btn-warning">
                                        <i class="fas fa-<?php echo $user['status'] == 'active' ? 'ban' : 'check'; ?>"></i>
                                    </button>
                                </form>
                                
                                <?php if ($user['role'] != 'admin'): ?>
                                <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this user?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>