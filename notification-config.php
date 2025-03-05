<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $project_id = $_POST['project_id'] ?? '';
    $default_topic = $_POST['default_topic'] ?? '';
    $google_services_json = $_POST['google_services_json'] ?? '';

    if (empty($project_id) || empty($default_topic)) {
        $_SESSION['error_message'] = 'অনুগ্রহ করে সকল প্রয়োজনীয় ক্ষেত্র পূরণ করুন';
    } else {
        // Validate JSON format
        if (!empty($google_services_json)) {
            json_decode($google_services_json);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $_SESSION['error_message'] = 'google-services.json এর জন্য অবৈধ JSON ফরম্যাট';
            }
        }

        if (empty($_SESSION['error_message'])) {
            try {
                $conn = connectDB();
                // Check if config exists
                $stmt = $conn->prepare("SELECT id FROM notification_configs LIMIT 1");
                $stmt->execute();
                $config = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($config) {
                    // Update existing config
                    $stmt = $conn->prepare("UPDATE notification_configs SET firebase_project_id = ?, firebase_topic = ?, google_services_json = ?, updated_at = NOW() WHERE id = ?");
                    $stmt->execute([$project_id, $default_topic, $google_services_json, $config['id']]);
                } else {
                    // Insert new config
                    $stmt = $conn->prepare("INSERT INTO notification_configs (firebase_project_id, firebase_topic, google_services_json, created_by) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$project_id, $default_topic, $google_services_json, $_SESSION['user_id']]);
                }
                $_SESSION['success_message'] = 'কনফিগারেশন সফলভাবে সংরক্ষণ করা হয়েছে';
            } catch(PDOException $e) {
                $_SESSION['error_message'] = "ডাটাবেস ত্রুটি: " . $e->getMessage();
            }
        }
    }
    // Redirect after POST
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Get messages from session
$error_message = $_SESSION['error_message'] ?? '';
$success_message = $_SESSION['success_message'] ?? '';

// Clear messages
unset($_SESSION['error_message']);
unset($_SESSION['success_message']);

