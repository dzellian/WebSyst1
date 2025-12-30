<?php
session_start();

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Check user role
function hasRole($role) {
    return isset($_SESSION['role']) && $_SESSION['role'] === $role;
}

// Redirect if not logged in
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: ../login.php");
        exit();
    }
}

// Redirect if not authorized
function requireRole($role) {
    requireLogin();
    if (!hasRole($role)) {
        header("Location: ../index.php");
        exit();
    }
}

// Upload file handler
function uploadFile($file, $type = 'profile') {
    $target_dir = "../assets/uploads/" . ($type === 'profile' ? 'profiles/' : 'signatures/');
    
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $imageFileType = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    $new_filename = uniqid() . '_' . time() . '.' . $imageFileType;
    $target_file = $target_dir . $new_filename;
    
    // Validate image
    $check = getimagesize($file["tmp_name"]);
    if ($check === false) {
        return ['success' => false, 'message' => 'File is not an image.'];
    }
    
    // Check file size (max 2MB)
    if ($file["size"] > 2000000) {
        return ['success' => false, 'message' => 'File is too large. Max 2MB.'];
    }
    
    // Allow certain formats
    if (!in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
        return ['success' => false, 'message' => 'Only JPG, JPEG, PNG & GIF files are allowed.'];
    }
    
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return ['success' => true, 'filename' => $new_filename];
    } else {
        return ['success' => false, 'message' => 'Error uploading file.'];
    }
}

// Check prerequisites
function checkPrerequisites($db, $student_id, $subject_id) {
    $query = "SELECT p.required_subject_id, s.subject_code, s.subject_name
              FROM prerequisites p
              JOIN subjects s ON p.required_subject_id = s.subject_id
              WHERE p.subject_id = :subject_id";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':subject_id', $subject_id);
    $stmt->execute();
    
    $prerequisites = $stmt->fetchAll();
    $missing = [];
    
    foreach ($prerequisites as $prereq) {
        $check_query = "SELECT enrollment_id FROM enrollments 
                       WHERE student_id = :student_id 
                       AND subject_id = :required_subject_id 
                       AND status = 'completed'
                       AND grade >= 3.0";
        
        $check_stmt = $db->prepare($check_query);
        $check_stmt->bindParam(':student_id', $student_id);
        $check_stmt->bindParam(':required_subject_id', $prereq['required_subject_id']);
        $check_stmt->execute();
        
        if ($check_stmt->rowCount() == 0) {
            $missing[] = $prereq['subject_code'] . ' - ' . $prereq['subject_name'];
        }
    }
    
    return $missing;
}

// Sanitize input
function clean($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Format grade
function formatGrade($grade) {
    if ($grade === null) return 'N/A';
    return number_format($grade, 2);
}

// Get current school year
function getCurrentSchoolYear() {
    $year = date('Y');
    $month = date('n');
    if ($month >= 6) {
        return $year . '-' . ($year + 1);
    } else {
        return ($year - 1) . '-' . $year;
    }
}
?>