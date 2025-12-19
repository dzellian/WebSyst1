<?php
$page_title = "My Submissions";
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../includes/functions.php';

requireRole('student');

$database = new Database();
$conn = $database->getConnection();
$user_id = getUserId();

// Get all submissions
$stmt = $conn->prepare("
    SELECT t.*, d.department_name, p.program_name, 
           u.first_name as adviser_first, u.last_name as adviser_last
    FROM thesis t
    JOIN departments d ON t.department_id = d.department_id
    JOIN programs p ON t.program_id = p.program_id
    JOIN users u ON t.adviser_id = u.user_id
    WHERE t.author_id = ?
    ORDER BY t.submission_date DESC
");
$stmt->execute([$user_id]);
$submissions = $stmt->fetchAll();

require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-list"></i> My Submissions</h2>
        <a href="upload-thesis.php" class="btn btn-success">
            <i class="fas fa-plus"></i> Upload New Thesis
        </a>
    </div>
    
    <div class="card">
        <div class="card-body">
            <?php if (count($submissions) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover" id="submissionsTable">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Adviser</th>
                                <th>Department</th>
                                <th>Program</th>
                                <th>Year</th>
                                <th>Status</th>
                                <th>Submission Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($submissions as $thesis): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($thesis['title']); ?></td>
                                    <td><?php echo $thesis['adviser_first'] . ' ' . $thesis['adviser_last']; ?></td>
                                    <td><?php echo $thesis['department_name']; ?></td>
                                    <td><?php echo $thesis['program_name']; ?></td>
                                    <td><?php echo $thesis['publication_year']; ?></td>
                                    <td>
                                        <?php
                                        $status_class = [
                                            'pending' => 'warning',
                                            'under_review' => 'info',
                                            'approved' => 'success',
                                            'rejected' => 'danger'
                                        ];
                                        ?>
                                        <span class="badge bg-<?php echo $status_class[$thesis['status']]; ?>">
                                            <?php echo ucfirst(str_replace('_', ' ', $thesis['status'])); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($thesis['submission_date'])); ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-primary view-btn" data-id="<?php echo $thesis['thesis_id']; ?>">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <?php if ($thesis['status'] === 'pending'): ?>
                                            <button class="btn btn-sm btn-danger delete-btn" data-id="<?php echo $thesis['thesis_id']; ?>">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No submissions yet.</p>
                    <a href="upload-thesis.php" class="btn btn-primary">Upload Your First Thesis</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- View Modal -->
<div class="modal fade" id="viewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Thesis Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modalContent">
                <!-- Content loaded via AJAX -->
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#submissionsTable').DataTable({
        order: [[6, 'desc']]
    });
    
    $('.view-btn').click(function() {
        const thesisId = $(this).data('id');
        $('#modalContent').html('<div class="text-center"><i class="fas fa-spinner fa-spin fa-3x"></i></div>');
        $('#viewModal').modal('show');
        
        $.get('view-submission.php?id=' + thesisId, function(data) {
            $('#modalContent').html(data);
        });
    });
    
    $('.delete-btn').click(function() {
        if (confirm('Are you sure you want to delete this submission?')) {
            const thesisId = $(this).data('id');
            window.location.href = 'delete-submission.php?id=' + thesisId;
        }
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>