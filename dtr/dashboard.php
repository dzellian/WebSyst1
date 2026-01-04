<?php
// dashboard.php
require_once 'config.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

// Fetch user details
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$success = '';
if (isset($_GET['deleted'])) {
    $success = "Account deleted successfully!";
    session_destroy();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - DTR System</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f5f5f5; }
        .navbar { background: #667eea; color: white; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .navbar h1 { font-size: 24px; }
        .navbar a { color: white; text-decoration: none; margin-left: 20px; padding: 8px 15px; border-radius: 5px; transition: background 0.3s; }
        .navbar a:hover { background: rgba(255,255,255,0.2); }
        .container { max-width: 1200px; margin: 40px auto; padding: 0 20px; }
        .profile-card { background: white; padding: 40px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); text-align: center; }
        .profile-picture { width: 150px; height: 150px; border-radius: 50%; object-fit: cover; margin-bottom: 20px; border: 5px solid #667eea; }
        .user-info { margin: 20px 0; }
        .user-info p { margin: 10px 0; font-size: 16px; }
        .user-info strong { color: #667eea; }
        .badge { display: inline-block; padding: 5px 15px; border-radius: 20px; font-size: 14px; font-weight: bold; }
        .badge-admin { background: #f39c12; color: white; }
        .badge-faculty { background: #3498db; color: white; }
        .btn-group { margin-top: 30px; }
        .btn { padding: 12px 30px; border: none; border-radius: 5px; cursor: pointer; font-size: 14px; margin: 5px; text-decoration: none; display: inline-block; }
        .btn-primary { background: #667eea; color: white; }
        .btn-danger { background: #e74c3c; color: white; }
        .btn:hover { opacity: 0.9; }
        .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #c3e6cb; }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>DTR System</h1>
        <div>
            <?php if (isAdmin()): ?>
                <a href="admin.php">Manage Users</a>
            <?php endif; ?>
            <a href="logout.php">Logout</a>
        </div>
    </div>
    
    <div class="container">
        <?php if ($success): ?>
            <div class="success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <div class="profile-card">
            <img src="uploads/<?php echo htmlspecialchars($user['profile_picture']); ?>" 
                 alt="Profile Picture" 
                 class="profile-picture"
                 onerror="this.src='https://via.placeholder.com/150?text=No+Image'">
            
            <h2><?php echo htmlspecialchars($user['full_name']); ?></h2>
            
            <span class="badge <?php echo $user['user_type'] === 'admin' ? 'badge-admin' : 'badge-faculty'; ?>">
                <?php echo strtoupper($user['user_type']); ?>
            </span>
            
            <div class="user-info">
                <p><strong>Username:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                <p><strong>Member Since:</strong> <?php echo date('F d, Y', strtotime($user['created_at'])); ?></p>
            </div>
            
            <div class="btn-group">
                <a href="dtr.php" class="btn btn-primary">Access DTR System</a>
                <a href="delete_account.php" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete your account? This action cannot be undone.')">Delete Account</a>
            </div>
        </div>
    </div>
</body>
</html>