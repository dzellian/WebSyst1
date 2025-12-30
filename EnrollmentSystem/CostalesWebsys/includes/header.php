<?php
require_once 'functions.php';
require_once 'image_helper.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Enrollment System'; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <div class="nav-brand">
                <i class="fas fa-graduation-cap"></i>
                <span>Enrollment System</span>
            </div>
            <?php if (isLoggedIn()): ?>
<div class="nav-menu">
    <span class="nav-user">
        <?php if (isset($_SESSION['profile_picture']) && $_SESSION['profile_picture']): ?>
            <img src="<?php echo $_SERVER['REQUEST_URI']; ?>/../assets/uploads/profiles/<?php echo $_SESSION['profile_picture']; ?>" 
                 alt="Profile" class="nav-avatar"
                 onerror="this.onerror=null; this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 24 24%22%3E%3Cpath fill=%22%23999%22 d=%22M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z%22/%3E%3C/svg%3E';">
        <?php else: ?>
            <i class="fas fa-user-circle"></i>
        <?php endif; ?>
        <?php echo htmlspecialchars($_SESSION['full_name']); ?>
        <span class="badge badge-<?php echo $_SESSION['role']; ?>">
            <?php echo ucfirst($_SESSION['role']); ?>
        </span>
    </span>
    <a href="../logout.php" class="btn btn-sm btn-outline">
        <i class="fas fa-sign-out-alt"></i> Logout
    </a>
</div>
<?php endif; ?>
        </div>
    </nav>

    <?php if (isLoggedIn()): ?>
    <div class="sidebar">
        <div class="sidebar-menu">
            <?php
            $role = $_SESSION['role'];
            $menu_items = [
                'admin' => [
                    ['icon' => 'tachometer-alt', 'text' => 'Dashboard', 'url' => 'dashboard.php'],
                    ['icon' => 'book', 'text' => 'Subjects', 'url' => 'subjects.php'],
                    ['icon' => 'users', 'text' => 'Users', 'url' => 'users.php'],
                    ['icon' => 'clipboard-list', 'text' => 'Enrollments', 'url' => 'enrollments.php'],
                ],
                'faculty' => [
                    ['icon' => 'tachometer-alt', 'text' => 'Dashboard', 'url' => 'dashboard.php'],
                    ['icon' => 'chalkboard-teacher', 'text' => 'My Classes', 'url' => 'classes.php'],
                    ['icon' => 'star', 'text' => 'Submit Grades', 'url' => 'grades.php'],
                ],
                'student' => [
                    ['icon' => 'tachometer-alt', 'text' => 'Dashboard', 'url' => 'dashboard.php'],
                    ['icon' => 'user-graduate', 'text' => 'Enroll Subjects', 'url' => 'enroll.php'],
                    ['icon' => 'user', 'text' => 'My Profile', 'url' => 'profile.php'],
                ]
            ];

            foreach ($menu_items[$role] as $item):
                $active = (basename($_SERVER['PHP_SELF']) === $item['url']) ? 'active' : '';
            ?>
                <a href="<?php echo $item['url']; ?>" class="sidebar-item <?php echo $active; ?>">
                    <i class="fas fa-<?php echo $item['icon']; ?>"></i>
                    <span><?php echo $item['text']; ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <main class="main-content">
        <div class="container">