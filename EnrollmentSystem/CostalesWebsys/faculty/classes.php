<?php
$page_title = 'Class List';
require_once '../config/database.php';
require_once '../includes/header.php';
requireRole('faculty');

$database = new Database();
$db = $database->getConnection();
$faculty_id = $_SESSION['user_id'];

$subject_id = isset($_GET['subject_id']) ? (int)$_GET['subject_id'] : 0;

if ($subject_id == 0) {
    // No subject selected, show list of subjects
    $subjects_query = "SELECT s.*, COUNT(e.enrollment_id) as enrolled_count
                       FROM subjects s
                       LEFT JOIN enrollments e ON s.subject_id = e.subject_id AND e.status = 'enrolled'
                       WHERE s.faculty_id = :faculty_id
                       GROUP BY s.subject_id
                       ORDER BY s.subject_code";
    $subjects_stmt = $db->prepare($subjects_query);
    $subjects_stmt->execute([':faculty_id' => $faculty_id]);
    $subjects = $subjects_stmt->fetchAll();
    ?>
    
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">
                <i class="fas fa-chalkboard"></i>
                My Classes
            </h2>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <?php if (count($subjects) > 0): ?>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Subject Code</th>
                            <th>Subject Name</th>
                            <th>Units</th>
                            <th>Year/Semester</th>
                            <th>Enrolled Students</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($subjects as $subject): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($subject['subject_code']); ?></strong></td>
                            <td><?php echo htmlspecialchars($subject['subject_name']); ?></td>
                            <td><?php echo $subject['units']; ?></td>
                            <td>Year <?php echo $subject['year_level']; ?> / Sem <?php echo $subject['semester']; ?></td>
                            <td>
                                <span class="badge badge-info"><?php echo $subject['enrolled_count']; ?> student(s)</span>
                            </td>
                            <td>
                                <a href="classes.php?subject_id=<?php echo $subject['subject_id']; ?>" 
                                   class="btn btn-sm btn-primary">
                                    <i class="fas fa-users"></i>
                                    View Class List
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i>
                No subjects assigned to you yet. Please contact the administrator.
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <?php
    require_once '../includes/footer.php';
    exit();
}

// Verify subject belongs to faculty
$verify_query = "SELECT * FROM subjects WHERE subject_id = :id AND faculty_id = :faculty_id";
$verify_stmt = $db->prepare($verify_query);
$verify_stmt->execute([':id' => $subject_id, ':faculty_id' => $faculty_id]);

if ($verify_stmt->rowCount() == 0) {
    $_SESSION['error'] = 'You do not have access to this subject.';
    header("Location: classes.php");
    exit();
}

$subject = $verify_stmt->fetch();

// Get enrolled students
$students_query = "SELECT e.*, u.full_name, u.email, u.profile_picture, u.signature
                   FROM enrollments e
                   JOIN users u ON e.student_id = u.user_id
                   WHERE e.subject_id = :subject_id
                   ORDER BY e.status, u.full_name";
$students_stmt = $db->prepare($students_query);
$students_stmt->execute([':subject_id' => $subject_id]);
$students = $students_stmt->fetchAll();
?>

<div class="card">
    <div class="card-header">
        <div>
            <h2 class="card-title">
                <i class="fas fa-users"></i>
                Class List
            </h2>
            <p class="text-muted mb-0">
                <?php echo htmlspecialchars($subject['subject_code'] . ' - ' . $subject['subject_name']); ?>
            </p>
        </div>
        <div class="btn-group">
            <a href="grades.php?subject_id=<?php echo $subject_id; ?>" class="btn btn-primary btn-sm">
                <i class="fas fa-star"></i>
                Submit Grades
            </a>
            <a href="classes.php" class="btn btn-outline btn-sm">
                <i class="fas fa-arrow-left"></i>
                Back
            </a>
        </div>
    </div>
</div>

<?php if (isset($_SESSION['error'])): ?>
<div class="alert alert-danger">
    <i class="fas fa-exclamation-circle"></i>
    <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-list"></i>
            Enrolled Students (<?php echo count($students); ?>)
        </h3>
    </div>
    <div class="card-body">
        <?php if (count($students) > 0): ?>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Photo</th>
                        <th>Student Name</th>
                        <th>Email</th>
                        <th>Signature</th>
                        <th>School Year</th>
                        <th>Semester</th>
                        <th>Status</th>
                        <th>Grade</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $student): ?>
                    <tr>
                        <td>
                            <?php 
                            if ($student['profile_picture']): 
                                $img_path = "../assets/uploads/profiles/" . $student['profile_picture'];
                                if (file_exists($img_path)):
                            ?>
                                <img src="<?php echo $img_path; ?>" alt="Profile" class="profile-img">
                            <?php else: ?>
                                <div class="profile-placeholder profile-img">
                                    <i class="fas fa-user"></i>
                                </div>
                            <?php 
                                endif;
                            else: 
                            ?>
                                <div class="profile-placeholder profile-img">
                                    <i class="fas fa-user"></i>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td><strong><?php echo htmlspecialchars($student['full_name']); ?></strong></td>
                        <td><?php echo htmlspecialchars($student['email']); ?></td>
                        <td>
                            <?php 
                            if ($student['signature']): 
                                $sig_path = "../assets/uploads/signatures/" . $student['signature'];
                                if (file_exists($sig_path)):
                            ?>
                                <img src="<?php echo $sig_path; ?>" alt="Signature" class="signature-img">
                            <?php else: ?>
                                <span class="text-muted">No signature</span>
                            <?php 
                                endif;
                            else: 
                            ?>
                                <span class="text-muted">No signature</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($student['school_year']); ?></td>
                        <td>
                            <?php 
                            $semesters = ['', '1st Semester', '2nd Semester', 'Summer'];
                            echo $semesters[$student['semester']] ?? $student['semester']; 
                            ?>
                        </td>
                        <td>
                            <?php
                            $status_badge = [
                                'enrolled' => 'info',
                                'completed' => 'success',
                                'failed' => 'danger',
                                'dropped' => 'warning'
                            ];
                            $badge_class = $status_badge[$student['status']] ?? 'info';
                            ?>
                            <span class="badge badge-<?php echo $badge_class; ?>">
                                <?php echo ucfirst($student['status']); ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($student['grade']): ?>
                                <strong><?php echo formatGrade($student['grade']); ?></strong>
                                <?php if ($student['grade'] >= 3.0): ?>
                                    <span class="badge badge-success">Passed</span>
                                <?php else: ?>
                                    <span class="badge badge-danger">Failed</span>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="badge badge-warning">Pending</span>
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
            No students enrolled in this subject yet.
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>