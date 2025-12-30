<?php
$page_title = 'Submit Grades';
require_once '../config/database.php';
require_once '../includes/header.php';
requireRole('faculty');

$database = new Database();
$db = $database->getConnection();
$faculty_id = $_SESSION['user_id'];

$success = '';
$error = '';

// Handle grade submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_grades'])) {
    try {
        $db->beginTransaction();
        
        $updated = 0;
        $grade_query = "UPDATE enrollments SET grade = :grade, status = :status WHERE enrollment_id = :id";
        $grade_stmt = $db->prepare($grade_query);
        
        foreach ($_POST['grades'] as $enrollment_id => $grade_value) {
            $grade = !empty($grade_value) ? (float)$grade_value : null;
            
            if ($grade !== null) {
                // Determine status based on grade
                if ($grade >= 3.0) {
                    $status = 'completed';
                } elseif ($grade > 0) {
                    $status = 'failed';
                } else {
                    $status = 'enrolled';
                }
                
                $grade_stmt->execute([
                    ':grade' => $grade,
                    ':status' => $status,
                    ':id' => $enrollment_id
                ]);
                $updated++;
            }
        }
        
        $db->commit();
        $success = "Successfully updated $updated grade(s)!";
    } catch (Exception $e) {
        $db->rollBack();
        $error = 'Failed to submit grades: ' . $e->getMessage();
    }
}

$subject_id = isset($_GET['subject_id']) ? (int)$_GET['subject_id'] : 0;

if ($subject_id == 0) {
    // No subject selected, show list of subjects
    $subjects_query = "SELECT s.*, 
                       COUNT(e.enrollment_id) as total_students,
                       SUM(CASE WHEN e.grade IS NULL THEN 1 ELSE 0 END) as pending_grades
                       FROM subjects s
                       LEFT JOIN enrollments e ON s.subject_id = e.subject_id
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
                <i class="fas fa-star"></i>
                Submit Grades
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
                            <th>Total Students</th>
                            <th>Pending Grades</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($subjects as $subject): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($subject['subject_code']); ?></strong></td>
                            <td><?php echo htmlspecialchars($subject['subject_name']); ?></td>
                            <td>
                                <span class="badge badge-info"><?php echo $subject['total_students']; ?></span>
                            </td>
                            <td>
                                <?php if ($subject['pending_grades'] > 0): ?>
                                    <span class="badge badge-warning"><?php echo $subject['pending_grades']; ?> pending</span>
                                <?php else: ?>
                                    <span class="badge badge-success">All graded</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="grades.php?subject_id=<?php echo $subject['subject_id']; ?>" 
                                   class="btn btn-sm btn-primary">
                                    <i class="fas fa-edit"></i>
                                    Grade Students
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
                No subjects assigned to you yet.
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <?php
    require_once '../includes/footer.php';
    exit();
}

// Get subject
$subject_query = "SELECT * FROM subjects WHERE subject_id = :id AND faculty_id = :faculty_id";
$subject_stmt = $db->prepare($subject_query);
$subject_stmt->execute([':id' => $subject_id, ':faculty_id' => $faculty_id]);

if ($subject_stmt->rowCount() == 0) {
    $_SESSION['error'] = 'You do not have access to this subject.';
    header("Location: grades.php");
    exit();
}

$subject = $subject_stmt->fetch();

// Get students for grading
$students_query = "SELECT e.*, u.full_name, u.profile_picture
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
                <i class="fas fa-star"></i>
                Submit Grades
            </h2>
            <p class="text-muted mb-0">
                <?php echo htmlspecialchars($subject['subject_code'] . ' - ' . $subject['subject_name']); ?>
            </p>
        </div>
        <div class="btn-group">
            <a href="classes.php?subject_id=<?php echo $subject_id; ?>" class="btn btn-outline btn-sm">
                <i class="fas fa-users"></i>
                View Class
            </a>
            <a href="grades.php" class="btn btn-outline btn-sm">
                <i class="fas fa-arrow-left"></i>
                Back
            </a>
        </div>
    </div>
</div>

<?php if ($success): ?>
<div class="alert alert-success">
    <i class="fas fa-check-circle"></i>
    <?php echo $success; ?>
</div>
<?php endif; ?>

<?php if ($error): ?>
<div class="alert alert-danger">
    <i class="fas fa-exclamation-circle"></i>
    <?php echo $error; ?>
</div>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
<div class="alert alert-danger">
    <i class="fas fa-exclamation-circle"></i>
    <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <div class="alert alert-info mb-3">
            <i class="fas fa-info-circle"></i>
            <strong>Grading Scale:</strong>
            <ul style="margin: 10px 0 0 20px;">
                <li><strong>1.0 - 1.5:</strong> Excellent</li>
                <li><strong>1.75 - 2.5:</strong> Very Good</li>
                <li><strong>2.75 - 3.0:</strong> Good (Passing)</li>
                <li><strong>Below 3.0:</strong> Failed</li>
                <li><strong>5.0:</strong> Failed</li>
            </ul>
        </div>
        
        <?php if (count($students) > 0): ?>
        <form method="POST">
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Photo</th>
                            <th>Student Name</th>
                            <th>School Year</th>
                            <th>Semester</th>
                            <th>Current Status</th>
                            <th>Current Grade</th>
                            <th>New Grade</th>
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
                                    <strong style="font-size: 1.1rem;"><?php echo formatGrade($student['grade']); ?></strong>
                                    <?php if ($student['grade'] >= 3.0): ?>
                                        <br><span class="badge badge-success">Passed</span>
                                    <?php else: ?>
                                        <br><span class="badge badge-danger">Failed</span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="badge badge-warning">Not Graded</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <input type="number" 
                                       name="grades[<?php echo $student['enrollment_id']; ?>]" 
                                       class="form-control" 
                                       style="max-width: 150px;"
                                       min="1.0" max="5.0" step="0.25"
                                       value="<?php echo $student['grade'] ?? ''; ?>"
                                       placeholder="e.g. 1.75">
                                <small class="form-text">1.0 to 5.0</small>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="mt-3">
                <button type="submit" name="submit_grades" class="btn btn-primary btn-lg">
                    <i class="fas fa-save"></i>
                    Save All Grades
                </button>
                <button type="reset" class="btn btn-outline btn-lg">
                    <i class="fas fa-undo"></i>
                    Reset
                </button>
            </div>
        </form>
        <?php else: ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i>
            No students enrolled in this subject.
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Grade validation
document.addEventListener('DOMContentLoaded', function() {
    const gradeInputs = document.querySelectorAll('input[name^="grades"]');
    
    gradeInputs.forEach(input => {
        input.addEventListener('input', function() {
            const value = parseFloat(this.value);
            
            if (this.value === '') {
                this.style.borderColor = '';
                return;
            }
            
            if (isNaN(value) || value < 1.0 || value > 5.0) {
                this.style.borderColor = 'var(--danger)';
                this.setCustomValidity('Grade must be between 1.0 and 5.0');
            } else {
                this.style.borderColor = 'var(--success)';
                this.setCustomValidity('');
                
                // Color code based on pass/fail
                if (value >= 3.0) {
                    this.style.backgroundColor = '#d1fae5';
                } else {
                    this.style.backgroundColor = '#fee2e2';
                }
            }
        });
        
        // Trigger validation on load if value exists
        if (input.value) {
            input.dispatchEvent(new Event('input'));
        }
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>