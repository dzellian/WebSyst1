<?php
require_once 'includes/functions.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

// Redirect to appropriate dashboard
switch ($_SESSION['role']) {
    case 'admin':
        header("Location: admin/dashboard.php");
        break;
    case 'faculty':
        header("Location: faculty/dashboard.php");
        break;
    case 'student':
        header("Location: student/dashboard.php");
        break;
    default:
        header("Location: login.php");
}
exit();
?>