<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>THESIS SYSTEM - LOGIN DEBUG</h2>";
echo "<hr>";

// Test 1: Database Connection
echo "<h3>TEST 1: Database Connection</h3>";
try {
    require_once 'config/database.php';
    $database = new Database();
    $conn = $database->getConnection();
    
    if ($conn) {
        echo "✅ Database connected successfully!<br>";
    } else {
        echo "❌ Database connection FAILED!<br>";
        die("STOP: Fix database connection first!");
    }
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "<br>";
    die("STOP: Fix the error above!");
}

// Test 2: Check Users Table
echo "<hr><h3>TEST 2: Check Users Table</h3>";
try {
    $result = $conn->query("SELECT COUNT(*) as total FROM users");
    $count = $result->fetchColumn();
    echo "✅ Users table exists. Total users: <strong>$count</strong><br>";
    
    if ($count == 0) {
        echo "⚠️ WARNING: No users found! Creating admin now...<br>";
        
        $hash = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (username, email, password, first_name, last_name, role, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute(['admin', 'admin@thesis.com', $hash, 'System', 'Administrator', 'admin', 'active']);
        
        echo "✅ Admin created!<br>";
    }
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "<br>";
}

// Test 3: Find Admin Account
echo "<hr><h3>TEST 3: Find Admin Account</h3>";
$stmt = $conn->prepare("SELECT * FROM users WHERE username = 'admin'");
$stmt->execute();
$admin = $stmt->fetch();

if ($admin) {
    echo "✅ Admin account found!<br>";
    echo "<table border='1' cellpadding='5' style='border-collapse:collapse;'>";
    echo "<tr><th>Field</th><th>Value</th></tr>";
    echo "<tr><td>user_id</td><td>" . $admin['user_id'] . "</td></tr>";
    echo "<tr><td>username</td><td><strong>" . $admin['username'] . "</strong></td></tr>";
    echo "<tr><td>email</td><td>" . $admin['email'] . "</td></tr>";
    echo "<tr><td>role</td><td><strong>" . $admin['role'] . "</strong></td></tr>";
    echo "<tr><td>status</td><td><strong>" . $admin['status'] . "</strong></td></tr>";
    echo "<tr><td>password hash</td><td style='font-size:10px;'>" . $admin['password'] . "</td></tr>";
    echo "</table><br>";
} else {
    echo "❌ Admin account NOT FOUND!<br>";
    echo "Creating admin account now...<br>";
    
    $hash = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (username, email, password, first_name, last_name, role, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute(['admin', 'admin@thesis.com', $hash, 'System', 'Administrator', 'admin', 'active']);
    
    echo "✅ Admin created! Refresh this page.<br>";
    die();
}

// Test 4: Password Verification
echo "<hr><h3>TEST 4: Password Verification</h3>";
$test_passwords = ['admin123', 'admin', 'password', '123456', 'admin1234'];

foreach ($test_passwords as $test_pass) {
    if (password_verify($test_pass, $admin['password'])) {
        echo "✅ <strong style='color:green; font-size:20px;'>PASSWORD FOUND: '$test_pass'</strong><br>";
        break;
    } else {
        echo "❌ Not: '$test_pass'<br>";
    }
}

// Test 5: Manual Login Test
echo "<hr><h3>TEST 5: Manual Login Simulation</h3>";

require_once 'config/functions.php';

$test_username = 'admin';
$test_password = 'admin123';

echo "Testing with:<br>";
echo "Username: <strong>$test_username</strong><br>";
echo "Password: <strong>$test_password</strong><br><br>";

$stmt = $conn->prepare("SELECT * FROM users WHERE username = ? AND status = 'active'");
$stmt->execute([$test_username]);
$user = $stmt->fetch();

if ($user) {
    echo "✅ User found in database<br>";
    
    if (password_verify($test_password, $user['password'])) {
        echo "✅ Password is CORRECT!<br>";
        echo "✅ Role: " . $user['role'] . "<br>";
        
        // Start session and login
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['first_name'] = $user['first_name'];
        $_SESSION['last_name'] = $user['last_name'];
        
        echo "✅ Session created!<br>";
        echo "<pre>";
        print_r($_SESSION);
        echo "</pre>";
        
        echo "<hr>";
        echo "<h3 style='color:green;'>SUCCESS! You should be able to login now.</h3>";
        echo "<a href='auth/login.php' style='background:green;color:white;padding:15px 30px;text-decoration:none;font-size:18px;border-radius:5px;display:inline-block;margin-top:10px;'>TRY LOGIN PAGE NOW</a><br><br>";
        echo "<a href='admin/index.php' style='background:blue;color:white;padding:15px 30px;text-decoration:none;font-size:18px;border-radius:5px;display:inline-block;margin-top:10px;'>OR GO DIRECTLY TO ADMIN PANEL</a>";
        
    } else {
        echo "❌ Password is WRONG!<br>";
        echo "Resetting password to 'admin123'...<br>";
        
        $new_hash = password_hash('admin123', PASSWORD_DEFAULT);
        $conn->prepare("UPDATE users SET password = ? WHERE username = 'admin'")->execute([$new_hash]);
        
        echo "✅ Password reset! Refresh this page and try again.<br>";
    }
} else {
    echo "❌ User not found or account is inactive!<br>";
}

// Test 6: Check config/database.php settings
echo "<hr><h3>TEST 6: Database Configuration</h3>";
echo "<pre>";
echo "Host: localhost\n";
echo "Database: thesis_archive_db\n";
echo "Username: root\n";
echo "Password: (empty)\n";
echo "</pre>";
echo "⚠️ If these settings are wrong, edit config/database.php<br>";

// Test 7: Check if includes folder exists
echo "<hr><h3>TEST 7: Check Required Files</h3>";
$required_files = [
    'config/database.php',
    'config/config.php',
    'config/session.php',
    'includes/functions.php',
    'auth/login.php',
    'admin/index.php'
];

foreach ($required_files as $file) {
    if (file_exists($file)) {
        echo "✅ $file exists<br>";
    } else {
        echo "❌ <strong>MISSING: $file</strong><br>";
    }
}

echo "<hr>";
echo "<h3>What to do next:</h3>";
echo "<ol>";
echo "<li>Check all the tests above</li>";
echo "<li>Look for ❌ (red X) - those are problems</li>";
echo "<li>If password was found/reset, try logging in</li>";
echo "<li>If you see 'SUCCESS' message, click the button above</li>";
echo "<li>Delete this debug_login.php file after fixing</li>";
echo "</ol>";
?>