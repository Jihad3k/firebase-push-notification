<?php
session_start();
require_once 'config/database.php';

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'অনুগ্রহ করে সকল ক্ষেত্র পূরণ করুন';
    } else {
        try {
            $conn = connectDB();
            $stmt = $conn->prepare("SELECT id, email, password FROM users WHERE email = ? AND password = ?");
            $stmt->execute([$email, $password]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['first_login'] = true; // Add this line
                header("Location: dashboard.php");
                exit();
            } else {
                $error = 'অবৈধ ইমেইল বা পাসওয়ার্ড';
            }
        } catch(PDOException $e) {
            $error = 'ডাটাবেস ত্রুটি: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>লগইন - পুশ নোটিফিকেশন অ্যাডমিন প্যানেল</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="login-page">
    <div class="background-pattern"></div>
    
    

    <div class="page-wrapper">
        <div class="login-container" data-aos="fade-up" data-aos-duration="1000">
            <div class="login-header">
                <div class="logo-container">
                    <img src="https://cdn.jsdelivr.net/gh/hscbmt/testpdf@main/ic_profile.png" alt="Logo" class="logo">
                </div>
                <h1 class="form-title">ফিরে আসার জন্য ধন্যবাদ</h1>
                <p class="form-subtitle">অনগ্রহ করে আপনার লগইন তথ্য প্রবেশ করুন</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger d-flex align-items-center" role="alert">
                    <i class="bi bi-exclamation-circle-fill me-2"></i>
                    <div><?php echo htmlspecialchars($error); ?></div>
                </div>
            <?php endif; ?>

            <form method="POST" action="" class="login-form">
                <div class="form-floating mb-3">
                    <input type="email" class="form-control" id="email" name="email" placeholder="name@example.com" required>
                    <label for="email">ইমেইল ঠিকানা</label>
                </div>
                <div class="form-floating mb-4">
                    <input type="password" class="form-control" id="password" name="password" placeholder="পাসওয়ার্ড" required>
                    <label for="password">পাসওয়ার্ড</label>
                </div>
                <button type="submit" class="btn btn-primary w-100 btn-login">
                    <span>সাইন ইন</span>
                    <i class="bi bi-arrow-right-circle ms-2"></i>
                </button>
            </form>
        </div>
    </div>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init();
        
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
