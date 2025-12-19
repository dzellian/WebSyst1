<?php
$page_title = "Student Dashboard";
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../includes/functions.php';

requireRole('student');

$database = new Database();
$conn = $database->getConnection();
$user_id = getUserId();

// Get statistics
$stats = [];

// Total submissions
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM thesis WHERE author_id = ?");
$stmt->execute([$user_id]);
$stats['total'] = $stmt->fetchColumn();

// Pending submissions
$stmt = $conn->prepare("SELECT COUNT(*) as pending FROM thesis WHERE author_id = ? AND status = 'pending'");
$stmt->execute([$user_id]);
$stats['pending'] = $stmt->fetchColumn();

// Approved submissions
$stmt = $conn->prepare("SELECT COUNT(*) as approved FROM thesis WHERE author_id = ? AND status = 'approved'");
$stmt->execute([$user_id]);
$stats['approved'] = $stmt->fetchColumn();

// Under review
$stmt = $conn->prepare("SELECT COUNT(*) as review FROM thesis WHERE author_id = ? AND status = 'under_review'");
$stmt->execute([$user_id]);
$stats['review'] = $stmt->fetchColumn();

// Rejected
$stmt = $conn->prepare("SELECT COUNT(*) as rejected FROM thesis WHERE author_id = ? AND status = 'rejected'");
$stmt->execute([$user_id]);
$stats['rejected'] = $stmt->fetchColumn();

// Recent submissions
$stmt = $conn->prepare("
    SELECT t.*, d.department_name, p.program_name, u.first_name, u.last_name
    FROM thesis t
    JOIN departments d ON t.department_id = d.department_id
    JOIN programs p ON t.program_id = p.program_id
    JOIN users u ON t.adviser_id = u.user_id
    WHERE t.author_id = ?
    ORDER BY t.submission_date DESC
    LIMIT 5
");
$stmt->execute([$user_id]);
$recent_submissions = $stmt->fetchAll();

require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<div class="container mt-4">
    <h2><i class="fas fa-dashboard"></i> Student Dashboard</h2>
    <p class="text-muted">Welcome back, <?php echo $_SESSION['first_name'] . ' ' . $_SESSION['last_name']; ?>!</p>
    <hr>
    
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card bg-primary text-white h-100">
                <div class="card-body text-center">
                    <i class="fas fa-file-alt fa-3x mb-2"></i>
                    <h5 class="card-title">Total Submissions</h5>
                    <h2 class="display-4"><?php echo $stats['total']; ?></h2>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card bg-warning text-white h-100">
                <div class="card-body text-center">
                    <i class="fas fa-clock fa-3x mb-2"></i>
                    <h5 class="card-title">Pending</h5>
                    <h2 class="display-4"><?php echo $stats['pending']; ?></h2>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card bg-info text-white h-100">
                <div class="card-body text-center">
                    <i class="fas fa-eye fa-3x mb-2"></i>
                    <h5 class="card-title">Under Review</h5>
                    <h2 class="display-4"><?php echo $stats['review']; ?></h2>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card bg-success text-white h-100">
                <div class="card-body text-center">
                    <i class="fas fa-check-circle fa-3x mb-2"></i>
                    <h5 class="card-title">Approved</h5>
                    <h2 class="display-4"><?php echo $stats['approved']; ?></h2>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5><i class="fas fa-bolt"></i> Quick Actions</h5>
                </div>
                <div class="card-body">
                    <a href="upload-thesis.php" class="btn btn-success me-2 mb-2">
                        <i class="fas fa-upload"></i> Upload New Thesis
                    </a>
                    <a href="my-submissions.php" class="btn btn-primary me-2 mb-2">
                        <i class="fas fa-list"></i> View My Submissions
                    </a>
                    <a href="profile.php" class="btn btn-info me-2 mb-2">
                        <i class="fas fa-user"></i> Update Profile
                    </a>
                    <a href="../public/library.php" class="btn btn-secondary mb-2">
                        <i class="fas fa-library"></i> Browse Library
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recent Submissions -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h5><i class="fas fa-clock"></i> Recent Submissions</h5>
                </div>
                <div class="card-body">
                    <?php if (count($recent_submissions) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Adviser</th>
                                        <th>Department</th>
                                        <th>Submission Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_submissions as $thesis): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars(substr($thesis['title'], 0, 50)); ?></strong>
                                                <?php if (strlen($thesis['title']) > 50) echo '...'; ?>
                                            </td>
                                            <td><?php echo $thesis['first_name'] . ' ' . $thesis['last_name']; ?></td>
                                            <td><?php echo $thesis['department_name']; ?></td>
                                            <td><?php echo date('M d, Y', strtotime($thesis['submission_date'])); ?></td>
                                            <td>
                                                <?php
                                                $status_class = [
                                                    'pending' => 'warning',
                                                    'under_review' => 'info',
                                                    'approved' => 'success',
                                                    'rejected' => 'danger'
                                                ];
                                                $status_icon = [
                                                    'pending' => 'clock',
                                                    'under_review' => 'eye',
                                                    'approved' => 'check-circle',
                                                    'rejected' => 'times-circle'
                                                ];
                                                ?>
                                                <span class="badge bg-<?php echo $status_class[$thesis['status']]; ?>">
                                                    <i class="fas fa-<?php echo $status_icon[$thesis['status']]; ?>"></i>
                                                    <?php echo ucfirst(str_replace('_', ' ', $thesis['status'])); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="my-submissions.php?id=<?php echo $thesis['thesis_id']; ?>" 
                                                   class="btn btn-sm btn-primary" 
                                                   title="View Details">
                                                    <i class="fas fa-eye"></i> View
                                                </a>
                                                <?php if ($thesis['status'] === 'approved'): ?>
                                                    <a href="../public/view-thesis.php?id=<?php echo $thesis['thesis_id']; ?>" 
                                                       class="btn btn-sm btn-success" 
                                                       title="View in Library">
                                                        <i class="fas fa-external-link-alt"></i>
                                                    </a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="text-end mt-3">
                            <a href="my-submissions.php" class="btn btn-outline-primary">
                                View All Submissions <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-inbox fa-5x text-muted mb-3"></i>
                            <h4 class="text-muted">No submissions yet</h4>
                            <p class="text-muted">Start by uploading your first thesis</p>
                            <a href="upload-thesis.php" class="btn btn-primary btn-lg mt-3">
                                <i class="fas fa-upload"></i> Upload Your First Thesis
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Additional Info Section -->
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5><i class="fas fa-info-circle"></i> Submission Guidelines</h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li class="mb-2"><i class="fas fa-check text-success"></i> File format: PDF, DOC, or DOCX</li>
                        <li class="mb-2"><i class="fas fa-check text-success"></i> Maximum file size: 50MB</li>
                        <li class="mb-2"><i class="fas fa-check text-success"></i> Include complete abstract and keywords</li>
                        <li class="mb-2"><i class="fas fa-check text-success"></i> Ensure all co-authors are listed</li>
                        <li class="mb-2"><i class="fas fa-check text-success"></i> Select correct department and program</li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5><i class="fas fa-question-circle"></i> Need Help?</h5>
                </div>
                <div class="card-body">
                    <p><strong>Submission Process:</strong></p>
                    <ol>
                        <li>Prepare your thesis document</li>
                        <li>Click "Upload New Thesis"</li>
                        <li>Fill in all required information</li>
                        <li>Upload your file</li>
                        <li>Wait for adviser review</li>
                    </ol>
                    <p class="mt-3">
                        <i class="fas fa-envelope"></i> Contact support: 
                        <a href="mailto:support@thesis.com">support@thesis.com</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>