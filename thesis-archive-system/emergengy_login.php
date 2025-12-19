<?php
require_once 'config/database.php';
require_once 'config/session.php';

$database = new Database();
$conn = $database->getConnection();

// Force delete and recreate admin
$conn->query("DELETE FROM users WHERE username = 'admin'");

$hash = password_hash('admin123', PASSWORD_DEFAULT);
$stmt = $conn->prepare("INSERT INTO users (username, email, password, first_name, last_name, role, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->execute(['admin', 'admin@thesis.com', $hash, 'System', 'Administrator', 'admin', 'active']);

// Check if created
$check = $conn->query("SELECT * FROM users WHERE username = 'admin'")->fetch();

if ($check) {
    echo "✅ Admin created successfully!<br><br>";
    echo "<strong>Username:</strong> admin<br>";
    echo "<strong>Password:</strong> admin123<br>";
    echo "<strong>Email:</strong> admin@thesis.com<br>";
    echo "<strong>Status:</strong> " . $check['status'] . "<br><br>";
    
    // Test password
    if (password_verify('admin123', $check['password'])) {
        echo "✅ Password verification: SUCCESS<br><br>";
        
        // Auto-login
        $_SESSION['user_id'] = $check['user_id'];
        $_SESSION['username'] = $check['username'];
        $_SESSION['role'] = $check['role'];
        $_SESSION['first_name'] = $check['first_name'];
        $_SESSION['last_name'] = $check['last_name'];
        
        echo "✅ You are now logged in!<br><br>";
        echo "<a href='admin/index.php' style='background:green;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;'>GO TO ADMIN DASHBOARD</a>";
    } else {
        echo "❌ Password verification: FAILED<br>";
    }
} else {
    echo "❌ Failed to create admin account!<br>";
    echo "Check if 'users' table exists in database.";
}
?>