<?php
/**
 * Database Configuration (PDO)
 * Dealka Marketplace
 */

// Database credentials
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'dealka_db');
define('DB_PORT', 3306);

// Application settings
define('BASE_URL', 'http://localhost/dealka_new/');
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('LOGS_DIR', __DIR__ . '/../logs/');
define('ASSETS_DIR', __DIR__ . '/../assets/');

// Fee settings
define('SELLER_FEE_PERCENT', 3.0);      // 3% fee on sale
define('WITHDRAWAL_FEE_PERCENT', 1.0);  // 1% fee on withdrawal
define('WITHDRAWAL_MIN_FEE', 1000);     // Minimum 1,000 LAK

// File upload settings
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif']);
define('ALLOWED_IMAGE_EXT', ['jpg', 'jpeg', 'png', 'gif']);

// Session settings
define('SESSION_TIMEOUT', 3600 * 24); // 24 hours
define('CSRF_TOKEN_LENGTH', 32);

// Error handling
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', LOGS_DIR . 'php_errors.log');

// Create logs directory if not exists
if (!is_dir(LOGS_DIR)) {
    mkdir(LOGS_DIR, 0755, true);
}

// Create upload directories if not exists
$upload_dirs = [
    UPLOAD_DIR . 'products/',
    UPLOAD_DIR . 'slips/'
];

foreach ($upload_dirs as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

/**
 * PDO Database Connection
 */
class Database {
    private static $instance = null;
    private $pdo;

    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
            ];

            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            die("Database connection failed. Please check your configuration.");
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->pdo;
    }

    public function beginTransaction() {
        return $this->pdo->beginTransaction();
    }

    public function commit() {
        return $this->pdo->commit();
    }

    public function rollBack() {
        return $this->pdo->rollBack();
    }
}

// Get database connection
$db = Database::getInstance();
$pdo = $db->getConnection();

/**
 * Helper function to log actions
 */
function log_action($user_id, $action, $description, $table_name = null, $record_id = null, $old_value = null, $new_value = null) {
    global $pdo;
    
    try {
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'CLI';
        
        $stmt = $pdo->prepare("
            INSERT INTO logs (user_id, action, description, table_name, record_id, old_value, new_value, ip_address)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $user_id,
            $action,
            $description,
            $table_name,
            $record_id,
            $old_value,
            $new_value,
            $ip_address
        ]);
    } catch (Exception $e) {
        error_log("Logging failed: " . $e->getMessage());
    }
}
?>
