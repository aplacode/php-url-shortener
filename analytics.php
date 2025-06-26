<?php
// analytics.php
require_once 'includes/config.php';
require_once 'includes/database.php';
require_once 'includes/functions.php';

try {
    // Create database connection
    $database = new Database();
    $db = $database->getConnection();
    
    if (!$db) {
        throw new Exception("Database connection failed");
    }
    
    // Create URL shortener instance
    $urlShortener = new UrlShortener($db);
    
    // Get statistics
    $totalStats = $urlShortener->getTotalStats();
    $recentUrls = $urlShortener->getRecentUrls(15);
    $topUrls = $urlShortener->getTopUrls(10);
    
} catch (Exception $e) {
    error_log("Analytics error: " . $e->getMessage());
    $error_message = "İstatistikler yüklenirken bir hata oluştu.";
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>İstatistikler - <?php echo htmlspecialchars(SITE_NAME); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/chart.js@4.0.1/dist/chart.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            font-family: 'Arial', sans-serif;
            min-height: 100vh;
        }
        .main-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            margin-top: 30px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .stat-label {
            font-size: 0.9rem;
            opacity: 0.9;
        }
        .table-responsive {
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        .table {
            margin-bottom: 0;
        }
        .table thead {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .url-cell {
            max-width: 300px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .chart-container {
            background: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        .navbar {
            background: rgba(255, 255, 255, 0.1) !important;
            backdrop-filter: blur(10px);
        }
        .navbar-brand, .nav-link {
            color: white !important;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 25px;
            padding: 10px 25px;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-link me-2"></i><?php echo htmlspecialchars(SITE_NAME); ?>
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="index.php">
                    <i class="fas fa-home me-1"></i>Ana Sayfa
                </a>
                <a class="nav-link active" href="analytics.php">
                    <i class="fas fa-chart-bar me-1"></i>İstatistikler
                </a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="main-card">
            <div class="card-body p-5">
                <h1 class="text-center mb-5">
                    <i class="fas fa-chart-line me-3"></i>URL Shortener İstatistikleri
                </h1>

                <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error_message; ?>
                    </div>
                <?php else: ?>
                    <!-- Statistics Cards -->
                    <div class="row mb-5">
                        <div class="col-md-3">
                            <div class="stat-card text-center">
                                <div class="stat-number"><?php echo number_format($totalStats['total_urls'] ?? 0); ?></div>
                                <div class="stat-label">
                                    <i class="fas fa-link me-2"></i>Toplam URL
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-card text-center">
                                <div class="stat-number"><?php echo number_format($totalStats['total_clicks'] ?? 0); ?></div>
                                <div class="stat-label">
                                    <i class="fas fa-mouse-pointer me-2"></i>Toplam Tıklama
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-card text-center">
                                <div class="stat-number"><?php echo number_format($totalStats['unique_users'] ?? 0); ?></div>
                                <div class="stat-label">
                                    <i class="fas fa-users me-2"></i>Benzersiz Kullanıcı
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-card text-center">
                                <div class="stat-number"><?php echo number_format($totalStats['urls_today'] ?? 0); ?></div>
                                <div class="stat-label">
                                    <i class="fas fa-calendar-day me-2"></i>Bugün Oluşturulan
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Charts Row -->
                    <div class="row mb-5">
                        <div class="col-md-6">
                            <div class="chart-container">
                                <h5 class="mb-3">
                                    <i class="fas fa-chart-pie me-2"></i>En Çok Tıklanan URL'ler
                                </h5>
                                <canvas id="topUrlsChart" width="400" height="300"></canvas>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="chart-container">
                                <h5 class="mb-3">
                                    <i class="fas fa-chart-line me-2"></i>Son 7 Günlük Aktivite
                                </h5>
                                <canvas id="activityChart" width="400" height="300"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Top URLs Table -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5 class="mb-3">
                                <i class="fas fa-trophy me-2"></i>En Çok Tıklanan URL'ler
                            </h5>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Sıra</th>
                                            <th>Kısa URL</th>
                                            <th>Orijinal URL</th>
                                            <th>Tıklama</th>
                                            <th>Oluşturulma</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($topUrls)): ?>
                                            <?php foreach ($topUrls as $index => $url): ?>
                                                <tr>
                                                    <td>
                                                        <span class="badge bg-primary"><?php echo $index + 1; ?></span>
                                                    </td>
                                                    <td>
                                                        <a href="<?php echo SITE_URL . '/' . htmlspecialchars($url['short_code']); ?>" 
                                                           target="_blank" class="text-decoration-none">
                                                            <?php echo htmlspecialchars($url['short_code']); ?>
                                                            <i class="fas fa-external-link-alt ms-1"></i>
                                                        </a>
                                                    </td>
                                                    <td class="url-cell" title="<?php echo htmlspecialchars($url['original_url']); ?>">
                                                        <?php echo htmlspecialchars($url['original_url']); ?>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-success"><?php echo number_format($url['clicks']); ?></span>
                                                    </td>
                                                    <td>
                                                        <?php echo date('d.m.Y H:i', strtotime($url['created_at'])); ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="5" class="text-center py-4">
                                                    <i class="fas fa-info-circle me-2"></i>Henüz URL oluşturulmamış.
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Recent URLs Table -->
                    <div class="row">
                        <div class="col-12">
                            <h5 class="mb-3">
                                <i class="fas fa-clock me-2"></i>Son Oluşturulan URL'ler
                            </h5>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Kısa URL</th>
                                            <th>Orijinal URL</th>
                                            <th>Tıklama</th>
                                            <th>Oluşturulma</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($recentUrls)): ?>
                                            <?php foreach ($recentUrls as $url): ?>
                                                <tr>
                                                    <td>
                                                        <a href="<?php echo SITE_URL . '/' . htmlspecialchars($url['short_code']); ?>" 
                                                           target="_blank" class="text-decoration-none">
                                                            <?php echo htmlspecialchars($url['short_code']); ?>
                                                            <i class="fas fa-external-link-alt ms-1"></i>
                                                        </a>
                                                    </td>
                                                    <td class="url-cell" title="<?php echo htmlspecialchars($url['original_url']); ?>">
                                                        <?php echo htmlspecialchars($url['original_url']); ?>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-info"><?php echo number_format($url['clicks']); ?></span>
                                                    </td>
                                                    <td>
                                                        <?php echo date('d.m.Y H:i', strtotime($url['created_at'])); ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="4" class="text-center py-4">
                                                    <i class="fas fa-info-circle me-2"></i>Henüz URL oluşturulmamış.
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="text-center mt-4">
                    <a href="index.php" class="btn btn-primary">
                        <i class="fas fa-arrow-left me-2"></i>Ana Sayfaya Dön
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.0.1/dist/chart.min.js"></script>
    
    <?php if (!isset($error_message) && !empty($topUrls)): ?>
    <script>
        // Top URLs Pie Chart
        const topUrlsCtx = document.getElementById('topUrlsChart').getContext('2d');
        const topUrlsChart = new Chart(topUrlsCtx, {
            type: 'pie',
            data: {
                labels: [
                    <?php foreach (array_slice($topUrls, 0, 5) as $url): ?>
                        '<?php echo addslashes(substr($url['short_code'], 0, 10)); ?>',
                    <?php endforeach; ?>
                ],
                datasets: [{
                    data: [
                        <?php foreach (array_slice($topUrls, 0, 5) as $url): ?>
                            <?php echo $url['clicks']; ?>,
                        <?php endforeach; ?>
                    ],
                    backgroundColor: [
                        '#667eea',
                        '#764ba2',
                        '#f093fb',
                        '#f5576c',
                        '#4facfe'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Activity Chart (placeholder data)
        const activityCtx = document.getElementById('activityChart').getContext('2d');
        const activityChart = new Chart(activityCtx, {
            type: 'line',
            data: {
                labels: ['6 gün önce', '5 gün önce', '4 gün önce', '3 gün önce', '2 gün önce', 'Dün', 'Bugün'],
                datasets: [{
                    label: 'Yeni URL\'ler',
                    data: [12, 19, 3, 5, 2, 3, 7],
                    borderColor: '#667eea',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    tension: 0.4
                }, {
                    label: 'Tıklamalar',
                    data: [65, 59, 80, 81, 56, 55, 40],
                    borderColor: '#764ba2',
                    backgroundColor: 'rgba(118, 75, 162, 0.1)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
    <?php endif; ?>
</body>
</html> 