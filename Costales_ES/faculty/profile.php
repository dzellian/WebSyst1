<?php
require_once '../includes/header.php';

if ($_SESSION['role'] != 'faculty') {
    header("Location: ../index.php");
}

$faculty_id = $_SESSION['user_id'];

// Get faculty info
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$faculty_id]);
$faculty = $stmt->fetch();

// Handle profile picture update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['profile'])) {
    $pic = uploadFile($_FILES['profile'], 'profiles');
    if ($pic) {
        $stmt = $pdo->prepare("UPDATE users SET profile_pic = ? WHERE id = ?");
        $stmt->execute([$pic, $faculty_id]);
        $success = "Profile picture updated";
        $faculty['profile_pic'] = $pic;
    } else {
        $error = "Failed to upload profile picture";
    }
}

// Handle signature update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['signature'])) {
    $sig = uploadFile($_FILES['signature'], 'signatures');
    if ($sig) {
        $stmt = $pdo->prepare("UPDATE users SET signature = ? WHERE id = ?");
        $stmt->execute([$sig, $faculty_id]);
        $success = "Signature updated";
        $faculty['signature'] = $sig;
    } else {
        $error = "Failed to upload signature";
    }
}

// Get statistics
$stmt = $pdo->query("
    SELECT COUNT(DISTINCT e.student_id) as total_students
    FROM enrollments e
    WHERE e.status = 'approved'
");
$stats = $stmt->fetch();
?>

<h2>Faculty Profile</h2>

<?php if(isset($success)): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<?php if(isset($error)): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<div class="grid">
    <!-- Profile Information -->
    <div class="card">
        <h3>Basic Information</h3>
        <p><strong>Name:</strong> <?php echo $faculty['name']; ?></p>
        <p><strong>Email:</strong> <?php echo $faculty['email']; ?></p>
        <p><strong>Role:</strong> <span style="background: #667eea; color: white; padding: 3px 8px; border-radius: 3px;">
            <?php echo ucfirst($faculty['role']); ?>
        </span></p>
        <p><strong>Member Since:</strong> <?php echo date('M d, Y', strtotime($faculty['created_at'])); ?></p>
        <p><strong>Total Students:</strong> <span style="background: #27ae60; color: white; padding: 3px 8px; border-radius: 3px;">
            <?php echo $stats['total_students']; ?>
        </span></p>
    </div>

    <!-- Profile Picture -->
    <div class="card">
        <h3>Profile Picture</h3>
        <?php if($faculty['profile_pic']): ?>
            <img src="../uploads/profiles/<?php echo $faculty['profile_pic']; ?>" 
                 class="profile-pic" alt="Profile" 
                 style="width: 200px; height: 200px; object-fit: cover; margin-bottom: 15px; border-radius: 5px;">
        <?php else: ?>
            <p style="color: #999;">No profile picture uploaded</p>
        <?php endif; ?>
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>Update Profile Picture:</label>
                <input type="file" name="profile" accept="image/*" required>
            </div>
            <button type="submit">Upload</button>
        </form>
    </div>

    <!-- Signature -->
    <div class="card">
        <h3>Signature</h3>
        <?php if($faculty['signature']): ?>
            <img src="../uploads/signatures/<?php echo $faculty['signature']; ?>" 
                 style="max-width: 250px; margin-bottom: 15px;">
        <?php else: ?>
            <p style="color: #999;">No signature uploaded</p>
        <?php endif; ?>
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>Update Signature:</label>
                <input type="file" name="signature" accept="image/*" required>
            </div>
            <button type="submit">Upload</button>
        </form>
    </div>
</div>

<!-- Quick Links -->
<div class="card">
    <h3>Quick Actions</h3>
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
        <a href="dashboard.php" style="background: #667eea; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">
            Back to Dashboard
        </a>
        <a href="class_list.php" style="background: #27ae60; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">
            View Classes
        </a>
        <a href="../logout.php" style="background: #e74c3c; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">
            Logout
        </a>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>