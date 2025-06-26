<?php
require_once 'includes/config.php';
require_once 'includes/database.php';
require_once 'includes/functions.php';

$database = new Database();
$db = $database->getConnection();
$urlShortener = new UrlShortener($db);

$result = null;

if ($_POST && isset($_POST['url'])) {
    $originalUrl = trim($_POST['url']);
    $customCode = !empty($_POST['custom_code']) ? trim($_POST['custom_code']) : null;
    
    $result = $urlShortener->createShortUrl($originalUrl, $customCode);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP URL Shortener</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🔗</text></svg>">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white text-center">
                        <h2>🔗 URL Shortener</h2>
                        <p class="mb-0">Shorten your long URLs quickly and easily</p>
                    </div>
                    <div class="card-body">
                        <?php if ($result): ?>
                            <?php if (isset($result['error'])): ?>
                                <div class="alert alert-danger">
                                    <strong>Error:</strong> <?= htmlspecialchars($result['error']) ?>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-success">
                                    <strong>Success!</strong> Your URL has been shortened:
                                    <br><br>
                                    <div class="input-group">
                                        <input type="text" class="form-control" value="<?= htmlspecialchars($result['short_url']) ?>" id="shortUrl" readonly>
                                        <button class="btn btn-outline-secondary" type="button" onclick="copyToClipboard()">Copy</button>
                                    </div>
                                    <small class="text-muted">
                                        <a href="<?= htmlspecialchars($result['short_url']) ?>+">View Statistics</a>
                                    </small>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="url" class="form-label">Enter URL to shorten:</label>
                                <input type="url" class="form-control" id="url" name="url" 
                                       placeholder="https://example.com/very/long/url" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="custom_code" class="form-label">Custom short code (optional):</label>
                                <input type="text" class="form-control" id="custom_code" name="custom_code" 
                                       placeholder="my-custom-code" maxlength="20">
                                <small class="text-muted">Leave empty for random code</small>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100">Shorten URL</button>
                        </form>
                    </div>
                    
                    <div class="card-footer text-center text-muted">
                        <small>Built with PHP & MySQL | <a href="admin/dashboard.php">Admin Dashboard</a></small>
                    </div>
                </div>
                
                <!-- Features -->
                <div class="row mt-4">
                    <div class="col-md-4">
                        <div class="text-center">
                            <h5>⚡ Fast</h5>
                            <p class="text-muted">Lightning fast redirects</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center">
                            <h5>📊 Analytics</h5>
                            <p class="text-muted">Track clicks and statistics</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center">
                            <h5>🎯 Custom</h5>
                            <p class="text-muted">Custom short codes available</p>
                        </div>
                    </div>
                </div>
                
                <!-- Recent URLs -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Recent Shortened URLs</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Short URL</th>
                                                <th>Original URL</th>
                                                <th>Clicks</th>
                                                <th>Created</th>
                                            </tr>
                                        </thead>
                                        <tbody id="recentUrls">
                                            <!-- Dynamically populated -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/app.js"></script>
</body>
</html> 