<?php
$page_title = "Manage Departments";
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../includes/functions.php';

requireRole('admin');

$database = new Database();
$conn = $database->getConnection();

$error = $success = '';

// Add department
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add'])) {
    $name = sanitize($_POST['department_name']);
    $code = sanitize($_POST['department_code']);
    $desc = sanitize($_POST['description']);
    
    $stmt = $conn->prepare("INSERT INTO departments (department_name, department_code, description) VALUES (?, ?, ?)");
    if ($stmt->execute([$name, $code, $desc])) {
        $success = "Department added successfully!";
        logActivity($conn, getUserId(), 'DEPT_ADD', 'departments', $conn->lastInsertId(), "Added department: $name");
    } else {
        $error = "Failed to add department";
    }
}

// Delete
if (isset($_GET['delete'])) {
    $stmt = $conn->prepare("DELETE FROM departments WHERE department_id = ?");
    $stmt->execute([$_GET['delete']]);
    logActivity($conn, getUserId(), 'DEPT_DELETE', 'departments', $_GET['delete'], 'Deleted department');
    header("Location: departments.php");
    exit();
}

$departments = $conn->query("SELECT * FROM departments ORDER BY department_name")->fetchAll();

require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<div class="container mt-4">
    <h2><i class="fas fa-building"></i> Manage Departments</h2>
    <hr>
    
    <?php if ($error): ?><div class="alert alert-danger"><?php echo $error; ?></div><?php endif; ?>
    <?php if ($success): ?><div class="alert alert-success"><?php echo $success; ?></div><?php endif; ?>
    
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5>Add Department</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="mb-3">
                            <label>Department Name *</label>
                            <input type="text" name="department_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Code *</label>
                            <input type="text" name="department_code" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Description</label>
                            <textarea name="description" class="form-control" rows="3"></textarea>
                        </div>
                        <button type="submit" name="add" class="btn btn-primary w-100">
                            <i class="fas fa-plus"></i> Add Department
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5>All Departments</h5>
                </div>
                <div class="card-body">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($departments as $dept): ?>
                                <tr>
                                    <td><?php echo $dept['department_code']; ?></td>
                                    <td><?php echo $dept['department_name']; ?></td>
                                    <td><?php echo $dept['description']; ?></td>
                                    <td>
                                        <a href="?delete=<?php echo $dept['department_id']; ?>" 
                                           class="btn btn-sm btn-danger"
                                           onclick="return confirm('Delete?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>