<?php
// SQLite Configuration for Electron App

// Set timezone to ensure consistent date handling
date_default_timezone_set('America/New_York'); // Change this to your local timezone

$database_path = __DIR__ . '/database/vessel_logger.db';

// Create database directory if it doesn't exist
$database_dir = dirname($database_path);
if (!is_dir($database_dir)) {
    mkdir($database_dir, 0755, true);
}

// SQLite connection using PDO (more compatible than sqlite3 extension)
try {
    $pdo = new PDO("sqlite:$database_path");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // Initialize database if it's empty
    $tables_check = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='vessels'");
    if ($tables_check->rowCount() == 0) {
        // Database is empty, initialize it
        $schema = file_get_contents(__DIR__ . '/database/vessel_logger.sql');
        $pdo->exec($schema);
    }
    
} catch (PDOException $e) {
    die("SQLite Connection failed: " . $e->getMessage());
}

// Create a mysqli-compatible wrapper for existing code
class SqliteWrapper {
    public $pdo; // Make PDO accessible
    public $connect_error = null;
    public $error = null;
    public $insert_id = 0;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function query($sql) {
        try {
            $stmt = $this->pdo->query($sql);
            $this->insert_id = $this->pdo->lastInsertId();
            return new SqliteResultWrapper($stmt);
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }
    
    public function prepare($sql) {
        try {
            // Convert MySQL ? placeholders to named placeholders for some compatibility
            $stmt = $this->pdo->prepare($sql);
            return new SqliteStatementWrapper($stmt, $this);
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }
    
    public function real_escape_string($string) {
        // PDO handles escaping, but for compatibility
        return str_replace("'", "''", $string);
    }
    
    public function close() {
        $this->pdo = null;
    }
}

class SqliteResultWrapper {
    private $stmt;
    private $results = [];
    private $position = 0;
    public $num_rows = 0;
    
    public function __construct($stmt) {
        $this->stmt = $stmt;
        if ($stmt) {
            $this->results = $stmt->fetchAll();
            $this->num_rows = count($this->results);
        }
    }
    
    public function fetch_assoc() {
        if ($this->position < count($this->results)) {
            return $this->results[$this->position++];
        }
        return null;
    }
    
    public function fetch_array() {
        return $this->fetch_assoc();
    }
    
    public function fetch_all($mode = null) {
        // Return all remaining results
        $remaining = [];
        while ($this->position < count($this->results)) {
            $remaining[] = $this->results[$this->position++];
        }
        return $remaining;
    }
}

class SqliteStatementWrapper {
    private $stmt;
    private $wrapper;
    public $error = null;
    
    public function __construct($stmt, $wrapper) {
        $this->stmt = $stmt;
        $this->wrapper = $wrapper;
    }
    
    public function bind_param($types, ...$params) {
        try {
            for ($i = 0; $i < count($params); $i++) {
                $this->stmt->bindValue($i + 1, $params[$i]);
            }
            return true;
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }
    
    public function execute() {
        try {
            $result = $this->stmt->execute();
            $this->wrapper->insert_id = $this->wrapper->pdo->lastInsertId();
            return $result;
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }
    
    public function get_result() {
        return new SqliteResultWrapper($this->stmt);
    }
    
    public $affected_rows = 0;
}

// Create the wrapper to make it compatible with existing mysqli code
$conn = new SqliteWrapper($pdo);
?>
