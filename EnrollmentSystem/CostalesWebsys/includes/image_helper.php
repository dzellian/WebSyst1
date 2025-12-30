<?php
function getImagePath($filename, $type = 'profile') {
    if (empty($filename)) {
        return null;
    }
    
    // Get the base path
    $base_path = '';
    $current_path = $_SERVER['PHP_SELF'];
    
    // Determine directory level
    if (strpos($current_path, '/admin/') !== false || 
        strpos($current_path, '/faculty/') !== false || 
        strpos($current_path, '/student/') !== false) {
        $base_path = '../';
    }
    
    $folder = $type === 'profile' ? 'profiles' : 'signatures';
    return $base_path . 'assets/uploads/' . $folder . '/' . $filename;
}

function displayProfileImage($filename, $alt = 'Profile', $class = 'profile-img') {
    $path = getImagePath($filename, 'profile');
    
    if ($path && file_exists($path)) {
        return '<img src="' . htmlspecialchars($path) . '" alt="' . htmlspecialchars($alt) . '" class="' . $class . '">';
    } else {
        return '<div class="profile-placeholder ' . $class . '">
                    <i class="fas fa-user"></i>
                </div>';
    }
}

function displaySignature($filename, $alt = 'Signature', $class = 'signature-img') {
    $path = getImagePath($filename, 'signature');
    
    if ($path && file_exists($path)) {
        return '<img src="' . htmlspecialchars($path) . '" alt="' . htmlspecialchars($alt) . '" class="' . $class . '">';
    } else {
        return '<span class="text-muted">No signature</span>';
    }
}
?>