<?php
// install.php - Database Installation Script
require_once 'includes/config.php';
require_once 'includes/database.php';

$installation_success = false;
$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Create database connection
        $database = new Database();
        $db = $database->getConnection();
        
        if (!$db) {
            throw new Exception("Veritabanı bağlantısı kurulamadı!");
        }
        
        // Create tables
        $result = $database->createTables();
        
        if ($result) {
            $installation_success = true;
            $success_message = "Veritabanı tabloları başarıyla oluşturuldu!";
            
            // Insert sample data
            $sampleUrls = [
                [
                    'original_url' => 'https://www.google.com',
                    'short_code' => 'google',
                    'user_ip' => '127.0.0.1'
                ],
                [
                    'original_url' => 'https://www.github.com',
                    'short_code' => 'github',
                    'user_ip' => '127.0.0.1'
                ],
                [
                    'original_url' => 'https://www.stackoverflow.com',
                    'short_code' => 'stack',
                    'user_ip' => '127.0.0.1'
                ]
            ];
            
            $query = "INSERT INTO urls (original_url, short_code, user_ip) VALUES (?, ?, ?)";
            $stmt = $db->prepare($query);
            
            foreach ($sampleUrls as $url) {
                $stmt->execute([$url['original_url'], $url['short_code'], $url['user_ip']]);
            }
            
            $success_message .= " Örnek veriler de eklendi.";
            
        } else {
            throw new Exception("Tablolar oluşturulamadı!");
        }
        
    } catch (Exception $e) {
        $error_message = "Kurulum hatası: " . $e->getMessage();
        error_log("Installation error: " . $e->getMessage());
    }
}

// Check if tables already exist
$tables_exist = false;
try {
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        $query = "SHOW TABLES LIKE 'urls'";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $tables_exist = $stmt->rowCount() > 0;
    }
} catch (Exception $e) {
    // Database might not exist yet
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kurulum - <?php echo htmlspecialchars(SITE_NAME); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            font-family: 'Arial', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .install-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            max-width: 600px;
            margin: 0 auto;
        }
        .install-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 20px 20px 0 0;
            padding: 30px;
            text-align: center;
        }
        .install-body {
            padding: 40px;
        }
        .btn-install {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 25px;
            padding: 12px 30px;
            color: white;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        .btn-install:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            color: white;
        }
        .alert {
            border-radius: 15px;
            border: none;
        }
        .config-info {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .status-icon {
            font-size: 24px;
            margin-right: 10px;
        }
        .requirement {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }
        .requirement.success {
            color: #28a745;
        }
        .requirement.error {
            color: #dc3545;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="install-card">
            <div class="install-header">
                <h1><i class="fas fa-cog me-3"></i>URL Shortener Kurulum</h1>
                <p class="mb-0">Veritabanı tablolarını oluşturmak için kurulumu başlatın</p>
            </div>
            
            <div class="install-body">
                <?php if ($installation_success): ?>
                    <div class="alert alert-success" role="alert">
                        <i class="fas fa-check-circle me-2"></i><?php echo $success_message; ?>
                    </div>
                    <div class="text-center">
                        <a href="index.php" class="btn btn-install">
                            <i class="fas fa-home me-2"></i>Ana Sayfaya Git
                        </a>
                        <div class="mt-3">
                            <small class="text-muted">
                                <i class="fas fa-exclamation-triangle me-1"></i>
                                Güvenlik için bu install.php dosyasını silin!
                            </small>
                        </div>
                    </div>
                <?php elseif ($error_message): ?>
                    <div class="alert alert-danger" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error_message; ?>
                    </div>
                    <div class="text-center">
                        <button onclick="location.reload()" class="btn btn-install">
                            <i class="fas fa-redo me-2"></i>Tekrar Dene
                        </button>
                    </div>
                <?php else: ?>
                    
                    <!-- System Requirements Check -->
                    <div class="config-info">
                        <h5><i class="fas fa-server me-2"></i>Sistem Gereksinimleri</h5>
                        
                        <?php
                        $php_version_ok = version_compare(PHP_VERSION, '7.4.0', '>=');
                        $pdo_available = extension_loaded('pdo');
                        $pdo_mysql_available = extension_loaded('pdo_mysql');
                        $zip_available = extension_loaded('zip');
                        ?>
                        
                        <div class="requirement <?php echo $php_version_ok ? 'success' : 'error'; ?>">
                            <i class="fas <?php echo $php_version_ok ? 'fa-check' : 'fa-times'; ?> status-icon"></i>
                            PHP Version: <?php echo PHP_VERSION; ?> (Minimum: 7.4)
                        </div>
                        
                        <div class="requirement <?php echo $pdo_available ? 'success' : 'error'; ?>">
                            <i class="fas <?php echo $pdo_available ? 'fa-check' : 'fa-times'; ?> status-icon"></i>
                            PDO Extension: <?php echo $pdo_available ? 'Mevcut' : 'Eksik'; ?>
                        </div>
                        
                        <div class="requirement <?php echo $pdo_mysql_available ? 'success' : 'error'; ?>">
                            <i class="fas <?php echo $pdo_mysql_available ? 'fa-check' : 'fa-times'; ?> status-icon"></i>
                            PDO MySQL Extension: <?php echo $pdo_mysql_available ? 'Mevcut' : 'Eksik'; ?>
                        </div>
                        
                        <div class="requirement <?php echo $zip_available ? 'success' : 'error'; ?>">
                            <i class="fas <?php echo $zip_available ? 'fa-check' : 'fa-times'; ?> status-icon"></i>
                            ZIP Extension: <?php echo $zip_available ? 'Mevcut' : 'Eksik'; ?>
                        </div>
                    </div>

                    <!-- Database Configuration -->
                    <div class="config-info">
                        <h5><i class="fas fa-database me-2"></i>Veritabanı Yapılandırması</h5>
                        <p><strong>Host:</strong> <?php echo DB_HOST; ?></p>
                        <p><strong>Database:</strong> <?php echo DB_NAME; ?></p>
                        <p><strong>Username:</strong> <?php echo DB_USER; ?></p>
                        <p class="mb-0"><strong>Charset:</strong> <?php echo DB_CHARSET; ?></p>
                    </div>

                    <?php if ($tables_exist): ?>
                        <div class="alert alert-warning" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Veritabanı tabloları zaten mevcut! Kurulum çalıştırılırsa mevcut veriler silinecek.
                        </div>
                    <?php endif; ?>

                    <!-- Installation Form -->
                    <?php if ($php_version_ok && $pdo_available && $pdo_mysql_available): ?>
                        <form method="post" class="text-center">
                            <h5 class="mb-3">Kuruluma Hazır!</h5>
                            <p class="text-muted mb-4">
                                Bu işlem veritabanında gerekli tabloları oluşturacak ve örnek veriler ekleyecektir.
                            </p>
                            
                            <?php if ($tables_exist): ?>
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="confirmOverwrite" required>
                                    <label class="form-check-label" for="confirmOverwrite">
                                        Mevcut verilerin silinmesini onaylıyorum
                                    </label>
                                </div>
                            <?php endif; ?>
                            
                            <button type="submit" class="btn btn-install">
                                <i class="fas fa-play me-2"></i>Kurulumu Başlat
                            </button>
                        </form>
                    <?php else: ?>
                        <div class="alert alert-danger" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Sistem gereksinimleri karşılanmıyor! Lütfen eksik uzantıları yükleyin.
                        </div>
                    <?php endif; ?>
                    
                    <div class="mt-4 text-center">
                        <small class="text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            Kurulum öncesi config.php dosyasındaki veritabanı ayarlarını kontrol edin.
                        </small>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 