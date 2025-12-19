<?php
$page_title = "Upload Thesis";
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../includes/functions.php';

requireRole('student');

$database = new Database();
$conn = $database->getConnection();
$user_id = getUserId();

$error = '';
$success = '';

// Get departments, programs, and advisers
$departments = $conn->query("SELECT * FROM departments ORDER BY department_name")->fetchAll();
$programs = $conn->query("SELECT * FROM programs ORDER BY program_name")->fetchAll();
$advisers = $conn->query("SELECT user_id, first_name, last_name FROM users WHERE role = 'adviser' AND status = 'active' ORDER BY first_name")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title']);
    $abstract = sanitize($_POST['abstract']);
    $keywords = sanitize($_POST['keywords']);
    $co_authors = sanitize($_POST['co_authors']);
    $adviser_id = sanitize($_POST['adviser_id']);
    $department_id = sanitize($_POST['department_id']);
    $program_id = sanitize($_POST['program_id']);
    $publication_year = sanitize($_POST['publication_year']);
    
    if (empty($title) || empty($abstract) || empty($adviser_id) || !isset($_FILES['thesis_file'])) {
        $error = "Please fill all required fields and upload a file";
    } else {
        // Upload file
        $upload_result = uploadFile($_FILES['thesis_file'], THESIS_PATH, ALLOWED_THESIS_TYPES);
        
        if ($upload_result['success']) {
            $file_name = $upload_result['file_name'];
            $file_path = $upload_result['file_path'];
            $file_size = $_FILES['thesis_file']['size'];
            $file_type = pathinfo($file_name, PATHINFO_EXTENSION);
            
            $stmt = $conn->prepare("
                INSERT INTO thesis (title, abstract, keywords, author_id, co_authors, adviser_id, department_id, program_id, publication_year, file_path, file_size, file_type, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')
            ");
            
            if ($stmt->execute([$title, $abstract, $keywords, $user_id, $co_authors, $adviser_id, $department_id, $program_id, $publication_year, $file_path, $file_size, $file_type])) {
                $thesis_id = $conn->lastInsertId();
                logActivity($conn, $user_id, 'THESIS_UPLOAD', 'thesis', $thesis_id, "Uploaded thesis: $title");
                
                // Notify adviser
                sendNotification($adviser_id, 'New Thesis Submission', "A new thesis '$title' has been submitted for your review.", $conn);
                
                $success = "Thesis uploaded successfully!";
            } else {
                deleteFile($file_path);
                $error = "Failed to save thesis information";
            }
        } else {
            $error = $upload_result['message'];
        }
    }
}

require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<div class="container mt-4 mb-5">
    <div class="row">
        <div class="col-md-10 offset-md-1">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4><i class="fas fa-upload"></i> Upload Thesis</h4>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            <?php echo $success; ?>
                            <a href="my-submissions.php">View my submissions</a>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="title" class="form-label">Thesis Title *</label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="abstract" class="form-label">Abstract *</label>
                            <textarea class="form-control" id="abstract" name="abstract" rows="6" required></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="keywords" class="form-label">Keywords (comma-separated)</label>
                            <input type="text" class="form-control" id="keywords" name="keywords" placeholder="e.g., machine learning, artificial intelligence, data science">
                        </div>
                        
                        <div class="mb-3">
                            <label for="co_authors" class="form-label">Co-Authors (if any)</label>
                            <input type="text" class="form-control" id="co_authors" name="co_authors" placeholder="e.g., John Doe, Jane Smith">
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="adviser_id" class="form-label">Thesis Adviser *</label>
                                <select class="form-select" id="adviser_id" name="adviser_id" required>
                                    <option value="">Select Adviser</option>
                                    <?php foreach ($advisers as $adviser): ?>
                                        <option value="<?php echo $adviser['user_id']; ?>">
                                            <?php echo $adviser['first_name'] . ' ' . $adviser['last_name']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="publication_year" class="form-label">Publication Year *</label>
                                <input type="number" class="form-control" id="publication_year" name="publication_year" min="1900" max="<?php echo date('Y'); ?>" value="<?php echo date('Y'); ?>" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="department_id" class="form-label">Department *</label>
                                <select class="form-select" id="department_id" name="department_id" required>
                                    <option value="">Select Department</option>
                                    <?php foreach ($departments as $dept): ?>
                                        <option value="<?php echo $dept['department_id']; ?>">
                                            <?php echo $dept['department_name']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="program_id" class="form-label">Program *</label>
                                <select class="form-select" id="program_id" name="program_id" required>
                                    <option value="">Select Program</option>
                                    <?php foreach ($programs as $prog): ?>
                                        <option value="<?php echo $prog['program_id']; ?>">
                                            <?php echo $prog['program_name']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="thesis_file" class="form-label">Thesis File (PDF, DOC, DOCX) *</label>
                            <input type="file" class="form-control" id="thesis_file" name="thesis_file" accept=".pdf,.doc,.docx" required>
                            <small class="form-text text-muted">Maximum file size: <?php echo formatFileSize(MAX_FILE_SIZE); ?></small>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-upload"></i> Upload Thesis
                            </button>
                            <a href="index.php" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>