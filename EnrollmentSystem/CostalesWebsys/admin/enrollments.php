<?php
$page_title = 'Manage Enrollments';
require_once '../config/database.php';
require_once '../includes/header.php';
requireRole('admin');

$database = new Database();
$db = $database->getConnection();

$success = '';
$error = '';

// Handle enrollment override
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] == 'override') {
        $enrollment_id = (int)$_POST['enrollment_id'];
        $new_status = clean($_POST['status']);
        
        $query = "UPDATE enrollments SET status = :status WHERE enrollment_id = :id";
        $stmt = $db->prepare($query);
        if ($stmt->execute([':status' => $new_status, ':id' => $enrollment_id])) {
            $success = 'Enrollment status updated!';
        }
    }
}

// Get all enrollments
$enrollments_query = "SELECT e.*, 
                      u.full_name as student_name,
                      s.subject_code, s.subject_name
                      FROM enrollments e
                      JOIN users u ON e.student_id = u.user_id
                      JOIN subjects s ON e.subject_id = s.subject_id
                      ORDER BY e.enrolled_at DESC";
$enrollments = $db->query($enrollments_query)->fetchAll();
?>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">
            <i class="fas fa-clipboard-list"></i>
            Enrollment Management
        </h2>
    </div>
</div>

<?php if ($success): ?>
<div class="alert alert-success">
    <i class="fas fa-check-circle"></i>
    <?php echo $success; ?>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Subject</th>
                        <th>School Year</th>
                        <th>Semester</th>
                        <th>Grade</th>
                        <th>Status</th>
                        <th>Enrolled Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($enrollments as $enrollment): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($enrollment['student_name']); ?></td>
                        <td>
                            <strong><?php echo htmlspecialchars($enrollment['subject_code']); ?></strong><br>
                            <small><?php echo htmlspecialchars($enrollment['subject_name']); ?></small>
                        </td>
                        <td><?php echo htmlspecialchars($enrollment['school_year']); ?></td>
                        <td><?php echo $enrollment['semester']; ?></td>
                        <td>
                            <?php if ($enrollment['grade']): ?>
                                <strong><?php echo formatGrade($enrollment['grade']); ?></strong>
                            <?php else: ?>
                                <span class="text-muted">N/A</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge badge-<?php 
                                echo $enrollment['status'] == 'enrolled' ? 'info' : 
                                    ($enrollment['status'] == 'completed' ? 'success' : 'danger'); 
                            ?>">
                                <?php echo ucfirst($enrollment['status']); ?>
                            </span>
                        </td>
                        <td><?php echo date('M d, Y', strtotime($enrollment['enrolled_at'])); ?></td>
                        <td>
                            <button class="btn btn-sm btn-warning" 
                                    onclick="openOverrideModal(<?php echo $enrollment['enrollment_id']; ?>, '<?php echo $enrollment['status']; ?>')">
                                <i class="fas fa-edit"></i>
                                Override
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Override Modal -->
<div id="overrideModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Override Enrollment Status</h3>
            <button class="modal-close" onclick="closeModal('overrideModal')">&times;</button>
        </div>
        <form method="POST">
            <div class="modal-body">
                <input type="hidden" name="action" value="override">
                <input type="hidden" name="enrollment_id" id="override_enrollment_id">
                
                <div class="form-group">
                    <label class="form-label required">New Status</label>
                    <select name="status" id="override_status" class="form-control" required>
                        <option value="enrolled">Enrolled</option>
                        <option value="completed">Completed</option>
                        <option value="failed">Failed</option>
                        <option value="dropped">Dropped</option>
                    </select>
                </div>
                
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Warning:</strong> This will override the enrollment status regardless of prerequisites or grades.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('overrideModal')">Cancel</button>
                <button type="submit" class="btn btn-warning">
                    <i class="fas fa-check"></i>
                    Confirm Override
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openOverrideModal(enrollmentId, currentStatus) {
    document.getElementById('override_enrollment_id').value = enrollmentId;
    document.getElementById('override_status').value = currentStatus;
    openModal('overrideModal');
}
</script>

<?php require_once '../includes/footer.php'; ?>