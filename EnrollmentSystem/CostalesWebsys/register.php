<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

if (isLoggedIn()) {
    header("Location: index.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = clean($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $email = clean($_POST['email']);
    $full_name = clean($_POST['full_name']);
    $role = clean($_POST['role']);
    
    // Validation
    if ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif (!isset($_FILES['profile_picture']) || $_FILES['profile_picture']['error'] == UPLOAD_ERR_NO_FILE) {
        $error = 'Profile picture is required.';
    } elseif (!isset($_FILES['signature']) || $_FILES['signature']['error'] == UPLOAD_ERR_NO_FILE) {
        $error = 'Signature is required.';
    } else {
        $database = new Database();
        $db = $database->getConnection();
        
        // Check if username exists
        $check_query = "SELECT user_id FROM users WHERE username = :username OR email = :email";
        $check_stmt = $db->prepare($check_query);
        $check_stmt->bindParam(':username', $username);
        $check_stmt->bindParam(':email', $email);
        $check_stmt->execute();
        
        if ($check_stmt->rowCount() > 0) {
            $error = 'Username or email already exists.';
        } else {
            // Upload profile picture
            $profile_upload = uploadFile($_FILES['profile_picture'], 'profile');
            if (!$profile_upload['success']) {
                $error = 'Profile Picture: ' . $profile_upload['message'];
            } else {
                // Upload signature
                $signature_upload = uploadFile($_FILES['signature'], 'signature');
                if (!$signature_upload['success']) {
                    $error = 'Signature: ' . $signature_upload['message'];
                } else {
                    // Insert user
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $query = "INSERT INTO users (username, password, email, full_name, role, profile_picture, signature) 
                             VALUES (:username, :password, :email, :full_name, :role, :profile_picture, :signature)";
                    
                    $stmt = $db->prepare($query);
                    $stmt->bindParam(':username', $username);
                    $stmt->bindParam(':password', $hashed_password);
                    $stmt->bindParam(':email', $email);
                    $stmt->bindParam(':full_name', $full_name);
                    $stmt->bindParam(':role', $role);
                    $stmt->bindParam(':profile_picture', $profile_upload['filename']);
                    $stmt->bindParam(':signature', $signature_upload['filename']);
                    
                    if ($stmt->execute()) {
                        $success = 'Registration successful! You can now login.';
                    } else {
                        $error = 'Registration failed. Please try again.';
                    }
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Enrollment System</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="login-container">
        <div class="login-box" style="max-width: 600px;">
            <div class="login-header">
                <i class="fas fa-user-plus"></i>
                <h1>Create Account</h1>
                <p>Register for enrollment system</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" enctype="multipart/form-data">
                <div class="form-group">
                    <label class="form-label required">Full Name</label>
                    <input type="text" name="full_name" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label required">Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label required">Username</label>
                    <input type="text" name="username" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label required">Role</label>
                    <select name="role" class="form-control" required>
                        <option value="">Select Role</option>
                        <option value="student">Student</option>
                        <option value="faculty">Faculty</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label required">Password</label>
                    <input type="password" name="password" class="form-control" minlength="6" required>
                    <small class="form-text">Minimum 6 characters</small>
                </div>
                
                <div class="form-group">
                    <label class="form-label required">Confirm Password</label>
                    <input type="password" name="confirm_password" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label required">Profile Picture</label>
                    <div class="file-upload" onclick="document.getElementById('profile_picture').click()">
                        <div class="file-upload-icon">
                            <i class="fas fa-cloud-upload-alt"></i>
                        </div>
                        <p>Click to upload profile picture</p>
                        <small class="form-text">JPG, PNG, GIF (Max 2MB)</small>
                    </div>
                    <input type="file" id="profile_picture" name="profile_picture" accept="image/*" required>
                    <div id="profile_preview" class="file-preview"></div>
                </div>
                
                <div class="form-group">
                    <label class="form-label required">Signature</label>
                    <div class="file-upload" onclick="document.getElementById('signature').click()">
                        <div class="file-upload-icon">
                            <i class="fas fa-signature"></i>
                        </div>
                        <p>Click to upload signature</p>
                        <small class="form-text">JPG, PNG, GIF (Max 2MB)</small>
                    </div>
                    <input type="file" id="signature" name="signature" accept="image/*" required>
                    <div id="signature_preview" class="file-preview"></div>
                </div>
                
                <button type="submit" class="btn btn-primary btn-lg" style="width: 100%;">
                    <i class="fas fa-user-plus"></i>
                    Register
                </button>
            </form>
            
            <div class="text-center mt-3">
                <p style="color: var(--gray);">
                    Already have an account? 
                    <a href="login.php" style="color: var(--primary);">Login here</a>
                </p>
            </div>
        </div>
    </div>
    
    <script>
        document.getElementById('profile_picture').addEventListener('change', function(e) {
            previewImage(e, 'profile_preview');
        });
        
        document.getElementById('signature').addEventListener('change', function(e) {
            previewImage(e, 'signature_preview');
        });
        
        function previewImage(e, previewId) {
            const preview = document.getElementById(previewId);
            preview.innerHTML = '';
            
            if (e.target.files && e.target.files[0]) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    const img = document.createElement('img');
                    img.src = event.target.result;
                    preview.appendChild(img);
                };
                reader.readAsDataURL(e.target.files[0]);
            }
        }
    </script>
</body>
</html>