<?php
// C:\xampp\htdocs\Costales_ES\includes\header.php

// Load auth + DB
require_once __DIR__ . '/auth.php';

// Block access if not logged in
if (!isLoggedIn()) {
    // from /admin/... or /student/... we need to go back one level to login
    header("Location: ../login.php");
    exit;
}

// Build role-based links (use absolute paths from project root)
$baseUrl = '/Costales_ES';   // <-- adjust if your folder name is different

$role = $_SESSION['role'];

if ($role === 'student') {
    $dashboard_link = "$baseUrl/student/dashboard.php";
    $profile_link   = "$baseUrl/student/profile.php";
} elseif ($role === 'faculty') {
    $dashboard_link = "$baseUrl/faculty/dashboard.php";
    $profile_link   = "$baseUrl/faculty/profile.php";
} else { // admin
    $dashboard_link = "$baseUrl/admin/manage_subjects.php";
    $profile_link   = "$baseUrl/admin/profile.php"; // create later if needed
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Enrollment System</title>
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>/css/style.css">
</head>
<body>
<header>
    <div class="container">
        <nav>
            <h1>Enrollment System</h1>
            <div>
                <a href="<?php echo $dashboard_link; ?>">Dashboard</a>
                <a href="<?php echo $profile_link; ?>">Profile</a>
                <a href="<?php echo $baseUrl; ?>/logout.php">Logout</a>
            </div>
        </nav>
    </div>
</header>
<div class="container">