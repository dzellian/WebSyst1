<?php
$page_title = 'Faculty Dashboard';
require_once '../config/database.php';
require_once '../includes/header.php';
requireRole('faculty');

$database = new Database();
$db = $database->getConnection();
$faculty_id = $_SESSION['user_id'];

// Get statistics
$my_subjects_query = "SELECT COUNT(*) FROM subjects WHERE faculty_id = :id";
$my_subjects_stmt = $db->prepare($my_subjects_query);
$my_subjects_stmt->execute([':id' => $faculty_id]);
$subjects_count = $my_subjects_stmt->fetchColumn();

$total_students_query = "SELECT COUNT(DISTINCT e.student_id) 
                        FROM enrollments e
                        JOIN subjects s ON e.subject_id = s.subject_id
                        WHERE s.faculty_id = :id";
$total_students_stmt = $db->prepare($total_students_query);
$total_students_stmt->execute([':id' => $faculty_id]);
$students_count = $total_students_stmt->fetchColumn();

$pending_grades_query = "SELECT COUNT(*) 
                        FROM enrollments e
                        JOIN subjects s ON e.subject_id = s.subject_id
                        WHERE s.faculty_id = :id AND e.grade IS NULL";
$pending_grades_stmt = $db->prepare($pending_grades_query);
$pending_grades_stmt->execute([':id' => $faculty_id]);
$pending_count = $pending_grades_stmt->fetchColumn();

$completed_grades_query = "SELECT COUNT(*) 
                          FROM enrollments e
                          JOIN subjects s ON e.subject_id = s.subject_id
                          WHERE s.faculty_id = :id AND e.grade IS NOT NULL";
$completed_grades_stmt = $db->prepare($completed_grades_query);
$completed_grades_stmt->execute([':id' => $faculty_id]);
$completed_count = $completed_grades_stmt->fetchColumn();

// Get my subjects with enrollment counts
$subjects_query = "SELECT s.*, 
                   COUNT(CASE WHEN e.status = 'enrolled' THEN 1 END) as enrolled_count,
                   COUNT(CASE WHEN e.grade IS NULL THEN 1 END) as pending_grades
                   FROM subjects s
                   LEFT JOIN enrollments e ON s.subject_id = e.subject_id
                   WHERE s.faculty_id = :id
                   GROUP BY s.subject_id
                   ORDER BY s.subject_code";
$subjects_stmt = $db->prepare($subjects_query);
$subjects_stmt->execute([':id' => $faculty_id]);
$my_subjects_list = $subjects_stmt->fetchAll();
?>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">
            <i class="fas fa-tachometer-alt"></i>
            Faculty Dashboard
        </h2>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon primary">
            <i class="fas fa-book"></i>
        </div>
        <div class="stat-content">
            <h3><?php echo $subjects_count; ?></h3>
            <p>My Subjects</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon success">
            <i class="fas fa-users"></i>
        </div>
        <div class="stat-content">
            <h3><?php echo $students_count; ?></h3>
            <p>Total Students</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon warning">
            <i class="fas fa-clock"></i>
        </div>
        <div class="stat-content">
            <h3><?php echo $pending_count; ?></h3>
            <p>Pending Grades</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon info">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-content">
            <h3><?php echo $completed_count; ?></h3>
            <p>Graded Students</p>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-chalkboard"></i>
            My Subjects
        </h3>
    </div>
    <div class="card-body">
        <?php if (count($my_subjects_list) > 0): ?>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Subject Code</th>
                        <th>Subject Name</th>
                        <th>Units</th>
                        <th>Year/Semester</th>
                        <th>Enrolled Students</th>
                        <th>Pending Grades</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($my_subjects_list as $subject): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($subject['subject_code']); ?></strong></td>
                        <td><?php echo htmlspecialchars($subject['subject_name']); ?></td>
                        <td><?php echo $subject['units']; ?></td>
                        <td>Year <?php echo $subject['year_level']; ?> / Sem <?php echo $subject['semester']; ?></td>
                        <td>
                            <span class="badge badge-info"><?php echo $subject['enrolled_count']; ?> student(s)</span>
                        </td>
                        <td>
                            <?php if ($subject['pending_grades'] > 0): ?>
                                <span class="badge badge-warning"><?php echo $subject['pending_grades']; ?> pending</span>
                            <?php else: ?>
                                <span class="badge badge-success">All graded</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="btn-group">
                                <a href="classes.php?subject_id=<?php echo $subject['subject_id']; ?>" 
                                   class="btn btn-sm btn-primary"
                                   title="View Class List">
                                    <i class="fas fa-users"></i>
                                    Class
                                </a>
                                <a href="grades.php?subject_id=<?php echo $subject['subject_id']; ?>" 
                                   class="btn btn-sm btn-success"
                                   title="Submit Grades">
                                    <i class="fas fa-star"></i>
                                    Grades
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i>
            No subjects assigned to you yet. Please contact the administrator to assign subjects.
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>