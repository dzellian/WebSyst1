<?php
// admin.php
require_once 'config.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('dashboard.php');
}

// Handle delete user
if (isset($_GET['delete'])) {
    $delete_id = $_GET['delete'];
    if ($delete_id != $_SESSION['user_id']) {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$delete_id]);
    }
    redirect('admin.php');
}

// Get search and sort parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$sort_by = isset($_GET['sort']) ? $_GET['sort'] : 'id';
$order = isset($_GET['order']) ? $_GET['order'] : 'ASC';

// Build query
$query = "SELECT * FROM users WHERE 1=1";
$params = [];

if ($search) {
    $query .= " AND (username LIKE ? OR email LIKE ? OR full_name LIKE ?)";
    $search_param = "%$search%";
    $params = [$search_param, $search_param, $search_param];
}

$allowed_sorts = ['id', 'username', 'email', 'full_name', 'user_type', 'created_at'];
if (in_array($sort_by, $allowed_sorts)) {
    $query .= " ORDER BY $sort_by";
    if ($order === 'DESC') {
        $query .= " DESC";
    } else {
        $query .= " ASC";
    }
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

function toggleOrder($current) {
    return $current === 'ASC' ? 'DESC' : 'ASC';
}

function sortIcon($column, $current_sort, $current_order) {
    if ($column === $current_sort) {
        return $current_order === 'ASC' ? '▲' : '▼';
    }
    return '⇅';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - DTR System</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f5f5f5; }
        .navbar { background: #667eea; color: white; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .navbar h1 { font-size: 24px; }
        .navbar a { color: white; text-decoration: none; margin-left: 20px; padding: 8px 15px; border-radius: 5px; }
        .navbar a:hover { background: rgba(255,255,255,0.2); }
        .container { max-width: 1400px; margin: 40px auto; padding: 0 20px; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .search-box { display: flex; gap: 10px; }
        .search-box input { padding: 10px; border: 1px solid #ddd; border-radius: 5px; width: 300px; }
        .search-box button { padding: 10px 20px; background: #667eea; color: white; border: none; border-radius: 5px; cursor: pointer; }
        .btn { padding: 10px 20px; background: #27ae60; color: white; text-decoration: none; border-radius: 5px; display: inline-block; }
        .btn:hover { background: #229954; }
        .table-container { background: white; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #f8f9fa; font-weight: bold; color: #333; cursor: pointer; user-select: none; }
        th:hover { background: #e9ecef; }
        .profile-img { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; }
        .badge { padding: 5px 10px; border-radius: 15px; font-size: 12px; font-weight: bold; }
        .badge-admin { background: #f39c12; color: white; }
        .badge-faculty { background: #3498db; color: white; }
        .btn-delete { padding: 6px 12px; background: #e74c3c; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 12px; }
        .btn-delete:hover { background: #c0392b; }
        .no-results { padding: 40px; text-align: center; color: #999; }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>Admin Panel</h1>
        <div>
            <a href="dashboard.php">Dashboard</a>
            <a href="logout.php">Logout</a>
        </div>
    </div>
    
    <div class="container">
        <div class="header">
            <h2>User Management</h2>
            <a href="add_user.php" class="btn">+ Add New User</a>
        </div>
        
        <form method="GET" class="search-box">
            <input type="text" name="search" placeholder="Search users..." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit">Search</button>
            <?php if ($search): ?>
                <a href="admin.php" class="btn" style="background: #95a5a6; text-decoration: none;">Clear</a>
            <?php endif; ?>
        </form>
        
        <div class="table-container" style="margin-top: 20px;">
            <?php if (count($users) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>
                            <a href="?sort=id&order=<?php echo toggleOrder($order); ?>&search=<?php echo urlencode($search); ?>" style="color: inherit; text-decoration: none;">
                                ID <?php echo sortIcon('id', $sort_by, $order); ?>
                            </a>
                        </th>
                        <th>Picture</th>
                        <th>
                            <a href="?sort=username&order=<?php echo toggleOrder($order); ?>&search=<?php echo urlencode($search); ?>" style="color: inherit; text-decoration: none;">
                                Username <?php echo sortIcon('username', $sort_by, $order); ?>
                            </a>
                        </th>
                        <th>
                            <a href="?sort=email&order=<?php echo toggleOrder($order); ?>&search=<?php echo urlencode($search); ?>" style="color: inherit; text-decoration: none;">
                                Email <?php echo sortIcon('email', $sort_by, $order); ?>
                            </a>
                        </th>
                        <th>
                            <a href="?sort=full_name&order=<?php echo toggleOrder($order); ?>&search=<?php echo urlencode($search); ?>" style="color: inherit; text-decoration: none;">
                                Full Name <?php echo sortIcon('full_name', $sort_by, $order); ?>
                            </a>
                        </th>
                        <th>
                            <a href="?sort=user_type&order=<?php echo toggleOrder($order); ?>&search=<?php echo urlencode($search); ?>" style="color: inherit; text-decoration: none;">
                                Type <?php echo sortIcon('user_type', $sort_by, $order); ?>
                            </a>
                        </th>
                        <th>
                            <a href="?sort=created_at&order=<?php echo toggleOrder($order); ?>&search=<?php echo urlencode($search); ?>" style="color: inherit; text-decoration: none;">
                                Joined <?php echo sortIcon('created_at', $sort_by, $order); ?>
                            </a>
                        </th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                    <tr>
                        <td><?php echo $u['id']; ?></td>
                        <td>
                            <img src="uploads/<?php echo htmlspecialchars($u['profile_picture']); ?>" 
                                 alt="Profile" 
                                 class="profile-img"
                                 onerror="this.src='https://via.placeholder.com/40?text=?'">
                        </td>
                        <td><?php echo htmlspecialchars($u['username']); ?></td>
                        <td><?php echo htmlspecialchars($u['email']); ?></td>
                        <td><?php echo htmlspecialchars($u['full_name']); ?></td>
                        <td>
                            <span class="badge <?php echo $u['user_type'] === 'admin' ? 'badge-admin' : 'badge-faculty'; ?>">
                                <?php echo strtoupper($u['user_type']); ?>
                            </span>
                        </td>
                        <td><?php echo date('M d, Y', strtotime($u['created_at'])); ?></td>
                        <td>
                            <?php if ($u['id'] != $_SESSION['user_id']): ?>
                                <button onclick="deleteUser(<?php echo $u['id']; ?>)" class="btn-delete">Delete</button>
                            <?php else: ?>
                                <span style="color: #999; font-size: 12px;">Current User</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
                <div class="no-results">No users found</div>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        function deleteUser(id) {
            if (confirm('Are you sure you want to delete this user?')) {
                window.location.href = 'admin.php?delete=' + id;
            }
        }
    </script>
</body>
</html>