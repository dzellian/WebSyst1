<?php
$page_title = "All Submissions";
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../includes/functions.php';

requireRole('adviser');

$database = new Database();
$conn = $database->getConnection();
$user_id = getUserId();

$stmt = $conn->prepare("
    SELECT t.*, u.first_name, u.last_name, d.department_name, p.program_name
    FROM thesis t 
    JOIN users u ON t.author_id = u.user_id 
    JOIN departments d ON t.department_id = d.department_id
    JOIN programs p ON t.program_id = p.program_id
    WHERE t.adviser_id = ? 
    ORDER BY t.submission_date DESC
");
$stmt->execute([$user_id]);
$submissions = $stmt->fetchAll();

require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<div class="container mt-4">
    <h2><i class="fas fa-list"></i> All Submissions</h2>
    <hr>
    
    <div class="card">
        <div class="card-body">
            <table class="table table-striped" id="thesisTable">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Author</th>
                        <th>Department</th>
                        <th>Year</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($submissions as $s): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($s['title']); ?></td>
                            <td><?php echo $s['first_name'] . ' ' . $s['last_name']; ?></td>
                            <td><?php echo $s['department_name']; ?></td>
                            <td><?php echo $s['publication_year']; ?></td>
                            <td>
                                <?php
                                $badges = ['pending'=>'warning', 'under_review'=>'info', 'approved'=>'success', 'rejected'=>'danger'];
                                ?>
                                <span class="badge bg-<?php echo $badges[$s['status']]; ?>">
                                    <?php echo ucfirst($s['status']); ?>
                                </span>
                            </td>
                            <td>
                                <a href="review-thesis.php?id=<?php echo $s['thesis_id']; ?>" class="btn btn-sm btn-primary">
                                    <i class="fas fa-eye"></i> Review
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#thesisTable').DataTable();
});
</script>

<?php require_once '../includes/footer.php'; ?>