<?php
$page_title = 'Admin Dashboard';
require_once '../config/database.php';
require_once '../includes/header.php';
requireRole('admin');

$database = new Database();
$db = $database->getConnection();

// Get statistics
$stats = [
    'students' => $db->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetchColumn(),
    'faculty' => $db->query("SELECT COUNT(*) FROM users WHERE role = 'faculty'")->fetchColumn(),
    'subjects' => $db->query("SELECT COUNT(*) FROM subjects")->fetchColumn(),
    'enrollments' => $db->query("SELECT COUNT(*) FROM enrollments WHERE status = 'enrolled'")->fetchColumn(),
];

// Recent enrollments
$recent_query = "SELECT e.*, u.full_name, s.subject_code, s.subject_name 
                 FROM enrollments e
                 JOIN users u ON e.student_id = u.user_id
                 JOIN subjects s ON e.subject_id = s.subject_id
                 ORDER BY e.enrolled_at DESC
                 LIMIT 10";
$recent_stmt = $db->query($recent_query);
$recent_enrollments = $recent_stmt->fetchAll();
?>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">
            <i class="fas fa-tachometer-alt"></i>
            Dashboard Overview
        </h2>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon primary">
            <i class="fas fa-user-graduate"></i>
        </div>
        <div class="stat-content">
            <h3><?php echo $stats['students']; ?></h3>
            <p>Total Students</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon success">
            <i class="fas fa-chalkboard-teacher"></i>
        </div>
        <div class="stat-content">
            <h3><?php echo $stats['faculty']; ?></h3>
            <p>Faculty Members</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon warning">
            <i class="fas fa-book"></i>
        </div>
        <div class="stat-content">
            <h3><?php echo $stats['subjects']; ?></h3>
            <p>Total Subjects</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon info">
            <i class="fas fa-clipboard-list"></i>
        </div>
        <div class="stat-content">
            <h3><?php echo $stats['enrollments']; ?></h3>
            <p>Active Enrollments</p>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-history"></i>
            Recent Enrollments
        </h3>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Subject</th>
                        <th>School Year</th>
                        <th>Semester</th>
                        <th>Status</th>
                        <th>Enrolled Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_enrollments as $enrollment): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($enrollment['full_name']); ?></td>
                        <td><?php echo htmlspecialchars($enrollment['subject_code'] . ' - ' . $enrollment['subject_name']); ?></td>
                        <td><?php echo htmlspecialchars($enrollment['school_year']); ?></td>
                        <td><?php echo $enrollment['semester']; ?></td>
                        <td>
                            <span class="badge badge-<?php echo $enrollment['status'] == 'enrolled' ? 'success' : 'info'; ?>">
                                <?php echo ucfirst($enrollment['status']); ?>
                            </span>
                        </td>
                        <td><?php echo date('M d, Y', strtotime($enrollment['enrolled_at'])); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>