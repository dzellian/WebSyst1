<?php
$page_title = "Manage Programs";
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../includes/functions.php';

requireRole('admin');

$database = new Database();
$conn = $database->getConnection();

$error = $success = '';

// Get all departments for dropdown
$departments = $conn->query("SELECT * FROM departments ORDER BY department_name")->fetchAll();

// Add program
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add'])) {
    $name = sanitize($_POST['program_name']);
    $code = sanitize($_POST['program_code']);
    $department_id = sanitize($_POST['department_id']);
    $degree_level = sanitize($_POST['degree_level']);
    $desc = sanitize($_POST['description']);
    
    $stmt = $conn->prepare("INSERT INTO programs (program_name, program_code, department_id, degree_level, description) VALUES (?, ?, ?, ?, ?)");
    if ($stmt->execute([$name, $code, $department_id, $degree_level, $desc])) {
        $success = "Program added successfully!";
        logActivity($conn, getUserId(), 'PROGRAM_ADD', 'programs', $conn->lastInsertId(), "Added program: $name");
    } else {
        $error = "Failed to add program";
    }
}

// Delete
if (isset($_GET['delete'])) {
    $stmt = $conn->prepare("DELETE FROM programs WHERE program_id = ?");
    $stmt->execute([$_GET['delete']]);
    logActivity($conn, getUserId(), 'PROGRAM_DELETE', 'programs', $_GET['delete'], 'Deleted program');
    header("Location: programs.php");
    exit();
}

// Get all programs with department names (FIXED QUERY)
$programs = $conn->query("
    SELECT p.*, d.department_name 
    FROM programs p 
    LEFT JOIN departments d ON p.department_id = d.department_id 
    ORDER BY p.program_name
")->fetchAll();

require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<div class="container mt-4 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2><i class="fas fa-graduation-cap"></i> Manage Programs</h2>
        <a href="index.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>
    <hr>
    
    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?php echo $error; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?php echo $success; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5><i class="fas fa-plus"></i> Add New Program</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Program Name *</label>
                            <input type="text" name="program_name" class="form-control" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Program Code *</label>
                            <input type="text" name="program_code" class="form-control" placeholder="e.g., BSCS" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Department *</label>
                            <select name="department_id" class="form-select" required>
                                <option value="">Select Department</option>
                                <?php foreach ($departments as $dept): ?>
                                    <option value="<?php echo $dept['department_id']; ?>">
                                        <?php echo $dept['department_name']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Degree Level *</label>
                            <select name="degree_level" class="form-select" required>
                                <option value="">Select Level</option>
                                <option value="undergraduate">Undergraduate</option>
                                <option value="masters">Masters</option>
                                <option value="doctorate">Doctorate</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3"></textarea>
                        </div>
                        
                        <button type="submit" name="add" class="btn btn-primary w-100">
                            <i class="fas fa-plus"></i> Add Program
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-list"></i> All Programs</h5>
                </div>
                <div class="card-body">
                    <?php if (count($programs) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="programsTable">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Code</th>
                                        <th>Program Name</th>
                                        <th>Department</th>
                                        <th>Degree Level</th>
                                        <th>Description</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($programs as $prog): ?>
                                        <tr>
                                            <td><strong><?php echo $prog['program_code']; ?></strong></td>
                                            <td><?php echo $prog['program_name']; ?></td>
                                            <td><?php echo $prog['department_name'] ?? 'N/A'; ?></td>
                                            <td>
                                                <span class="badge bg-info">
                                                    <?php echo ucfirst($prog['degree_level']); ?>
                                                </span>
                                            </td>
<td><?php echo $prog['description'] ? (strlen($prog['description']) > 50 ? substr($prog['description'], 0, 50) . '...' : $prog['description']) : 'N/A'; ?></td>                                            <td>
                                                <a href="?delete=<?php echo $prog['program_id']; ?>" 
                                                   class="btn btn-sm btn-danger"
                                                   onclick="return confirm('Delete this program? This will affect all associated users and thesis!')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-inbox fa-5x text-muted mb-3"></i>
                            <p class="text-muted">No programs found. Add your first program.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#programsTable').DataTable({
        order: [[1, 'asc']]
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>