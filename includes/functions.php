<?php
// includes/functions.php

class UrlShortener {
    private $conn;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Generate unique short code
    public function generateShortCode($length = SHORT_URL_LENGTH) {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $shortCode = '';
        
        do {
            $shortCode = '';
            for ($i = 0; $i < $length; $i++) {
                $shortCode .= $characters[rand(0, strlen($characters) - 1)];
            }
        } while ($this->shortCodeExists($shortCode));
        
        return $shortCode;
    }
    
    // Check if short code exists
    private function shortCodeExists($shortCode) {
        $query = "SELECT id FROM urls WHERE short_code = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$shortCode]);
        return $stmt->rowCount() > 0;
    }
    
    // Validate URL
    public function validateUrl($url) {
        // Add http if missing
        if (!preg_match('/^https?:\/\//', $url)) {
            $url = 'http://' . $url;
        }
        
        // Basic URL validation
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }
        
        // Check for malicious URLs
        $dangerous_domains = ['malware.com', 'phishing.com', 'spam.com'];
        $parsed = parse_url($url);
        if (isset($parsed['host']) && in_array($parsed['host'], $dangerous_domains)) {
            return false;
        }
        
        return $url;
    }
    
    // Create short URL
    public function createShortUrl($originalUrl, $customCode = null) {
        $validatedUrl = $this->validateUrl($originalUrl);
        if (!$validatedUrl) {
            return ['error' => 'Invalid URL format'];
        }
        
        if (strlen($originalUrl) > MAX_URL_LENGTH) {
            return ['error' => 'URL too long (max ' . MAX_URL_LENGTH . ' characters)'];
        }
        
        // Check rate limit
        if (!$this->checkRateLimit()) {
            return ['error' => 'Rate limit exceeded. Please try again later.'];
        }
        
        // Validate custom code
        if ($customCode) {
            if (!preg_match('/^[a-zA-Z0-9_-]+$/', $customCode)) {
                return ['error' => 'Custom code can only contain letters, numbers, hyphens and underscores'];
            }
            if (strlen($customCode) > 20) {
                return ['error' => 'Custom code too long (max 20 characters)'];
            }
            if ($this->shortCodeExists($customCode)) {
                return ['error' => 'Custom code already exists'];
            }
        }
        
        $shortCode = $customCode ?: $this->generateShortCode();
        
        $query = "INSERT INTO urls (original_url, short_code, user_ip) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        
        try {
            $stmt->execute([$validatedUrl, $shortCode, $_SERVER['REMOTE_ADDR'] ?? 'unknown']);
            return [
                'success' => true,
                'short_url' => SITE_URL . '/' . $shortCode,
                'short_code' => $shortCode,
                'original_url' => $validatedUrl
            ];
        } catch (PDOException $e) {
            error_log("URL creation error: " . $e->getMessage());
            return ['error' => 'Database error occurred'];
        }
    }
    
    // Get original URL
    public function getOriginalUrl($shortCode) {
        $query = "SELECT * FROM urls WHERE short_code = ? AND is_active = 1 LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$shortCode]);
        
        if ($stmt->rowCount() > 0) {
            $url = $stmt->fetch();
            
            // Check if expired
            if ($url['expires_at'] && strtotime($url['expires_at']) < time()) {
                return null;
            }
            
            // Update click count
            $this->updateClickCount($url['id']);
            
            return $url['original_url'];
        }
        
        return null;
    }
    
    // Update click count
    private function updateClickCount($urlId) {
        $updateQuery = "UPDATE urls SET clicks = clicks + 1 WHERE id = ?";
        $updateStmt = $this->conn->prepare($updateQuery);
        $updateStmt->execute([$urlId]);
        
        // Save click statistics
        if (ENABLE_ANALYTICS) {
            $this->saveClickStats($urlId);
        }
    }
    
    // Save click statistics
    private function saveClickStats($urlId) {
        $query = "INSERT INTO click_stats (url_id, user_ip, user_agent, referer) VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([
            $urlId,
            $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            $_SERVER['HTTP_USER_AGENT'] ?? '',
            $_SERVER['HTTP_REFERER'] ?? ''
        ]);
    }
    
    // Rate limiting
    private function checkRateLimit() {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $query = "SELECT COUNT(*) as count FROM urls WHERE user_ip = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$ip]);
        $result = $stmt->fetch();
        
        return $result['count'] < RATE_LIMIT;
    }
    
    // Get URL statistics
    public function getUrlStats($shortCode) {
        $query = "SELECT u.*, COUNT(cs.id) as total_clicks,
                         DATE(u.created_at) as created_date,
                         TIME(u.created_at) as created_time
                  FROM urls u 
                  LEFT JOIN click_stats cs ON u.id = cs.url_id 
                  WHERE u.short_code = ? 
                  GROUP BY u.id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$shortCode]);
        return $stmt->fetch();
    }
    
    // Get recent URLs
    public function getRecentUrls($limit = 10) {
        $query = "SELECT short_code, original_url, clicks, created_at 
                  FROM urls 
                  WHERE is_active = 1 
                  ORDER BY created_at DESC 
                  LIMIT ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
    
    // Get click statistics for a URL
    public function getClickStats($shortCode, $days = 7) {
        $query = "SELECT DATE(cs.clicked_at) as date, COUNT(*) as clicks
                  FROM click_stats cs
                  INNER JOIN urls u ON cs.url_id = u.id
                  WHERE u.short_code = ? 
                  AND cs.clicked_at > DATE_SUB(NOW(), INTERVAL ? DAY)
                  GROUP BY DATE(cs.clicked_at)
                  ORDER BY date";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$shortCode, $days]);
        return $stmt->fetchAll();
    }
    
    // Get top URLs
    public function getTopUrls($limit = 10) {
        $query = "SELECT short_code, original_url, clicks, created_at
                  FROM urls 
                  WHERE is_active = 1 
                  ORDER BY clicks DESC 
                  LIMIT ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
    
    // Get total statistics
    public function getTotalStats() {
        $query = "SELECT 
                    COUNT(*) as total_urls,
                    SUM(clicks) as total_clicks,
                    COUNT(DISTINCT user_ip) as unique_users,
                    COUNT(CASE WHEN created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR) THEN 1 END) as urls_today
                  FROM urls 
                  WHERE is_active = 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetch();
    }
    
    // Disable URL
    public function disableUrl($shortCode) {
        $query = "UPDATE urls SET is_active = 0 WHERE short_code = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$shortCode]);
    }
    
    // Delete old URLs (cleanup)
    public function cleanupOldUrls($days = 365) {
        $query = "DELETE FROM urls WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$days]);
    }
}
?> 