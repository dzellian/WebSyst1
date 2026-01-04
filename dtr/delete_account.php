<?php
// delete_account.php
require_once 'config.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];

// Delete user's profile picture if not default
$stmt = $pdo->prepare("SELECT profile_picture FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user && $user['profile_picture'] !== 'default.jpg') {
    $file_path = 'uploads/' . $user['profile_picture'];
    if (file_exists($file_path)) {
        unlink($file_path);
    }
}

// Delete user from database
$stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
$stmt->execute([$user_id]);

// Destroy session
session_destroy();

// Redirect to login
header("Location: login.php?deleted=1");
exit();
?>

---FILE_SEPARATOR---

<?php
// dtr.php
require_once 'config.php';

if (!isLoggedIn()) {
    redirect('login.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DTR System</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f5f5f5; }
        .navbar { background: #667eea; color: white; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .navbar h1 { font-size: 24px; }
        .navbar a { color: white; text-decoration: none; margin-left: 20px; padding: 8px 15px; border-radius: 5px; }
        .navbar a:hover { background: rgba(255,255,255,0.2); }
        .container { max-width: 1200px; margin: 40px auto; padding: 0 20px; text-align: center; }
        .welcome { background: white; padding: 60px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h2 { color: #333; margin-bottom: 20px; }
        p { color: #666; font-size: 18px; }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>DTR System</h1>
        <div>
            <a href="dashboard.php">Dashboard</a>
            <?php if (isAdmin()): ?>
                <a href="admin.php">Manage Users</a>
            <?php endif; ?>
            <a href="logout.php">Logout</a>
        </div>
    </div>
    
    <div class="container">
        <div class="welcome">
            <h2>Welcome to the DTR System</h2>
            <p>This is where the Daily Time Record functionality would be implemented.</p>
            <p style="margin-top: 20px;">You are logged in as: <strong><?php echo htmlspecialchars($_SESSION['full_name']); ?></strong></p>
        </div>
    </div>
</body>
</html>