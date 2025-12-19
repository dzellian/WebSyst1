<?php
$page_title = "Review Thesis";
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../includes/functions.php';

requireRole('adviser');

$database = new Database();
$conn = $database->getConnection();
$user_id = getUserId();
$thesis_id = $_GET['id'] ?? 0;

$error = $success = '';

// Get thesis details
$stmt = $conn->prepare("
    SELECT t.*, u.first_name, u.last_name, u.email, d.department_name, p.program_name
    FROM thesis t
    JOIN users u ON t.author_id = u.user_id
    JOIN departments d ON t.department_id = d.department_id
    JOIN programs p ON t.program_id = p.program_id
    WHERE t.thesis_id = ? AND t.adviser_id = ?
");
$stmt->execute([$thesis_id, $user_id]);
$thesis = $stmt->fetch();

if (!$thesis) {
    header("Location: submissions.php");
    exit();
}

// Handle review submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $status = $_POST['status'];
    $comments = sanitize($_POST['comments']);
    
    // Update thesis status
    $stmt = $conn->prepare("UPDATE thesis SET status = ?, approval_date = NOW() WHERE thesis_id = ?");
    $stmt->execute([$status, $thesis_id]);
    
    // Insert approval record
    $stmt = $conn->prepare("INSERT INTO approvals (thesis_id, reviewer_id, status, comments) VALUES (?, ?, ?, ?)");
    $stmt->execute([$thesis_id, $user_id, $status, $comments]);
    
    // Log review
    $stmt = $conn->prepare("INSERT INTO review_logs (thesis_id, reviewer_id, action, comments) VALUES (?, ?, ?, ?)");
    $stmt->execute([$thesis_id, $user_id, 'REVIEW_' . strtoupper($status), $comments]);
    
    logActivity($conn, $user_id, 'THESIS_REVIEW', 'thesis', $thesis_id, "Reviewed thesis: $status");
    
    // Notify student
    sendNotification($thesis['author_id'], 'Thesis Review Update', "Your thesis has been $status.", $conn);
    
    $success = "Review submitted successfully!";
}

require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<div class="container mt-4 mb-5">
    <h2><i class="fas fa-file-alt"></i> Review Thesis</h2>
    <hr>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5>Thesis Information</h5>
                </div>
                <div class="card-body">
                    <h4><?php echo htmlspecialchars($thesis['title']); ?></h4>
                    <p><strong>Author:</strong> <?php echo $thesis['first_name'] . ' ' . $thesis['last_name']; ?></p>
                    <p><strong>Department:</strong> <?php echo $thesis['department_name']; ?></p>
                    <p><strong>Program:</strong> <?php echo $thesis['program_name']; ?></p>
                    <p><strong>Year:</strong> <?php echo $thesis['publication_year']; ?></p>
                    <p><strong>Keywords:</strong> <?php echo $thesis['keywords']; ?></p>
                    <hr>
                    <h5>Abstract</h5>
                    <p><?php echo nl2br(htmlspecialchars($thesis['abstract'])); ?></p>
                    <hr>
                    <a href="<?php echo BASE_URL . str_replace(ROOT_PATH . '/', '', $thesis['file_path']); ?>" class="btn btn-info" target="_blank">
                        <i class="fas fa-download"></i> Download Thesis
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5>Submit Review</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Decision *</label>
                            <select name="status" class="form-select" required>
                                <option value="">Choose...</option>
                                <option value="approved">Approve</option>
                                <option value="rejected">Reject</option>
                                <option value="under_review">Request Revision</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Comments *</label>
                            <textarea name="comments" class="form-control" rows="5" required></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-success w-100">
                            <i class="fas fa-check"></i> Submit Review
                        </button>
                    </form>
                </div>
            </div>
            
            <div class="card mt-3">
                <div class="card-header">
                    <h6>Current Status</h6>
                </div>
                <div class="card-body text-center">
                    <span class="badge bg-warning fs-5"><?php echo ucfirst($thesis['status']); ?></span>
                    <p class="text-muted mt-2">Submitted: <?php echo date('M d, Y', strtotime($thesis['submission_date'])); ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>