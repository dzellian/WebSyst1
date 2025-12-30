<?php
$page_title = 'My Profile';
require_once '../config/database.php';
require_once '../includes/header.php';
requireRole('student');

$database = new Database();
$db = $database->getConnection();
$student_id = $_SESSION['user_id'];

$success = '';
$error = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = clean($_POST['full_name']);
    $email = clean($_POST['email']);
    
    $update_query = "UPDATE users SET full_name = :full_name, email = :email";
    $params = [':full_name' => $full_name, ':email' => $email, ':user_id' => $student_id];
    
    // Handle profile picture upload
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] != UPLOAD_ERR_NO_FILE) {
        $upload = uploadFile($_FILES['profile_picture'], 'profile');
        if ($upload['success']) {
            $update_query .= ", profile_picture = :profile_picture";
            $params[':profile_picture'] = $upload['filename'];
            $_SESSION['profile_picture'] = $upload['filename'];
        } else {
            $error = $upload['message'];
        }
    }
    
    // Handle signature upload
    if (isset($_FILES['signature']) && $_FILES['signature']['error'] != UPLOAD_ERR_NO_FILE) {
        $upload = uploadFile($_FILES['signature'], 'signature');
        if ($upload['success']) {
            $update_query .= ", signature = :signature";
            $params[':signature'] = $upload['filename'];
            $_SESSION['signature'] = $upload['filename'];
        } else {
            $error = $upload['message'];
        }
    }
    
    $update_query .= " WHERE user_id = :user_id";
    
    if (empty($error)) {
        $stmt = $db->prepare($update_query);
        if ($stmt->execute($params)) {
            $_SESSION['full_name'] = $full_name;
            $success = 'Profile updated successfully!';
        } else {
            $error = 'Failed to update profile.';
        }
    }
}

// Get user data
$query = "SELECT * FROM users WHERE user_id = :id";
$stmt = $db->prepare($query);
$stmt->execute([':id' => $student_id]);
$user = $stmt->fetch();
?>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">
            <i class="fas fa-user"></i>
            My Profile
        </h2>
    </div>
</div>

<?php if ($success): ?>
<div class="alert alert-success">
    <i class="fas fa-check-circle"></i>
    <?php echo $success; ?>
</div>
<?php endif; ?>

<?php if ($error): ?>
<div class="alert alert-danger">
    <i class="fas fa-exclamation-circle"></i>
    <?php echo $error; ?>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <form method="POST" enctype="multipart/form-data">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
                <div>
                    <h3 class="mb-2">
                        <i class="fas fa-user-circle"></i>
                        Personal Information
                    </h3>
                    
                    <div class="form-group">
                        <label class="form-label required">Full Name</label>
                        <input type="text" name="full_name" class="form-control" 
                               value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label required">Email</label>
                        <input type="email" name="email" class="form-control" 
                               value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Username</label>
                        <input type="text" class="form-control" 
                               value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
                        <small class="form-text">Username cannot be changed</small>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Role</label>
                        <input type="text" class="form-control" 
                               value="<?php echo ucfirst($user['role']); ?>" disabled>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Account Status</label>
                        <div>
                            <span class="badge badge-<?php echo $user['status'] == 'active' ? 'success' : 'danger'; ?>">
                                <?php echo ucfirst($user['status']); ?>
                            </span>
                        </div>
                    </div>
                </div>
                
                <div>
                    <h3 class="mb-2">
                        <i class="fas fa-image"></i>
                        Profile Images
                    </h3>
                    
                    <div class="form-group">
    <label class="form-label">Profile Picture</label>
    <?php 
    if ($user['profile_picture']): 
        $img_path = "../assets/uploads/profiles/" . $user['profile_picture'];
        if (file_exists($img_path)):
    ?>
        <div class="mb-2">
            <img src="<?php echo $img_path; ?>" alt="Profile" 
                 style="width: 150px; height: 150px; object-fit: cover; border-radius: 8px; border: 2px solid var(--border);">
        </div>
    <?php 
        endif;
    endif; 
    ?>
    <input type="file" name="profile_picture" class="form-control" accept="image/*" onchange="previewImage(this, 'profilePreview')">
    <small class="form-text">Leave empty to keep current picture</small>
    <div id="profilePreview" class="file-preview mt-2"></div>
</div>

<div class="form-group">
    <label class="form-label">Signature</label>
    <?php 
    if ($user['signature']): 
        $sig_path = "../assets/uploads/signatures/" . $user['signature'];
        if (file_exists($sig_path)):
    ?>
        <div class="mb-2">
            <img src="<?php echo $sig_path; ?>" alt="Signature" 
                 style="width: 200px; height: 80px; object-fit: contain; border: 1px solid var(--border); border-radius: 4px; padding: 8px; background: white;">
        </div>
    <?php 
        endif;
    endif; 
    ?>
    <input type="file" name="signature" class="form-control" accept="image/*" onchange="previewImage(this, 'signaturePreview')">
    <small class="form-text">Leave empty to keep current signature</small>
    <div id="signaturePreview" class="file-preview mt-2"></div>
</div>
                    
                    <div class="form-group">
                        <label class="form-label">Member Since</label>
                        <input type="text" class="form-control" 
                               value="<?php echo date('F d, Y', strtotime($user['created_at'])); ?>" disabled>
                    </div>
                </div>
            </div>
            
            <div class="mt-3">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-save"></i>
                    Update Profile
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function previewImage(input, previewId) {
    const preview = document.getElementById(previewId);
    preview.innerHTML = '';
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const img = document.createElement('img');
            img.src = e.target.result;
            img.style.maxWidth = '200px';
            img.style.maxHeight = '200px';
            img.style.border = '2px solid var(--border)';
            img.style.borderRadius = '8px';
            preview.appendChild(img);
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

<?php require_once '../includes/footer.php'; ?>