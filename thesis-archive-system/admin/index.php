<?php
$page_title = "Admin Dashboard";
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../includes/functions.php';

requireRole('admin');

$database = new Database();
$conn = $database->getConnection();

// Statistics
$stats = [
    'users' => $conn->query("SELECT COUNT(*) FROM users")->fetchColumn(),
    'students' => $conn->query("SELECT COUNT(*) FROM users WHERE role='student'")->fetchColumn(),
    'advisers' => $conn->query("SELECT COUNT(*) FROM users WHERE role='adviser'")->fetchColumn(),
    'thesis' => $conn->query("SELECT COUNT(*) FROM thesis")->fetchColumn(),
    'pending' => $conn->query("SELECT COUNT(*) FROM thesis WHERE status='pending'")->fetchColumn(),
    'approved' => $conn->query("SELECT COUNT(*) FROM thesis WHERE status='approved'")->fetchColumn(),
];

require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<div class="container mt-4">
    <h2><i class="fas fa-tachometer-alt"></i> Admin Dashboard</h2>
    <hr>
    
    <div class="row">
        <div class="col-md-3">
            <div class="card bg-primary text-white mb-3">
                <div class="card-body">
                    <h5>Total Users</h5>
                    <h2><?php echo $stats['users']; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white mb-3">
                <div class="card-body">
                    <h5>Students</h5>
                    <h2><?php echo $stats['students']; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white mb-3">
                <div class="card-body">
                    <h5>Advisers</h5>
                    <h2><?php echo $stats['advisers']; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white mb-3">
                <div class="card-body">
                    <h5>Total Thesis</h5>
                    <h2><?php echo $stats['thesis']; ?></h2>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h5>Quick Access</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <a href="users.php" class="btn btn-primary w-100">
                                <i class="fas fa-users"></i><br>Manage Users
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="departments.php" class="btn btn-info w-100">
                                <i class="fas fa-building"></i><br>Departments
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="programs.php" class="btn btn-success w-100">
                                <i class="fas fa-graduation-cap"></i><br>Programs
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="approve-thesis.php" class="btn btn-warning w-100">
                                <i class="fas fa-check-circle"></i><br>Approve Thesis
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="activity-logs.php" class="btn btn-secondary w-100">
                                <i class="fas fa-history"></i><br>Activity Logs
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="backup.php" class="btn btn-danger w-100">
                                <i class="fas fa-database"></i><br>Backup System
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>