<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$success_message = '';
$error_message = '';

try {
    $conn = connectDB();
    
    // Get current user data
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Handle form submission for new admin
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['action'])) {
            if ($_POST['action'] === 'create') {
                $name = $_POST['name'] ?? '';
                $email = $_POST['email'] ?? '';
                $password = $_POST['password'] ?? '';
                
                if (empty($name) || empty($email) || empty($password)) {
                    $error_message = 'সকল ক্ষেত্র প্রয়োজন';
                } else {
                    // Check if email already exists
                    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
                    $stmt->execute([$email]);
                    if ($stmt->fetch()) {
                        $error_message = 'ইমেইল ইতিমধ্যে বিদ্যমান';
                    } else {
                        $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
                        $stmt->execute([$name, $email, $password]);
                        $success_message = 'অ্যাডমিন ব্যবহারকারী সফলভাবে তৈরি করা হয়েছে';
                    }
                }
            } elseif ($_POST['action'] === 'delete' && isset($_POST['user_id'])) {
                if ($_POST['user_id'] == $_SESSION['user_id']) {
                    $error_message = 'আপনি নিজের অ্যাকাউন্ট মুছতে পারবেন না';
                } else {
                    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
                    $stmt->execute([$_POST['user_id']]);
                    $success_message = 'অ্যাডমিন ব্যবহারকারী সফলভাবে মুছে ফেলা হয়েছে';
                }
            }
        }
    }
    
    // Get all admin users
    $stmt = $conn->prepare("SELECT * FROM users ORDER BY created_at DESC");
    $stmt->execute();
    $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    $error_message = "ডাটাবেস ত্রুটি: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>অ্যাডমিন ব্যবস্থাপনা - অ্যাডমিন প্যানেল</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
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
                        <a class="nav-link" href="notification-config.php">
                            <i class="bi bi-gear me-1"></i> কনফিগারেশন
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="admin-management.php">
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
            <h1 class="h3 mb-2">অ্যাডমিন ব্যবস্থাপনা</h1>
            <p class="mb-0">পুশ নোটিফিকেশন সিস্টেমে অ্যাডমিনিস্ট্রেটর অ্যাকাউন্ট পরিচালনা করুন এবং অ্যাক্সেস নিয়ন্ত্রণ করুন।</p>
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

        <div class="notification-card">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="section-title mb-0">অ্যাডমিন ব্যবহারকারী</h2>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAdminModal">
                    <i class="bi bi-plus-lg me-2"></i>নতুন অ্যাডমিন যোগ করুন
                </button>
            </div>
            
            <div class="admin-list">
                <?php foreach ($admins as $admin): ?>
                    <div class="admin-item">
                        <div class="admin-info">
                            <div class="admin-name"><?php echo htmlspecialchars($admin['name']); ?></div>
                            <div class="admin-email"><?php echo htmlspecialchars($admin['email']); ?></div>
                        </div>
                        <div class="admin-actions">
                            <?php if ($admin['id'] != $_SESSION['user_id']): ?>
                                <form method="POST" action="" class="d-inline" onsubmit="return confirm('আপনি কি নিশ্চিত যে আপনি এই অ্যাডমিন ব্যবহারকারী মুছতে চান?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="user_id" value="<?php echo $admin['id']; ?>">
                                    <button type="submit" class="btn btn-danger btn-icon">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Add Admin Modal -->
    <div class="modal fade" id="addAdminModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">নতুন অ্যাডমিন যোগ করুন</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="">
                    <input type="hidden" name="action" value="create">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="name" class="form-label">নাম</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">ইমেইল</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">পাসওয়ার্ড</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">বাতিল</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-person-plus me-2"></i>অ্যাডমিন যোগ করুন
                        </button>
                    </div>
                </form>
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
