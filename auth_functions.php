<?php
// User authentication and session management functions

// Start session if not already started
function ensure_session() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
}

// Check if user is logged in
function is_logged_in() {
    ensure_session();
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Check if user is admin
function is_admin() {
    ensure_session();
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
}

// Check if user is test admin (restricted)
function is_test_admin() {
    ensure_session();
    return isset($_SESSION['is_test_admin']) && $_SESSION['is_test_admin'] === true;
}

// Check if user can access user management
function can_manage_users() {
    return is_admin() && !is_test_admin();
}

// Check if user can access vessel management  
function can_manage_vessels() {
    return is_admin() && !is_test_admin();
}

// Check if user can select vessels (test admin can only use Test Vessel)
function can_select_vessel($vessel_id = null) {
    if (is_test_admin()) {
        // Test admin can only access Test Vessel
        if ($vessel_id) {
            $sql = "SELECT VesselName FROM vessels WHERE VesselID = ?";
            global $conn;
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('i', $vessel_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $vessel = $result->fetch_assoc();
            return $vessel && $vessel['VesselName'] === 'Test Vessel';
        }
        return false;
    }
    return is_logged_in();
}

// Get current user info
function get_logged_in_user() {
    ensure_session();
    if (!is_logged_in()) {
        return null;
    }
    
    return [
        'user_id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'],
        'first_name' => $_SESSION['first_name'],
        'last_name' => $_SESSION['last_name'],
        'full_name' => $_SESSION['first_name'] . ' ' . $_SESSION['last_name'],
        'is_admin' => $_SESSION['is_admin'] ?? false
    ];
}

// Login user
function login_user($conn, $username, $password) {
    $stmt = $conn->prepare("SELECT UserID, Username, PasswordHash, FirstName, LastName, Email, IsAdmin FROM users WHERE Username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['PasswordHash'])) {
            $_SESSION['user_id'] = $user['UserID'];
            $_SESSION['username'] = $user['Username'];
            $_SESSION['first_name'] = $user['FirstName'];
            $_SESSION['last_name'] = $user['LastName'];
            $_SESSION['email'] = $user['Email'];
            $_SESSION['is_admin'] = $user['IsAdmin'];
            return true;
        }
    }
    return false;
}

// Logout user
function logout_user() {
    ensure_session();
    session_unset();
    session_destroy();
}

// Require login for data entry pages
function require_login() {
    if (!is_logged_in()) {
        header('Location: login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
        exit;
    }
}

// Require admin access
function require_admin() {
    require_login();
    if (!is_admin()) {
        header('Location: dashboard.php?error=access_denied');
        exit;
    }
}

// Require vessel selection for data entry/viewing pages
function require_vessel_selection() {
    require_login();
    ensure_session();
    
    // Check if vessel is selected
    if (!isset($_SESSION['current_vessel_id']) || empty($_SESSION['current_vessel_id'])) {
        header('Location: select_vessel.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
        exit;
    }
}

// Check if vessel is selected (for conditional display)
function has_vessel_selected() {
    ensure_session();
    return isset($_SESSION['current_vessel_id']) && !empty($_SESSION['current_vessel_id']);
}

// Generate password reset token
function generate_reset_token($conn, $email) {
    $token = bin2hex(random_bytes(32));
    $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
    
    $sql = "UPDATE users SET ResetToken = ?, ResetTokenExpiry = ? WHERE Email = ? AND IsActive = 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sss', $token, $expiry, $email);
    
    if ($stmt->execute() && $stmt->affected_rows > 0) {
        return $token;
    }
    return false;
}

// Verify reset token
function verify_reset_token($conn, $token) {
    $sql = "SELECT UserID, Username, Email FROM users WHERE ResetToken = ? AND ResetTokenExpiry > NOW() AND IsActive = 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_assoc();
}

// Reset password
function reset_password($conn, $token, $new_password) {
    $user = verify_reset_token($conn, $token);
    if (!$user) {
        return false;
    }
    
    $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
    $sql = "UPDATE users SET PasswordHash = ?, ResetToken = NULL, ResetTokenExpiry = NULL WHERE UserID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('si', $password_hash, $user['UserID']);
    
    return $stmt->execute();
}

// Create new user
function create_user($conn, $username, $email, $password, $first_name, $last_name, $is_admin = false) {
    // Check if username or email already exists
    $check_sql = "SELECT UserID FROM users WHERE Username = ? OR Email = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param('ss', $username, $email);
    $check_stmt->execute();
    
    if ($check_stmt->get_result()->num_rows > 0) {
        return false; // User already exists
    }
    
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    $sql = "INSERT INTO users (Username, Email, PasswordHash, FirstName, LastName, IsAdmin) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sssssi', $username, $email, $password_hash, $first_name, $last_name, $is_admin);
    
    return $stmt->execute();
}

// Get all users (admin only)
function get_all_users($conn) {
    $sql = "SELECT UserID, Username, Email, FirstName, LastName, IsAdmin, IsActive, CreatedDate, LastLogin FROM users ORDER BY LastName, FirstName";
    return $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
}

// Update user
function update_user($conn, $user_id, $username, $email, $first_name, $last_name, $is_admin, $is_active) {
    $sql = "UPDATE users SET Username = ?, Email = ?, FirstName = ?, LastName = ?, IsAdmin = ?, IsActive = ? WHERE UserID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sssssii', $username, $email, $first_name, $last_name, $is_admin, $is_active, $user_id);
    
    return $stmt->execute();
}

// Change password
function change_password($conn, $user_id, $new_password) {
    $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
    $sql = "UPDATE users SET PasswordHash = ? WHERE UserID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('si', $password_hash, $user_id);
    
    return $stmt->execute();
}
?>
