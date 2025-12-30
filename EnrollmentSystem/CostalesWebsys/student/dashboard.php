<?php
$page_title = 'Student Dashboard';
require_once '../config/database.php';
require_once '../includes/header.php';
requireRole('student');

$database = new Database();
$db = $database->getConnection();
$student_id = $_SESSION['user_id'];

// Get student statistics
$enrolled_count = $db->prepare("SELECT COUNT(*) FROM enrollments WHERE student_id = :id AND status = 'enrolled'");
$enrolled_count->execute([':id' => $student_id]);
$enrolled = $enrolled_count->fetchColumn();

$completed_count = $db->prepare("SELECT COUNT(*) FROM enrollments WHERE student_id = :id AND status = 'completed'");
$completed_count->execute([':id' => $student_id]);
$completed = $completed_count->fetchColumn();

$gpa_query = "SELECT AVG(grade) as gpa FROM enrollments WHERE student_id = :id AND status = 'completed' AND grade IS NOT NULL";
$gpa_stmt = $db->prepare($gpa_query);
$gpa_stmt->execute([':id' => $student_id]);
$gpa = $gpa_stmt->fetch()['gpa'] ?? 0;

// Get current enrollments
$current_query = "SELECT e.*, s.subject_code, s.subject_name, s.units, s.semester, 
                  u.full_name as faculty_name
                  FROM enrollments e
                  JOIN subjects s ON e.subject_id = s.subject_id
                  LEFT JOIN users u ON s.faculty_id = u.user_id
                  WHERE e.student_id = :id AND e.status = 'enrolled'
                  ORDER BY s.subject_code";
$current_stmt = $db->prepare($current_query);
$current_stmt->execute([':id' => $student_id]);
$current_enrollments = $current_stmt->fetchAll();

// Get recent grades
$grades_query = "SELECT e.*, s.subject_code, s.subject_name, s.units
                 FROM enrollments e
                 JOIN subjects s ON e.subject_id = s.subject_id
                 WHERE e.student_id = :id AND e.status = 'completed' AND e.grade IS NOT NULL
                 ORDER BY e.enrolled_at DESC
                 LIMIT 5";
$grades_stmt = $db->prepare($grades_query);
$grades_stmt->execute([':id' => $student_id]);
$recent_grades = $grades_stmt->fetchAll();
?>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">
            <i class="fas fa-tachometer-alt"></i>
            Student Dashboard
        </h2>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon primary">
            <i class="fas fa-book-open"></i>
        </div>
        <div class="stat-content">
            <h3><?php echo $enrolled; ?></h3>
            <p>Current Enrollments</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon success">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-content">
            <h3><?php echo $completed; ?></h3>
            <p>Completed Subjects</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon warning">
            <i class="fas fa-chart-line"></i>
        </div>
        <div class="stat-content">
            <h3><?php echo number_format($gpa, 2); ?></h3>
            <p>Grade Point Average</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon info">
            <i class="fas fa-graduation-cap"></i>
        </div>
        <div class="stat-content">
            <h3><?php echo ($enrolled * 3); ?></h3>
            <p>Total Units (Current)</p>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-clipboard-list"></i>
            Current Enrollments
        </h3>
        <a href="enroll.php" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i>
            Enroll New Subject
        </a>
    </div>
    <div class="card-body">
        <?php if (count($current_enrollments) > 0): ?>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Subject Code</th>
                        <th>Subject Name</th>
                        <th>Units</th>
                        <th>Faculty</th>
                        <th>Semester</th>
                        <th>School Year</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($current_enrollments as $enrollment): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($enrollment['subject_code']); ?></strong></td>
                        <td><?php echo htmlspecialchars($enrollment['subject_name']); ?></td>
                        <td><?php echo $enrollment['units']; ?></td>
                        <td><?php echo htmlspecialchars($enrollment['faculty_name'] ?? 'TBA'); ?></td>
                        <td><?php echo $enrollment['semester']; ?></td>
                        <td><?php echo htmlspecialchars($enrollment['school_year']); ?></td>
                        <td>
                            <span class="badge badge-success">
                                <?php echo ucfirst($enrollment['status']); ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i>
            You are not currently enrolled in any subjects. <a href="enroll.php">Enroll now</a>
        </div>
        <?php endif; ?>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-star"></i>
            Recent Grades
        </h3>
    </div>
    <div class="card-body">
        <?php if (count($recent_grades) > 0): ?>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Subject Code</th>
                        <th>Subject Name</th>
                        <th>Units</th>
                        <th>Grade</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_grades as $grade): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($grade['subject_code']); ?></strong></td>
                        <td><?php echo htmlspecialchars($grade['subject_name']); ?></td>
                        <td><?php echo $grade['units']; ?></td>
                        <td><strong><?php echo formatGrade($grade['grade']); ?></strong></td>
                        <td>
                            <?php if ($grade['grade'] >= 3.0): ?>
                                <span class="badge badge-success">Passed</span>
                            <?php else: ?>
                                <span class="badge badge-danger">Failed</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i>
            No grades available yet.
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>