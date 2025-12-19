<?php
$page_title = "Adviser Dashboard";
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../includes/functions.php';

requireRole('adviser');

$database = new Database();
$conn = $database->getConnection();
$user_id = getUserId();

// Get statistics
$stmt = $conn->prepare("SELECT COUNT(*) FROM thesis WHERE adviser_id = ?");
$stmt->execute([$user_id]);
$total = $stmt->fetchColumn();

$stmt = $conn->prepare("SELECT COUNT(*) FROM thesis WHERE adviser_id = ? AND status = 'pending'");
$stmt->execute([$user_id]);
$pending = $stmt->fetchColumn();

$stmt = $conn->prepare("SELECT COUNT(*) FROM thesis WHERE adviser_id = ? AND status = 'approved'");
$stmt->execute([$user_id]);
$approved = $stmt->fetchColumn();

// Recent submissions
$stmt = $conn->prepare("
    SELECT t.*, u.first_name, u.last_name 
    FROM thesis t 
    JOIN users u ON t.author_id = u.user_id 
    WHERE t.adviser_id = ? 
    ORDER BY t.submission_date DESC LIMIT 5
");
$stmt->execute([$user_id]);
$recent = $stmt->fetchAll();

require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<div class="container mt-4">
    <h2><i class="fas fa-dashboard"></i> Adviser Dashboard</h2>
    <hr>
    
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5>Total Supervised</h5>
                    <h2><?php echo $total; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h5>Pending Review</h5>
                    <h2><?php echo $pending; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5>Approved</h5>
                    <h2><?php echo $approved; ?></h2>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card mb-4">
        <div class="card-header">
            <h5>Quick Actions</h5>
        </div>
        <div class="card-body">
            <a href="submissions.php" class="btn btn-primary me-2">
                <i class="fas fa-list"></i> View All Submissions
            </a>
            <a href="profile.php" class="btn btn-info">
                <i class="fas fa-user"></i> Update Profile
            </a>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h5>Recent Submissions</h5>
        </div>
        <div class="card-body">
            <?php if (count($recent) > 0): ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Author</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent as $r): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($r['title']); ?></td>
                                <td><?php echo $r['first_name'] . ' ' . $r['last_name']; ?></td>
                                <td><?php echo date('M d, Y', strtotime($r['submission_date'])); ?></td>
                                <td><span class="badge bg-warning"><?php echo $r['status']; ?></span></td>
                                <td>
                                    <a href="review-thesis.php?id=<?php echo $r['thesis_id']; ?>" class="btn btn-sm btn-primary">Review</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="text-muted">No submissions yet.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>