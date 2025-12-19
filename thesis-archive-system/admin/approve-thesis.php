<?php
$page_title = "Approve Thesis";
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../includes/functions.php';

requireRole('admin');

$database = new Database();
$conn = $database->getConnection();

$success = $error = '';

// Handle approval/rejection
if (isset($_POST['update_status'])) {
    $thesis_id = $_POST['thesis_id'];
    $new_status = $_POST['status'];
    $admin_comments = sanitize($_POST['admin_comments']);
    
    $stmt = $conn->prepare("UPDATE thesis SET status = ?, approval_date = NOW() WHERE thesis_id = ?");
    if ($stmt->execute([$new_status, $thesis_id])) {
        // Log the action
        $stmt = $conn->prepare("INSERT INTO approvals (thesis_id, reviewer_id, status, comments) VALUES (?, ?, ?, ?)");
        $stmt->execute([$thesis_id, getUserId(), $new_status, $admin_comments]);
        
        logActivity($conn, getUserId(), 'ADMIN_THESIS_' . strtoupper($new_status), 'thesis', $thesis_id, "Admin $new_status thesis");
        
        $success = "Thesis status updated successfully!";
    } else {
        $error = "Failed to update thesis status";
    }
}

// Get all thesis submissions
$thesis_list = $conn->query("
    SELECT t.*, 
           u.first_name as author_first, u.last_name as author_last, u.email as author_email,
           adv.first_name as adviser_first, adv.last_name as adviser_last,
           d.department_name, p.program_name
    FROM thesis t 
    JOIN users u ON t.author_id = u.user_id 
    JOIN users adv ON t.adviser_id = adv.user_id
    JOIN departments d ON t.department_id = d.department_id
    JOIN programs p ON t.program_id = p.program_id
    ORDER BY 
        CASE 
            WHEN t.status = 'pending' THEN 1
            WHEN t.status = 'under_review' THEN 2
            WHEN t.status = 'approved' THEN 3
            WHEN t.status = 'rejected' THEN 4
        END,
        t.submission_date DESC
")->fetchAll();

require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2><i class="fas fa-check-circle"></i> Approve Thesis</h2>
        <a href="index.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>
    <hr>
    
    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle"></i> <?php echo $success; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <!-- Statistics -->
    <div class="row mb-4">
        <?php
        $stats = [
            'pending' => 0,
            'under_review' => 0,
            'approved' => 0,
            'rejected' => 0
        ];
        foreach ($thesis_list as $t) {
            $stats[$t['status']]++;
        }
        ?>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body text-center">
                    <h6>Pending</h6>
                    <h2><?php echo $stats['pending']; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <h6>Under Review</h6>
                    <h2><?php echo $stats['under_review']; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h6>Approved</h6>
                    <h2><?php echo $stats['approved']; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body text-center">
                    <h6>Rejected</h6>
                    <h2><?php echo $stats['rejected']; ?></h2>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-body">
            <?php if (count($thesis_list) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover" id="thesisTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Title</th>
                                <th>Author</th>
                                <th>Adviser</th>
                                <th>Department</th>
                                <th>Year</th>
                                <th>Status</th>
                                <th>Submitted</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($thesis_list as $t): ?>
                                <tr>
                                    <td><?php echo $t['thesis_id']; ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars(substr($t['title'], 0, 50)); ?></strong>
                                        <?php if (strlen($t['title']) > 50) echo '...'; ?>
                                    </td>
                                    <td><?php echo $t['author_first'] . ' ' . $t['author_last']; ?></td>
                                    <td><?php echo $t['adviser_first'] . ' ' . $t['adviser_last']; ?></td>
                                    <td><?php echo $t['department_name']; ?></td>
                                    <td><?php echo $t['publication_year']; ?></td>
                                    <td>
                                        <?php
                                        $badges = [
                                            'pending' => 'warning',
                                            'under_review' => 'info',
                                            'approved' => 'success',
                                            'rejected' => 'danger'
                                        ];
                                        ?>
                                        <span class="badge bg-<?php echo $badges[$t['status']]; ?>">
                                            <?php echo ucfirst(str_replace('_', ' ', $t['status'])); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($t['submission_date'])); ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-primary view-btn" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#viewModal<?php echo $t['thesis_id']; ?>">
                                            <i class="fas fa-eye"></i> View
                                        </button>
                                    </td>
                                </tr>
                                
                                <!-- Modal for each thesis -->
                                <div class="modal fade" id="viewModal<?php echo $t['thesis_id']; ?>" tabindex="-1">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header bg-primary text-white">
                                                <h5 class="modal-title">Thesis Details</h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <h5><?php echo htmlspecialchars($t['title']); ?></h5>
                                                <hr>
                                                
                                                <div class="row mb-3">
                                                    <div class="col-md-6">
                                                        <p><strong>Author:</strong> <?php echo $t['author_first'] . ' ' . $t['author_last']; ?></p>
                                                        <p><strong>Email:</strong> <?php echo $t['author_email']; ?></p>
                                                        <p><strong>Adviser:</strong> <?php echo $t['adviser_first'] . ' ' . $t['adviser_last']; ?></p>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <p><strong>Department:</strong> <?php echo $t['department_name']; ?></p>
                                                        <p><strong>Program:</strong> <?php echo $t['program_name']; ?></p>
                                                        <p><strong>Year:</strong> <?php echo $t['publication_year']; ?></p>
                                                    </div>
                                                </div>
                                                
                                                <?php if ($t['keywords']): ?>
                                                    <p><strong>Keywords:</strong><br>
                                                        <?php
                                                        $keywords = explode(',', $t['keywords']);
                                                        foreach ($keywords as $kw) {
                                                            echo '<span class="badge bg-secondary me-1">' . trim($kw) . '</span>';
                                                        }
                                                        ?>
                                                    </p>
                                                <?php endif; ?>
                                                
                                                <p><strong>Abstract:</strong></p>
                                                <p class="text-justify"><?php echo nl2br(htmlspecialchars($t['abstract'])); ?></p>
                                                
                                                <hr>
                                                
                                                <p><strong>Current Status:</strong> 
                                                    <span class="badge bg-<?php echo $badges[$t['status']]; ?>">
                                                        <?php echo ucfirst(str_replace('_', ' ', $t['status'])); ?>
                                                    </span>
                                                </p>
                                                
                                                <p><strong>File:</strong> 
                                                    <a href="<?php echo BASE_URL . str_replace(ROOT_PATH . '/', '', $t['file_path']); ?>" 
                                                       class="btn btn-sm btn-info" 
                                                       target="_blank">
                                                        <i class="fas fa-download"></i> Download Thesis
                                                    </a>
                                                </p>
                                                
                                                <!-- Update Status Form -->
                                                <hr>
                                                <form method="POST" action="">
                                                    <input type="hidden" name="thesis_id" value="<?php echo $t['thesis_id']; ?>">
                                                    
                                                    <div class="mb-3">
                                                        <label class="form-label"><strong>Update Status:</strong></label>
                                                        <select name="status" class="form-select" required>
                                                            <option value="">-- Select Status --</option>
                                                            <option value="pending" <?php echo $t['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                            <option value="under_review" <?php echo $t['status'] == 'under_review' ? 'selected' : ''; ?>>Under Review</option>
                                                            <option value="approved" <?php echo $t['status'] == 'approved' ? 'selected' : ''; ?>>Approved</option>
                                                            <option value="rejected" <?php echo $t['status'] == 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                                                        </select>
                                                    </div>
                                                    
                                                    <div class="mb-3">
                                                        <label class="form-label"><strong>Admin Comments:</strong></label>
                                                        <textarea name="admin_comments" class="form-control" rows="3" placeholder="Optional comments..."></textarea>
                                                    </div>
                                                    
                                                    <button type="submit" name="update_status" class="btn btn-primary w-100">
                                                        <i class="fas fa-check"></i> Update Status
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-5x text-muted mb-3"></i>
                    <h4 class="text-muted">No Thesis Submissions Yet</h4>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#thesisTable').DataTable({
        order: [[7, 'desc']], // Sort by submission date
        pageLength: 25
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>