try {
    $conn = connectDB();
    // Get current config
    $stmt = $conn->prepare("SELECT * FROM notification_configs ORDER BY id DESC LIMIT 1");
    $stmt->execute();
    $current_config = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Get user data
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error_message = "ডাটাবেস ত্রুটি: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>নোটিফিকেশন কনফিগারেশন - অ্যাডমিন প্যানেল</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/hscbmt/cdn@latest/style.css">
    <link rel ="stylesheet" href="assets/css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="dashboard.php">
                <img src="assets/images/logo.png" alt="Logo" class="me-2" style="height: 32px;">
                <span>পুশ প্যানেল</span>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
                <i class="bi bi-list"></i>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">
                            <i class="bi bi-speedometer2 me-1"></i> ড্যাশবোর্ড
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="push-notification.php">
                            <i class="bi bi-bell me-1"></i> পুশ নোটিফিকেশন
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="notification-config.php">
                            <i class="bi bi-gear me-1"></i> কনফিগারেশন
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="admin-management.php">
                            <i class="bi bi-people me-1"></i> অ্যাডমিন ব্যবস্থাপনা
                        </a>
                    </li>
                </ul>
                
                <div class="d-flex align-items-center gap-3">
                    <button class="theme-toggle" onclick="toggleTheme()">
                        <i class="bi bi-sun-fill"></i>
                    </button>
                    <div class="dropdown">
                        <button class="btn btn-link dropdown-toggle text-decoration-none" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <?php echo htmlspecialchars($user['name']); ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="profile.php"><i class="bi bi-person me-2"></i>প্রোফাইল</a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>লগআউট</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <!-- Welcome section -->
        <div class="welcome-section">
            <h1 class="h3 mb-2">নোটিফিকেশন কনফিগারেশন</h1>
            <p class="mb-0">সর্বোত্তম ডেলিভারির জন্য আপনার ফায়ারবেস সেটিংস এবং নোটিফিকেশন প্যারামিটার কনফিগার করুন।</p>
        </div>

        <?php if ($success_message): ?>
            <div class="alert alert-success">
                <i class="bi bi-check-circle me-2"></i><?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>
        <?php if ($error_message): ?>
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle me-2"></i><?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-lg-8">
                <div class="notification-card">
                    <h2 class="section-title">ফায়ারবেস কনফিগারেশন</h2>
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="project_id" class="form-label">ফায়ারবেস প্রজেক্ট আইডি</label>
                            <input type="text" class="form-control" id="project_id" name="project_id" 
                                value="<?php echo htmlspecialchars($current_config['firebase_project_id'] ?? 'hscbmt-guide-boi'); ?>" required>
                            <div class="help-text">
                                <i class="bi bi-info-circle me-1"></i>
                                আপনার ফায়ারবেস প্রজেক্ট আইডি (উদাহরণ: Apps-name-xxxxxx)
                            </div>
                        </div>
                        <div class="mb-4">
                            <label for="default_topic" class="form-label">ডিফল্ট টপিক</label>
                            <input type="text" class="form-control" id="default_topic" name="default_topic" 
                                value="<?php echo htmlspecialchars($current_config['firebase_topic'] ?? 'allDevices'); ?>" required>
                            <div class="help-text">
                                <i class="bi bi-info-circle me-1"></i>
                                নোটিফিকেশন ব্রডকাস্টিং এর জন্য ডিফল্ট টপিক
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="google_services_json" class="form-label">google-services.json</label>
                            <textarea class="form-control" id="google_services_json" name="google_services_json" rows="10"><?php echo htmlspecialchars($current_config['google_services_json'] ?? ''); ?></textarea>
                            <div class="help-text">
                                <i class="bi bi-info-circle me-1"></i>
                                এখানে আপনার google-services.json এর কন্টেন্ট পেস্ট করুন
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-2"></i>কনফিগারেশন সংরক্ষণ করুন
                        </button>
                    </form>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="notification-card">
                    <h2 class="section-title">কনফিগারেশন গাইড</h2>
                    <div class="mb-4">
                        <h5 class="mb-3">ফায়ারবেস ক্লাউড মেসেজিং সেট আপ করা</h5>
                        <ol class="mb-4">
                            <li class="mb-2">ফায়ারবেস কনসোলে যান <a href="https://console.firebase.google.com/" target="_blank" class="text-primary">ফায়ারবেস কনসোল</a></li>
                            <li class="mb-2">আপনার প্রজেক্ট নির্বাচন করুন বা একটি নতুন তৈরি করুন</li>
                            <li class="mb-2">প্রজেক্ট সেটিংস > ক্লাউড মেসেজিং এ নেভিগেট করুন</li>
                            <li class="mb-2">আপনার নোটিফিকেশনের জন্য একটি ডিফল্ট টপিক বেছে নিন</li>
                            <li>google-services.json ডাউনলোড করুন এবং এর কন্টেন্ট উপরে পেস্ট করুন</li>
                        </ol>
                        <div class="alert alert-info">
                            <i class="bi bi-lightbulb me-2"></i>
                            <strong>টিপস:</strong> আপনার সার্ভার কী নিরাপদ রাখুন এবং ক্লায়েন্ট-সাইড কোডে এটি প্রকাশ করবেন না।
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleTheme() {
            document.body.classList.toggle('dark-mode');
            const isDarkMode = document.body.classList.contains('dark-mode');
            localStorage.setItem('darkMode', isDarkMode);
            updateThemeIcon();
        }

        function updateThemeIcon() {
            const themeIcon = document.querySelector('.theme-toggle i');
            if (document.body.classList.contains('dark-mode')) {
                themeIcon.classList.replace('bi-sun-fill', 'bi-moon-fill');
            } else {
                themeIcon.classList.replace('bi-moon-fill', 'bi-sun-fill');
            }
        }

        // Check for saved theme preference
        const savedTheme = localStorage.getItem('darkMode');
        if (savedTheme === 'true') {
            document.body.classList.add('dark-mode');
            updateThemeIcon();
        }
    </script>
</body>
</html>
