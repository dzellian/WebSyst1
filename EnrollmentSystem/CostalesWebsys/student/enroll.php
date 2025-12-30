<?php
$page_title = 'Enroll Subjects';
require_once '../config/database.php';
require_once '../includes/header.php';
requireRole('student');

$database = new Database();
$db = $database->getConnection();
$student_id = $_SESSION['user_id'];

$success = '';
$error = '';

// Handle enrollment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['subject_id'])) {
    $subject_id = (int)$_POST['subject_id'];
    $semester = (int)$_POST['semester'];
    $school_year = clean($_POST['school_year']);
    
    // Check prerequisites
    $missing = checkPrerequisites($db, $student_id, $subject_id);
    
    if (!empty($missing)) {
        $error = 'Missing prerequisites: ' . implode(', ', $missing);
    } else {
        // Check if already enrolled
        $check_query = "SELECT enrollment_id FROM enrollments 
                       WHERE student_id = :student_id AND subject_id = :subject_id 
                       AND semester = :semester AND school_year = :school_year";
        $check_stmt = $db->prepare($check_query);
        $check_stmt->execute([
            ':student_id' => $student_id,
            ':subject_id' => $subject_id,
            ':semester' => $semester,
            ':school_year' => $school_year
        ]);
        
        if ($check_stmt->rowCount() > 0) {
            $error = 'You are already enrolled in this subject for the selected semester.';
        } else {
            // Enroll student
            $enroll_query = "INSERT INTO enrollments (student_id, subject_id, semester, school_year, status) 
                           VALUES (:student_id, :subject_id, :semester, :school_year, 'enrolled')";
            $enroll_stmt = $db->prepare($enroll_query);
            
            if ($enroll_stmt->execute([
                ':student_id' => $student_id,
                ':subject_id' => $subject_id,
                ':semester' => $semester,
                ':school_year' => $school_year
            ])) {
                $success = 'Successfully enrolled in the subject!';
            } else {
                $error = 'Failed to enroll. Please try again.';
            }
        }
    }
}

// Get available subjects
$subjects_query = "SELECT s.*, u.full_name as faculty_name,
                   (SELECT COUNT(*) FROM prerequisites WHERE subject_id = s.subject_id) as prereq_count,
                   (SELECT GROUP_CONCAT(req_s.subject_code SEPARATOR ', ')
                    FROM prerequisites p
                    JOIN subjects req_s ON p.required_subject_id = req_s.subject_id
                    WHERE p.subject_id = s.subject_id) as prereq_list
                   FROM subjects s
                   LEFT JOIN users u ON s.faculty_id = u.user_id
                   ORDER BY s.year_level, s.semester, s.subject_code";
$subjects = $db->query($subjects_query)->fetchAll();

$current_school_year = getCurrentSchoolYear();
?>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">
            <i class="fas fa-user-graduate"></i>
            Enroll in Subjects
        </h2>
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

<div class="card">
    <div class="card-body">
        <div class="alert alert-info mb-3">
            <i class="fas fa-info-circle"></i>
            <strong>Note:</strong> You can only enroll in subjects if you have completed all prerequisite subjects with a passing grade (3.0 or higher).
        </div>
        
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Subject Name</th>
                        <th>Units</th>
                        <th>Year/Sem</th>
                        <th>Faculty</th>
                        <th>Prerequisites</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($subjects as $subject): ?>
                    <?php
                        $missing = checkPrerequisites($db, $student_id, $subject['subject_id']);
                        $can_enroll = empty($missing);
                    ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($subject['subject_code']); ?></strong></td>
                        <td>
                            <?php echo htmlspecialchars($subject['subject_name']); ?>
                            <?php if ($subject['description']): ?>
                            <br><small class="text-muted"><?php echo htmlspecialchars($subject['description']); ?></small>
                            <?php endif; ?>
                        </td>
                        <td><?php echo $subject['units']; ?></td>
                        <td>
                            Year <?php echo $subject['year_level']; ?><br>
                            Sem <?php echo $subject['semester']; ?>
                        </td>
                        <td><?php echo htmlspecialchars($subject['faculty_name'] ?? 'TBA'); ?></td>
                        <td>
                            <?php if ($subject['prereq_count'] > 0): ?>
                                <span class="badge badge-warning" 
                                      title="<?php echo htmlspecialchars($subject['prereq_list']); ?>">
                                    <?php echo $subject['prereq_count']; ?> required
                                </span>
                                <?php if (!$can_enroll): ?>
                                <br><small class="text-danger">Missing: <?php echo implode(', ', $missing); ?></small>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="badge badge-success">None</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($can_enroll): ?>
                            <button class="btn btn-sm btn-primary" 
                                    onclick="openEnrollModal(<?php echo $subject['subject_id']; ?>, '<?php echo htmlspecialchars($subject['subject_code']); ?>', '<?php echo htmlspecialchars($subject['subject_name']); ?>')">
                                <i class="fas fa-plus"></i>
                                Enroll
                            </button>
                            <?php else: ?>
                            <button class="btn btn-sm btn-outline" disabled>
                                <i class="fas fa-lock"></i>
                                Locked
                            </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Enrollment Modal -->
<div id="enrollModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Confirm Enrollment</h3>
            <button class="modal-close" onclick="closeModal('enrollModal')">&times;</button>
        </div>
        <form method="POST">
            <div class="modal-body">
                <input type="hidden" name="subject_id" id="modal_subject_id">
                
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    You are enrolling in: <strong id="modal_subject_name"></strong>
                </div>
                
                <div class="form-group">
                    <label class="form-label required">School Year</label>
                    <input type="text" name="school_year" class="form-control" 
                           value="<?php echo $current_school_year; ?>" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label required">Semester</label>
                    <select name="semester" class="form-control" required>
                        <option value="1">1st Semester</option>
                        <option value="2">2nd Semester</option>
                        <option value="3">Summer</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('enrollModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-check"></i>
                    Confirm Enrollment
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openEnrollModal(subjectId, subjectCode, subjectName) {
    document.getElementById('modal_subject_id').value = subjectId;
    document.getElementById('modal_subject_name').textContent = subjectCode + ' - ' + subjectName;
    openModal('enrollModal');
}
</script>

<?php require_once '../includes/footer.php'; ?>