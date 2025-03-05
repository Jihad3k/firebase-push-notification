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
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if (empty($name) || empty($email)) {
            $error_message = 'নাম এবং ইমেইল প্রয়োজন';
        } else {
            // Update basic info
            $stmt = $conn->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
            $stmt->execute([$name, $email, $_SESSION['user_id']]);

            // Update password if provided
            if (!empty($current_password) && !empty($new_password)) {
                if ($new_password !== $confirm_password) {
                    $error_message = 'নতুন পাসওয়ার্ড মিলছে না';
                } elseif ($current_password !== $user['password']) {
                    $error_message = 'বর্তমান পাসওয়ার্ড সঠিক নয়';
                } else {
                    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                    $stmt->execute([$new_password, $_SESSION['user_id']]);
                }
            }

            if (empty($error_message)) {
                $success_message = 'প্রোফাইল সফলভাবে আপডেট করা হয়েছে';
                // Refresh user data
                $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
                $stmt->execute([$_SESSION['user_id']]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
            }
        }
    }
} catch(PDOException $e) {
    $error_message = "ত্রুটি: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>প্রোফাইল - পুশ নোটিফিকেশন অ্যাডমিন প্যানেল</title>
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
            <div class="d-flex align-items-center gap-3">
                <button class="theme-toggle" onclick="toggleTheme()">
                    <i class="bi bi-sun-fill"></i>
                </button>
                <div class="dropdown">
                    <button class="btn btn-link dropdown-toggle text-decoration-none" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <?php echo htmlspecialchars($user['name']); ?>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <li><a class="dropdown-item active" href="profile.php"><i class="bi bi-person me-2"></i>প্রোফাইল</a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><a class="dropdown-item" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>লগআউট</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <?php if ($success_message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>
        <?php if ($error_message): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <div class="welcome-section">
            <h1 class="h3 mb-2">প্রোফাইল সেটিংস</h1>
            <p class="mb-0">আপনার ব্যক্তিগত তথ্য এবং পাসওয়ার্ড আপডেট করুন।</p>
        </div>

        <div class="profile-card mt-4">
            <form method="POST" action="">
                <div class="mb-3">
                    <label for="name" class="form-label">নাম</label>
                    <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">ইমেইল</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>
                <hr class="my-4">
                <h3 class="section-title h5">পাসওয়ার্ড পরিবর্তন করুন</h3>
                <div class="mb-3">
                    <label for="current_password" class="form-label">বর্তমান পাসওয়ার্ড</label>
                    <input type="password" class="form-control" id="current_password" name="current_password">
                </div>
                <div class="mb-3">
                    <label for="new_password" class="form-label">নতুন পাসওয়ার্ড</label>
                    <input type="password" class="form-control" id="new_password" name="new_password">
                </div>
                <div class="mb-3">
                    <label for="confirm_password" class="form-label">নতুন পাসওয়ার্ড নিশ্চিত করুন</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check2 me-2"></i>পরিবর্তন সংরক্ষণ করুন
                </button>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        // Initialize animations
        AOS.init({
            duration: 800,
            once: true
        });
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
