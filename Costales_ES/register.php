<?php
require_once 'config/db.php';
require_once 'includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = register($_POST['name'], $_POST['email'], $_POST['password'], $_POST['role']);
    
    if ($user_id) {
        // Upload profile picture
        if (!empty($_FILES['profile']['name'])) {
            $pic = uploadFile($_FILES['profile'], 'profiles');
            if ($pic) {
                $stmt = $pdo->prepare("UPDATE users SET profile_pic = ? WHERE id = ?");
                $stmt->execute([$pic, $user_id]);
            }
        }
        
        // Upload signature
        if (!empty($_FILES['signature']['name'])) {
            $sig = uploadFile($_FILES['signature'], 'signatures');
            if ($sig) {
                $stmt = $pdo->prepare("UPDATE users SET signature = ? WHERE id = ?");
                $stmt->execute([$sig, $user_id]);
            }
        }
        
        $_SESSION['user_id'] = $user_id;
        $_SESSION['role'] = $_POST['role'];
        header("Location: index.php");
    } else {
        $error = "Registration failed";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Register - Enrollment System</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <div class="card" style="max-width: 500px; margin: 50px auto;">
            <h2>Register</h2>
            <?php if(isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Full Name:</label>
                    <input type="text" name="name" required>
                </div>
                <div class="form-group">
                    <label>Email:</label>
                    <input type="email" name="email" required>
                </div>
                <div class="form-group">
                    <label>Password:</label>
                    <input type="password" name="password" required>
                </div>
                <div class="form-group">
                    <label>Role:</label>
                    <select name="role" required>
                        <option value="student">Student</option>
                        <option value="faculty">Faculty</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Profile Picture:</label>
                    <input type="file" name="profile" accept="image/*" required>
                </div>
                <div class="form-group">
                    <label>Signature:</label>
                    <input type="file" name="signature" accept="image/*" required>
                </div>
                <button type="submit">Register</button>
            </form>
        </div>
    </div>
</body>
</html>