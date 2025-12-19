<?php
$page_title = "Thesis Library";
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../includes/functions.php';

$database = new Database();
$conn = $database->getConnection();

// Get approved thesis
$thesis_list = $conn->query("
    SELECT t.*, u.first_name, u.last_name, d.department_name, p.program_name
    FROM thesis t
    JOIN users u ON t.author_id = u.user_id
    JOIN departments d ON t.department_id = d.department_id
    JOIN programs p ON t.program_id = p.program_id
    WHERE t.status = 'approved'
    ORDER BY t.publication_year DESC, t.title
")->fetchAll();

require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<div class="content-wrapper">
    <div class="container mt-4 mb-5">
        <h2><i class="fas fa-library"></i> Thesis Library</h2>
        <p class="text-muted">Browse approved thesis documents</p>
        <hr>
        
        <?php if (count($thesis_list) > 0): ?>
            <div class="row">
                <?php foreach ($thesis_list as $thesis): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($thesis['title']); ?></h5>
                                <p class="card-text">
                                    <small>
                                        <strong><i class="fas fa-user"></i> Author:</strong> <?php echo $thesis['first_name'] . ' ' . $thesis['last_name']; ?><br>
                                        <strong><i class="fas fa-calendar"></i> Year:</strong> <?php echo $thesis['publication_year']; ?><br>
                                        <strong><i class="fas fa-building"></i> Department:</strong> <?php echo $thesis['department_name']; ?><br>
                                        <strong><i class="fas fa-graduation-cap"></i> Program:</strong> <?php echo $thesis['program_name']; ?>
                                    </small>
                                </p>
                                <p class="card-text text-muted">
                                    <?php echo substr($thesis['abstract'], 0, 150); ?>...
                                </p>
                            </div>
                            <div class="card-footer bg-white">
                                <div class="d-flex justify-content-between align-items-center">
                                    <a href="view-thesis.php?id=<?php echo $thesis['thesis_id']; ?>" class="btn btn-primary btn-sm">
                                        <i class="fas fa-eye"></i> View Details
                                    </a>
                                    <div>
                                        <span class="badge bg-info">
                                            <i class="fas fa-eye"></i> <?php echo $thesis['views']; ?>
                                        </span>
                                        <span class="badge bg-success">
                                            <i class="fas fa-download"></i> <?php echo $thesis['downloads']; ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-inbox fa-5x text-muted mb-3"></i>
                <h4 class="text-muted">No Thesis Available Yet</h4>
                <p class="text-muted">Check back later for approved thesis documents.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>