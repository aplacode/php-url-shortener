<?php
// redirect.php
require_once 'includes/config.php';
require_once 'includes/database.php';
require_once 'includes/functions.php';

// Get short code from URL
$shortCode = '';
$request_uri = $_SERVER['REQUEST_URI'];

// Remove query string if exists
$request_uri = strtok($request_uri, '?');

// Get the path components
$pathInfo = trim($request_uri, '/');

// If there's a path, use it as short code
if (!empty($pathInfo) && $pathInfo !== 'index.php') {
    $shortCode = $pathInfo;
}

// If no short code, redirect to home
if (empty($shortCode)) {
    header('Location: ' . SITE_URL);
    exit;
}

try {
    // Create database connection
    $database = new Database();
    $db = $database->getConnection();
    
    if (!$db) {
        throw new Exception("Database connection failed");
    }
    
    // Create URL shortener instance
    $urlShortener = new UrlShortener($db);
    
    // Get original URL
    $originalUrl = $urlShortener->getOriginalUrl($shortCode);
    
    if ($originalUrl) {
        // Track the click in analytics if enabled
        if (ENABLE_ANALYTICS) {
            // Additional analytics can be added here
            error_log("Redirect: $shortCode -> $originalUrl");
        }
        
        // Redirect to original URL
        header('Location: ' . $originalUrl, true, 301);
        exit;
    } else {
        // URL not found - show 404 page
        http_response_code(404);
        ?>
        <!DOCTYPE html>
        <html lang="tr">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>URL Bulunamadı - <?php echo htmlspecialchars(SITE_NAME); ?></title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
            <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
            <style>
                body {
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    height: 100vh;
                    display: flex;
                    align-items: center;
                    font-family: 'Arial', sans-serif;
                }
                .error-container {
                    text-align: center;
                    color: white;
                }
                .error-code {
                    font-size: 120px;
                    font-weight: bold;
                    text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
                }
                .error-message {
                    font-size: 24px;
                    margin-bottom: 30px;
                }
                .back-btn {
                    background: rgba(255,255,255,0.2);
                    border: 2px solid white;
                    color: white;
                    padding: 12px 30px;
                    border-radius: 50px;
                    font-size: 18px;
                    transition: all 0.3s ease;
                }
                .back-btn:hover {
                    background: white;
                    color: #667eea;
                    transform: translateY(-2px);
                    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
                }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-md-6">
                        <div class="error-container">
                            <div class="error-code">404</div>
                            <div class="error-message">
                                <i class="fas fa-exclamation-triangle mb-3" style="font-size: 48px;"></i><br>
                                Aradığınız kısa URL bulunamadı!
                            </div>
                            <p class="mb-4">
                                URL süresi dolmuş olabilir veya yanlış bir bağlantıya tıklamış olabilirsiniz.
                            </p>
                            <a href="<?php echo SITE_URL; ?>" class="btn back-btn">
                                <i class="fas fa-home me-2"></i>Ana Sayfaya Dön
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </body>
        </html>
        <?php
    }
    
} catch (Exception $e) {
    error_log("Redirect error: " . $e->getMessage());
    http_response_code(500);
    ?>
    <!DOCTYPE html>
    <html lang="tr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Hata - <?php echo htmlspecialchars(SITE_NAME); ?></title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
        <style>
            body {
                background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
                height: 100vh;
                display: flex;
                align-items: center;
                font-family: 'Arial', sans-serif;
            }
            .error-container {
                text-align: center;
                color: white;
            }
            .error-code {
                font-size: 120px;
                font-weight: bold;
                text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
            }
            .error-message {
                font-size: 24px;
                margin-bottom: 30px;
            }
            .back-btn {
                background: rgba(255,255,255,0.2);
                border: 2px solid white;
                color: white;
                padding: 12px 30px;
                border-radius: 50px;
                font-size: 18px;
                transition: all 0.3s ease;
            }
            .back-btn:hover {
                background: white;
                color: #ff6b6b;
                transform: translateY(-2px);
                box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="error-container">
                        <div class="error-code">500</div>
                        <div class="error-message">
                            <i class="fas fa-exclamation-circle mb-3" style="font-size: 48px;"></i><br>
                            Sunucu Hatası!
                        </div>
                        <p class="mb-4">
                            Bir teknik sorun oluştu. Lütfen daha sonra tekrar deneyin.
                        </p>
                        <a href="<?php echo SITE_URL; ?>" class="btn back-btn">
                            <i class="fas fa-home me-2"></i>Ana Sayfaya Dön
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
}
?> 