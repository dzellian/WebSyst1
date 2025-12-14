<?php
require_once '../includes/header.php';

if ($_SESSION['role'] != 'student') {
    header("Location: ../index.php");
}

$student_id = $_SESSION['user_id'];

// Get student info
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$student_id]);
$student = $stmt->fetch();

// Handle profile picture update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['profile'])) {
    $pic = uploadFile($_FILES['profile'], 'profiles');
    if ($pic) {
        $stmt = $pdo->prepare("UPDATE users SET profile_pic = ? WHERE id = ?");
        $stmt->execute([$pic, $student_id]);
        $success = "Profile picture updated";
        $student['profile_pic'] = $pic;
    } else {
        $error = "Failed to upload profile picture";
    }
}

// Handle signature update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['signature'])) {
    $sig = uploadFile($_FILES['signature'], 'signatures');
    if ($sig) {
        $stmt = $pdo->prepare("UPDATE users SET signature = ? WHERE id = ?");
        $stmt->execute([$sig, $student_id]);
        $success = "Signature updated";
        $student['signature'] = $sig;
    } else {
        $error = "Failed to upload signature";
    }
}

// Get completed subjects
$stmt = $pdo->prepare("SELECT s.* FROM completed_subjects cs JOIN subjects s ON cs.subject_id = s.id WHERE cs.student_id = ?");
$stmt->execute([$student_id]);
$completed = $stmt->fetchAll();

// Get enrolled subjects with grades
$stmt = $pdo->prepare("
    SELECT s.*, e.status, e.grade 
    FROM enrollments e 
    JOIN subjects s ON e.subject_id = s.id 
    WHERE e.student_id = ? 
    ORDER BY e.enrolled_at DESC
");
$stmt->execute([$student_id]);
$enrollments = $stmt->fetchAll();
?>

<h2>My Profile</h2>

<?php if(isset($success)): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<?php if(isset($error)): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<div class="grid">
    <!-- Profile Section -->
    <div class="card">
        <h3>Profile Information</h3>
        <p><strong>Name:</strong> <?php echo $student['name']; ?></p>
        <p><strong>Email:</strong> <?php echo $student['email']; ?></p>
        <p><strong>Role:</strong> <span style="background: #667eea; color: white; padding: 3px 8px; border-radius: 3px;">
            <?php echo ucfirst($student['role']); ?>
        </span></p>
        <p><strong>Member Since:</strong> <?php echo date('M d, Y', strtotime($student['created_at'])); ?></p>
    </div>

    <!-- Profile Picture Section -->
    <div class="card">
        <h3>Profile Picture</h3>
        <?php if($student['profile_pic']): ?>
            <img src="../uploads/profiles/<?php echo $student['profile_pic']; ?>" class="profile-pic" alt="Profile" style="width: 200px; height: 200px; object-fit: cover; margin-bottom: 15px;">
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

    <!-- Signature Section -->
    <div class="card">
        <h3>Signature</h3>
        <?php if($student['signature']): ?>
            <img src="../uploads/signatures/<?php echo $student['signature']; ?>" class="signature-img" alt="Signature" style="width: 250px; margin-bottom: 15px;">
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

<!-- Enrollments Section -->
<div class="card" style="margin-top: 30px;">
    <h3>My Enrollments & Grades</h3>
    <table>
        <tr>
            <th>Subject</th>
            <th>Code</th>
            <th>Status</th>
            <th>Grade</th>
            <th>Enrolled Date</th>
        </tr>
        <?php if (count($enrollments) > 0): ?>
            <?php foreach($enrollments as $e): ?>
                <tr>
                    <td><?php echo $e['name']; ?></td>
                    <td><?php echo $e['code']; ?></td>
                    <td>
                        <?php 
                        $status_color = [
                            'pending' => '#f39c12',
                            'approved' => '#27ae60',
                            'rejected' => '#e74c3c'
                        ];
                        $color = $status_color[$e['status']] ?? '#95a5a6';
                        ?>
                        <span style="background: <?php echo $color; ?>; color: white; padding: 3px 8px; border-radius: 3px;">
                            <?php echo ucfirst($e['status']); ?>
                        </span>
                    </td>
                    <td>
                        <?php echo $e['grade'] ? $e['grade'] : '-'; ?>
                    </td>
                    <td><?php echo date('M d, Y', strtotime($e['enrolled_at'])); ?></td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="5" style="text-align: center; color: #999;">No enrollments yet</td>
            </tr>
        <?php endif; ?>
    </table>
</div>

<!-- Completed Subjects Section -->
<div class="card">
    <h3>Completed Subjects</h3>
    <?php if (count($completed) > 0): ?>
        <table>
            <tr>
                <th>Subject</th>
                <th>Code</th>
                <th>Description</th>
            </tr>
            <?php foreach($completed as $c): ?>
                <tr>
                    <td><?php echo $c['name']; ?></td>
                    <td><?php echo $c['code']; ?></td>
                    <td><?php echo $c['description']; ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p style="color: #999;">No completed subjects yet</p>
    <?php endif; ?>
</div>

<!-- Quick Links -->
<div class="card">
    <h3>Quick Actions</h3>
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
        <a href="enroll.php" style="background: #667eea; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">
            Enroll in Subject
        </a>
        <a href="../logout.php" style="background: #e74c3c; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">
            Logout
        </a>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>