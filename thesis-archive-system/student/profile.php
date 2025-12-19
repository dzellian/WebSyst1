<?php
$page_title = "Profile";
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

// Get user data
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $first_name = sanitize($_POST['first_name']);
        $last_name = sanitize($_POST['last_name']);
        $email = sanitize($_POST['email']);
        
        $stmt = $conn->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ? WHERE user_id = ?");
        if ($stmt->execute([$first_name, $last_name, $email, $user_id])) {
            $_SESSION['first_name'] = $first_name;
            $_SESSION['last_name'] = $last_name;
            logActivity($conn, $user_id, 'PROFILE_UPDATE', 'users', $user_id, 'Updated profile information');
            $success = "Profile updated successfully!";
            // Refresh user data
            $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();
        }
    }
    
    if (isset($_POST['update_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        if (password_verify($current_password, $user['password'])) {
            if ($new_password === $confirm_password) {
                if (strlen($new_password) >= 6) {
                    $hashed = password_hash($new_password, PASSWORD_DEFAULT);
                    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
                    if ($stmt->execute([$hashed, $user_id])) {
                        logActivity($conn, $user_id, 'PASSWORD_CHANGE', 'users', $user_id, 'Changed password');
                        $success = "Password updated successfully!";
                    }
                } else {
                    $error = "Password must be at least 6 characters";
                }
            } else {
                $error = "Passwords do not match";
            }
        } else {
            $error = "Current password is incorrect";
        }
    }
    
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $upload_result = uploadFile($_FILES['profile_picture'], PROFILE_PATH, ALLOWED_IMAGE_TYPES);
        if ($upload_result['success']) {
            // Delete old picture
            if ($user['profile_picture'] && file_exists(PROFILE_PATH . $user['profile_picture'])) {
                unlink(PROFILE_PATH . $user['profile_picture']);
            }
            
            $stmt = $conn->prepare("UPDATE users SET profile_picture = ? WHERE user_id = ?");
            if ($stmt->execute([$upload_result['file_name'], $user_id])) {
                logActivity($conn, $user_id, 'PROFILE_PICTURE_UPDATE', 'users', $user_id, 'Updated profile picture');
                $success = "Profile picture updated!";
                // Refresh user data
                $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
                $stmt->execute([$user_id]);
                $user = $stmt->fetch();
            }
        } else {
            $error = $upload_result['message'];
        }
    }
    
    if (isset($_FILES['signature']) && $_FILES['signature']['error'] === UPLOAD_ERR_OK) {
        $upload_result = uploadFile($_FILES['signature'], SIGNATURE_PATH, ALLOWED_IMAGE_TYPES);
        if ($upload_result['success']) {
            // Delete old signature
            if ($user['signature'] && file_exists(SIGNATURE_PATH . $user['signature'])) {
                unlink(SIGNATURE_PATH . $user['signature']);
            }
            
            $stmt = $conn->prepare("UPDATE users SET signature = ? WHERE user_id = ?");
            if ($stmt->execute([$upload_result['file_name'], $user_id])) {
                logActivity($conn, $user_id, 'SIGNATURE_UPDATE', 'users', $user_id, 'Updated signature');
                $success = "Signature updated!";
                // Refresh user data
                $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
                $stmt->execute([$user_id]);
                $user = $stmt->fetch();
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
    <h2><i class="fas fa-user-circle"></i> My Profile</h2>
    <hr>
    
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <div class="row">
        <!-- Profile Information -->
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5><i class="fas fa-user"></i> Profile Information</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="first_name" class="form-label">First Name</label>
                                <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo $user['first_name']; ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="last_name" class="form-label">Last Name</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo $user['last_name']; ?>" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo $user['email']; ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" class="form-control" value="<?php echo $user['username']; ?>" disabled>
                        </div>
                        
                        <button type="submit" name="update_profile" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Profile
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Change Password -->
            <div class="card mb-4">
                <div class="card-header bg-warning text-white">
                    <h5><i class="fas fa-lock"></i> Change Password</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Current Password</label>
                            <input type="password" class="form-control" id="current_password" name="current_password" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="new_password" class="form-label">New Password</label>
                            <input type="password" class="form-control" id="new_password" name="new_password" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        </div>
                        
                        <button type="submit" name="update_password" class="btn btn-warning">
                            <i class="fas fa-key"></i> Change Password
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Profile Picture & Signature -->
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5><i class="fas fa-image"></i> Profile Picture</h5>
                </div>
                <div class="card-body text-center">
                    <?php if ($user['profile_picture']): ?>
                        <img src="<?php echo BASE_URL; ?>uploads/profiles/<?php echo $user['profile_picture']; ?>" class="img-fluid rounded-circle mb-3" style="max-width: 200px;">
                    <?php else: ?>
                        <i class="fas fa-user-circle fa-10x text-muted mb-3"></i>
                    <?php endif; ?>
                    
                    <form method="POST" action="" enctype="multipart/form-data">
                        <div class="mb-3">
                            <input type="file" class="form-control" name="profile_picture" accept="image/*">
                        </div>
                        <button type="submit" class="btn btn-info">
                            <i class="fas fa-upload"></i> Upload
                        </button>
                    </form>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5><i class="fas fa-signature"></i> Signature</h5>
                </div>
                <div class="card-body text-center">
                    <?php if ($user['signature']): ?>
                        <img src="<?php echo BASE_URL; ?>uploads/signatures/<?php echo $user['signature']; ?>" class="img-fluid mb-3" style="max-width: 200px;">
                    <?php else: ?>
                        <p class="text-muted">No signature uploaded</p>
                    <?php endif; ?>
                    
                    <form method="POST" action="" enctype="multipart/form-data">
                        <div class="mb-3">
                            <input type="file" class="form-control" name="signature" accept="image/*">
                        </div>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-upload"></i> Upload
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>