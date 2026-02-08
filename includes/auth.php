<?php
/**
 * Authentication Functions
 * Dealka Marketplace
 */

require_once __DIR__ . '/../config/db.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Generate CSRF token
 */
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(CSRF_TOKEN_LENGTH / 2));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Check if user is logged in
 */
function is_logged_in() {
    return isset($_SESSION['user_id']) && isset($_SESSION['username']);
}

/**
 * Check if user is admin
 */
function is_admin() {
    return is_logged_in() && isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Require login
 */
function require_login() {
    if (!is_logged_in()) {
        header("Location: " . BASE_URL . "pages/auth/login.php");
        exit();
    }
}

/**
 * Require admin
 */
function require_admin() {
    if (!is_admin()) {
        header("Location: " . BASE_URL . "pages/auth/login.php");
        exit();
    }
}

/**
 * Get current user ID
 */
function get_user_id() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current user data
 */
function get_logged_user() {
    global $pdo;
    
    if (!is_logged_in()) {
        return null;
    }

    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND status = 'active'");
        $stmt->execute([get_user_id()]);
        return $stmt->fetch();
    } catch (Exception $e) {
        error_log("Get current user error: " . $e->getMessage());
        return null;
    }
}

/**
 * Register user
 */
function register_user($username, $email, $password, $phone = '') {
    global $pdo;

    // Validate input
    if (empty($username) || empty($email) || empty($password)) {
        return ['success' => false, 'message' => 'All fields are required'];
    }

    if (strlen($password) < 6) {
        return ['success' => false, 'message' => 'Password must be at least 6 characters'];
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'message' => 'Invalid email format'];
    }

    if (strlen($username) < 3 || strlen($username) > 50) {
        return ['success' => false, 'message' => 'Username must be 3-50 characters'];
    }

    try {
        // Check if user exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        
        if ($stmt->fetch()) {
            return ['success' => false, 'message' => 'Username or email already exists'];
        }

        // Hash password
        $password_hash = password_hash($password, PASSWORD_BCRYPT);

        // Insert user
        $stmt = $pdo->prepare("
            INSERT INTO users (username, email, phone, password_hash, role, status)
            VALUES (?, ?, ?, ?, 'user', 'active')
        ");

        $stmt->execute([$username, $email, $phone, $password_hash]);
        $user_id = $pdo->lastInsertId();

        // Log action
        log_action($user_id, 'register', 'User registered: ' . $username);

        return ['success' => true, 'message' => 'Registration successful', 'user_id' => $user_id];
    } catch (Exception $e) {
        error_log("Registration error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Registration failed'];
    }
}

/**
 * Login user
 */
function login_user($username, $password) {
    global $pdo;

    if (empty($username) || empty($password)) {
        return ['success' => false, 'message' => 'Username and password are required'];
    }

    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE (username = ? OR email = ?) AND status = 'active'");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            // Log failed attempt
            log_action(null, 'login_failed', 'Failed login attempt: ' . $username);
            return ['success' => false, 'message' => 'Invalid username or password'];
        }

        // Set session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['balance'] = $user['balance'];

        // Log successful login
        log_action($user['id'], 'login', 'User logged in');

        return ['success' => true, 'message' => 'Login successful', 'user' => $user];
    } catch (Exception $e) {
        error_log("Login error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Login failed'];
    }
}

/**
 * Logout user
 */
function logout_user() {
    if (is_logged_in()) {
        log_action(get_user_id(), 'logout', 'User logged out');
    }

    session_destroy();
    header("Location: " . BASE_URL . "index.php");
    exit();
}

/**
 * Update user balance
 */
function update_balance($user_id, $amount, $action = 'update', $description = '') {
    global $pdo;

    try {
        $pdo->beginTransaction();

        // Get current balance
        $stmt = $pdo->prepare("SELECT balance FROM users WHERE id = ? FOR UPDATE");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();

        if (!$user) {
            $pdo->rollBack();
            return ['success' => false, 'message' => 'User not found'];
        }

        $old_balance = $user['balance'];
        $new_balance = $old_balance + $amount;

        if ($new_balance < 0) {
            $pdo->rollBack();
            return ['success' => false, 'message' => 'Insufficient balance'];
        }

        // Update balance
        $stmt = $pdo->prepare("UPDATE users SET balance = ? WHERE id = ?");
        $stmt->execute([$new_balance, $user_id]);

        // Log action
        log_action($user_id, $action, $description, 'users', $user_id, $old_balance, $new_balance);

        $pdo->commit();

        // Update session if current user
        if ($user_id == get_user_id()) {
            $_SESSION['balance'] = $new_balance;
        }

        return ['success' => true, 'message' => 'Balance updated', 'old_balance' => $old_balance, 'new_balance' => $new_balance];
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Update balance error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Balance update failed'];
    }
}

/**
 * Sanitize input
 */
function sanitize($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Validate file upload
 */
function validate_file_upload($file) {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'File upload error'];
    }

    if ($file['size'] > MAX_UPLOAD_SIZE) {
        return ['success' => false, 'message' => 'File size exceeds limit (5MB)'];
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime_type, ALLOWED_IMAGE_TYPES)) {
        return ['success' => false, 'message' => 'Invalid file type. Only JPG, PNG, GIF allowed'];
    }

    return ['success' => true, 'mime_type' => $mime_type];
}

/**
 * Upload file
 */
function upload_file($file, $upload_dir) {
    // Validate file
    $validation = validate_file_upload($file);
    if (!$validation['success']) {
        return $validation;
    }

    try {
        // Generate unique filename
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'file_' . time() . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
        $filepath = $upload_dir . $filename;

        // Create directory if not exists
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            return ['success' => false, 'message' => 'Failed to save file'];
        }

        // Set permissions
        chmod($filepath, 0644);

        return ['success' => true, 'filename' => $filename, 'filepath' => $filepath];
    } catch (Exception $e) {
        error_log("File upload error: " . $e->getMessage());
        return ['success' => false, 'message' => 'File upload failed'];
    }
}

/**
 * Generate order code
 */
function generate_order_code() {
    return 'ORD' . date('YmdHis') . strtoupper(substr(md5(uniqid()), 0, 6));
}

/**
 * Format currency
 */
function format_currency($amount) {
    return number_format($amount, 2, '.', ',') . ' LAK';
}

/**
 * Format date
 */
function format_date($date) {
    return date('d/m/Y H:i', strtotime($date));
}
?>
