<?php
require_once __DIR__ . '/../config/db.php';

// Register user
function register_user($name, $email, $password, $role, $company_name = null) {
    global $conn;
    $hash = password_hash($password, PASSWORD_BCRYPT);
    $stmt = mysqli_prepare($conn, "INSERT INTO users (name, email, password, role, company_name) VALUES (?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, 'sssss', $name, $email, $hash, $role, $company_name);
    $result = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $result;
}

// Login user
function login_user($email, $password) {
    global $conn;
    $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE email = ? AND status = 'active' LIMIT 1");
    mysqli_stmt_bind_param($stmt, 's', $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_name'] = $user['name'];
        return true;
    }
    return false;
}

// Logout user
function logout_user() {
    session_unset();
    session_destroy();
}

// Get current user
function current_user() {
    global $conn;
    if (!isset($_SESSION['user_id'])) return null;
    $id = $_SESSION['user_id'];
    $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE id = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    return $user;
}

// Check if logged in
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// Check user role
function is_admin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}
function is_provider() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'provider';
}
function is_client() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'client';
}

// Profile Image Helper Functions
function get_profile_image_html($user, $size = 'medium', $show_upload = false) {
    $name = $user['name'] ?? 'User';
    $profile_image = $user['profile_image'] ?? null;
    $user_id = $user['id'] ?? 0;
    
    $size_class = 'profile-image-' . $size;
    $placeholder_class = 'profile-image-placeholder-' . $size;
    
    $html = '<div class="profile-image-container">';
    
    if ($profile_image && file_exists('../assets/uploads/profile_images/' . $profile_image)) {
        $html .= '<img src="../assets/uploads/profile_images/' . htmlspecialchars($profile_image) . '" 
                       alt="' . htmlspecialchars($name) . '" 
                       class="profile-image ' . $size_class . '" 
                       title="' . htmlspecialchars($name) . '">';
    } else {
        $initials = get_user_initials($name);
        $html .= '<div class="profile-image-placeholder ' . $placeholder_class . '" title="' . htmlspecialchars($name) . '">' . $initials . '</div>';
    }
    
    if ($show_upload) {
        $html .= '<label for="profile_image_' . $user_id . '" class="profile-image-upload" title="Upload Profile Image">
                    <i class="bi bi-camera"></i>
                  </label>
                  <input type="file" id="profile_image_' . $user_id . '" 
                         class="profile-image-input" 
                         accept="image/*" 
                         data-user-id="' . $user_id . '">';
    }
    
    $html .= '</div>';
    
    return $html;
}

function get_user_initials($name) {
    $words = explode(' ', trim($name));
    $initials = '';
    
    if (count($words) >= 2) {
        $initials = strtoupper(substr($words[0], 0, 1) . substr($words[count($words) - 1], 0, 1));
    } else {
        $initials = strtoupper(substr($name, 0, 2));
    }
    
    return $initials;
}

function upload_profile_image($user_id, $file) {
    $upload_dir = '../assets/uploads/profile_images/';
    $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
    $max_size = 5 * 1024 * 1024; // 5MB
    
    // Validate file
    if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        return ['success' => false, 'message' => 'No file uploaded'];
    }
    
    if (!in_array($file['type'], $allowed_types)) {
        return ['success' => false, 'message' => 'Invalid file type. Only JPG, PNG, and GIF are allowed'];
    }
    
    if ($file['size'] > $max_size) {
        return ['success' => false, 'message' => 'File too large. Maximum size is 5MB'];
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'profile_' . $user_id . '_' . time() . '.' . $extension;
    $filepath = $upload_dir . $filename;
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        // Update database
        global $conn;
        $stmt = mysqli_prepare($conn, "UPDATE users SET profile_image = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, 'si', $filename, $user_id);
        $result = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        if ($result) {
            return ['success' => true, 'filename' => $filename, 'message' => 'Profile image updated successfully'];
        } else {
            unlink($filepath); // Remove file if database update failed
            return ['success' => false, 'message' => 'Failed to update database'];
        }
    } else {
        return ['success' => false, 'message' => 'Failed to save file'];
    }
}

function delete_profile_image($user_id) {
    global $conn;
    
    // Get current profile image
    $stmt = mysqli_prepare($conn, "SELECT profile_image FROM users WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    
    if ($user && $user['profile_image']) {
        $filepath = '../assets/uploads/profile_images/' . $user['profile_image'];
        if (file_exists($filepath)) {
            unlink($filepath);
        }
    }
    
    // Update database
    $stmt = mysqli_prepare($conn, "UPDATE users SET profile_image = NULL WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $user_id);
    $result = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    
    return $result;
} 