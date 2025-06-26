<?php
// includes/database.php

class Database {
    private $host = DB_HOST;
    private $db_name = DB_NAME;
    private $username = DB_USER;
    private $password = DB_PASS;
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4",
                $this->username,
                $this->password,
                array(
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                )
            );
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
            die();
        }
        return $this->conn;
    }
    
    public function createTables() {
        $sql = "
        CREATE TABLE IF NOT EXISTS urls (
            id INT AUTO_INCREMENT PRIMARY KEY,
            original_url VARCHAR(2048) NOT NULL,
            short_code VARCHAR(10) UNIQUE NOT NULL,
            clicks INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            expires_at TIMESTAMP NULL,
            user_ip VARCHAR(45),
            is_active BOOLEAN DEFAULT TRUE,
            INDEX idx_short_code (short_code),
            INDEX idx_created_at (created_at),
            INDEX idx_user_ip (user_ip)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

        CREATE TABLE IF NOT EXISTS click_stats (
            id INT AUTO_INCREMENT PRIMARY KEY,
            url_id INT,
            clicked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            user_ip VARCHAR(45),
            user_agent TEXT,
            referer VARCHAR(500),
            country VARCHAR(2),
            city VARCHAR(100),
            FOREIGN KEY (url_id) REFERENCES urls(id) ON DELETE CASCADE,
            INDEX idx_url_id (url_id),
            INDEX idx_clicked_at (clicked_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";
        
        try {
            $this->conn->exec($sql);
            return true;
        } catch(PDOException $e) {
            error_log("Database table creation error: " . $e->getMessage());
            return false;
        }
    }
}
?> 