<?php
require_once '../includes/header.php';

if ($_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

$admin_id = $_SESSION['user_id'];

// Get admin info
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$admin_id]);
$admin = $stmt->fetch();

// Update profile picture
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    if (!empty($_FILES['profile']['name'])) {
        $pic = uploadFile($_FILES['profile'], 'profiles');
        if ($pic) {
            $stmt = $pdo->prepare("UPDATE users SET profile_pic = ? WHERE id = ?");
            $stmt->execute([$pic, $admin_id]);
            $admin['profile_pic'] = $pic;
            $success = "Profile picture updated.";
        } else {
            $error = "Failed to upload profile picture.";
        }
    }
}

// Update signature
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_signature'])) {
    if (!empty($_FILES['signature']['name'])) {
        $sig = uploadFile($_FILES['signature'], 'signatures');
        if ($sig) {
            $stmt = $pdo->prepare("UPDATE users SET signature = ? WHERE id = ?");
            $stmt->execute([$sig, $admin_id]);
            $admin['signature'] = $sig;
            $success = "Signature updated.";
        } else {
            $error = "Failed to upload signature.";
        }
    }
}
?>

<h2>Admin Profile</h2>

<?php if (!empty($success)): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>
<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<div class="grid">
    <!-- Info -->
    <div class="card">
        <h3>Account Information</h3>
        <p><strong>Name:</strong> <?php echo htmlspecialchars($admin['name']); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($admin['email']); ?></p>
        <p><strong>Role:</strong>
            <span style="background:#667eea;color:#fff;padding:3px 8px;border-radius:3px;">
                <?php echo ucfirst($admin['role']); ?>
            </span>
        </p>
        <p><strong>Member Since:</strong>
            <?php echo date('M d, Y', strtotime($admin['created_at'])); ?>
        </p>
    </div>

    <!-- Profile picture -->
    <div class="card">
        <h3>Profile Picture</h3>
        <?php if (!empty($admin['profile_pic'])): ?>
            <img src="../uploads/profiles/<?php echo htmlspecialchars($admin['profile_pic']); ?>"
                 alt="Profile" class="profile-pic"
                 style="width:200px;height:200px;object-fit:cover;margin-bottom:15px;">
        <?php else: ?>
            <p style="color:#999;">No profile picture uploaded.</p>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>Upload New Profile Picture</label>
                <input type="file" name="profile" accept="image/*" required>
            </div>
            <button type="submit" name="update_profile">Save</button>
        </form>
    </div>

    <!-- Signature -->
    <div class="card">
        <h3>Signature</h3>
        <?php if (!empty($admin['signature'])): ?>
            <img src="../uploads/signatures/<?php echo htmlspecialchars($admin['signature']); ?>"
                 alt="Signature" class="signature-img"
                 style="max-width:250px;margin-bottom:15px;">
        <?php else: ?>
            <p style="color:#999;">No signature uploaded.</p>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>Upload New Signature</label>
                <input type="file" name="signature" accept="image/*" required>
            </div>
            <button type="submit" name="update_signature">Save</button>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>