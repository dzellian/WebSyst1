<?php
require_once 'config/db.php';
require_once 'includes/auth.php';

if (!isLoggedIn()) {
    header("Location: login.php");
}

$role = $_SESSION['role'];

if ($role == 'student') {
    header("Location: student/enroll.php");
} elseif ($role == 'faculty') {
    header("Location: faculty/class_list.php");
} elseif ($role == 'admin') {
    header("Location: admin/manage_subjects.php");
}
?>