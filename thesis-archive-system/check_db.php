<?php
require_once 'config/database.php';

$database = new Database();
$conn = $database->getConnection();

if ($conn) {
    echo "✅ Database connected!<br><br>";
    
    // Check users table
    $users = $conn->query("SELECT * FROM users")->fetchAll();
    echo "<strong>Total users:</strong> " . count($users) . "<br><br>";
    
    echo "<table border='1' cellpadding='10'>";
    echo "<tr><th>ID</th><th>Username</th><th>Role</th><th>Status</th><th>Test Password</th></tr>";
    
    foreach ($users as $user) {
        $password_ok = password_verify('admin123', $user['password']) ? '✅ admin123 works' : '❌';
        
        echo "<tr>";
        echo "<td>" . $user['user_id'] . "</td>";
        echo "<td>" . $user['username'] . "</td>";
        echo "<td>" . $user['role'] . "</td>";
        echo "<td>" . $user['status'] . "</td>";
        echo "<td>" . $password_ok . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
} else {
    echo "❌ Database connection FAILED!<br>";
    echo "Check config/database.php settings";
}
?>