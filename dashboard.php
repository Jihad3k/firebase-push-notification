<?php
session_start();
require_once 'config/database.php';


if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

try {
    $conn = connectDB();
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ড্যাশবোর্ড - পুশ নোটিফিকেশন অ্যাডমিন প্যানেল</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/hscbmt/cdn@latest/style.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
    <?php if (isset($_SESSION['first_login'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>
            সফলভাবে লগইন করা হয়েছে!
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
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
                        <a class="nav-link active" href="dashboard.php">
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

    <?php
    $modalContent = <<<'HTML'
    <div class="modal fade" id="loginAlert" tabindex="-1" aria-labelledby="loginAlertLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="loginAlertLabel">স্বাগতম!</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>আপনি সফলভাবে Push Notification Admin Panel-এ লগইন করেছেন।</p>
                    <p>সাহায্যের প্রয়োজন? আপনার সিস্টেম অ্যাডমিনিস্ট্রেটরের সাথে যোগাযোগ করুন।</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">বন্ধ করুন</button>
                    <a href="https://m.me/Jihad3k" class="btn btn-primary">অ্যাডমিনের সাথে যোগাযোগ করুন</a>
                </div>
            </div>
        </div>
    </div>
HTML;
    try {
        $conn = connectDB();

        // Calculate current hash
        $currentHash = hash_hmac('sha256', $modalContent, MODAL_HASH_SALT);

        // Retrieve the stored hash (from pass_hash column)
        $stmt = $conn->prepare("SELECT pass_hash FROM chash ORDER BY id DESC LIMIT 1");
        $stmt->execute();
        $hashRecord = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$hashRecord) {
            // First time - insert the current hash into pass_hash column
            $stmt = $conn->prepare("INSERT INTO chash (pass_chash, pass_hash) VALUES (?, ?)");
            $stmt->execute([$currentHash, $currentHash]);
            $originalHash = $currentHash;
        } else {
            // Use stored hash from pass_hash column
            $originalHash = $hashRecord['pass_hash'];

            // If current hash is different, update the pass_chash column
            if ($currentHash !== $originalHash) {
                $stmt = $conn->prepare("UPDATE chash SET pass_chash = ? WHERE pass_hash = ?");
                $stmt->execute([$currentHash, $originalHash]);
            }
        }

        // If the hashes don't match, show the alert
        if ($currentHash !== $originalHash) {
            echo '<div class="alert alert-danger">এডমিনের পারমিশন ছাড়া এই ক্রেডিট এডিট করার চেষ্টা করবেন না</div>';
            exit;
        }
    } catch (PDOException $e) {
        echo '<div class="alert alert-danger">Database error: ' . $e->getMessage() . '</div>';
        exit;
    }

    ?>

    <!-- Login Alert Modal -->
    <?= $modalContent ?>

    <div class="container py-4">
        <!-- Add welcome section -->
        <div class="welcome-section">
            <h1 class="h3 mb-2">স্বাগতম, <?php echo htmlspecialchars($user['name']); ?>! 👋</h1>
            <p class="mb-0">আপনার পুশ নোটিফিকেশন সিস্টেমের আজকের আপডেট এখানে দেখুন।</p>
        </div>

        <!-- Add statistics row -->
        <!-- <div class="stats-card">
            <div class="stats-icon gradient-1">
                <i class="bi bi-send"></i>
            </div>
            <div class="stats-number">2,345</div>
            <div class="stats-label">Total Notifications</div>
        </div>
        <div class="stats-card">
            <div class="stats-icon gradient-2">
                <i class="bi bi-people"></i>
            </div>
            <div class="stats-number">1,234</div>
            <div class="stats-label">Active Users</div>
        </div>
        <div class="stats-card">
            <div class="stats-icon gradient-3">
                <i class="bi bi-check-circle"></i>
            </div>
            <div class="stats-number">98.2%</div>
            <div class="stats-label">Delivery Rate</div>
        </div>
        <div class="stats-card">
            <div class="stats-icon gradient-4">
                <i class="bi bi-graph-up"></i>
            </div>
            <div class="stats-number">45.2%</div>
            <div class="stats-label">Engagement Rate</div>
        </div> -->

        <!-- Update section title -->
        <div class="d-flex align-items-center justify-content-center mb-4">
            <hr class="flex-grow-1 me-3">
            <h2 class="page-title m-0">দ্রুত কার্যক্রম</h2>
            <hr class="flex-grow-1 ms-3">
        </div>

        <!-- Add quick actions row -->
        <div class="row g-4">
            <div class="col-md-4">
                <a href="push-notification.php" class="text-decoration-none">
                    <div class="action-card">
                        <div class="action-icon-wrapper gradient-primary">
                            <i class="bi bi-bell-fill"></i>
                        </div>
                        <h3 class="action-title">পুশ নোটিফিকেশন</h3>
                        <p class="action-text">কাস্টমাইজযোগ্য কন্টেন্ট এবং অ্যাকশন সহ ব্যবহারকারীদের পুশ নোটিফিকেশন পাঠান।</p>
                        <div class="action-footer">
                            <span class="action-link">নোটিফিকেশন পাঠান <i class="bi bi-arrow-right ms-1"></i></span>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="notification-config.php" class="text-decoration-none">
                    <div class="action-card">
                        <div class="action-icon-wrapper gradient-info">
                            <i class="bi bi-gear-wide-connected"></i>
                        </div>
                        <h3 class="action-title">নোটিফিকেশন কনফিগারেশন</h3>
                        <p class="action-text">আপনার নোটিফিকেশন সেটিংস, Firebase ক্রেডেনশিয়াল এবং টপিক কনফিগার করুন।</p>
                        <div class="action-footer">
                            <span class="action-link">সেটিংস পরিচালনা করুন <i class="bi bi-arrow-right ms-1"></i></span>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="admin-management.php" class="text-decoration-none">
                    <div class="action-card">
                        <div class="action-icon-wrapper gradient-warning">
                            <i class="bi bi-people-fill"></i>
                        </div>
                        <h3 class="action-title">অ্যাডমিন ব্যবস্থাপনা</h3>
                        <p class="action-text">নোটিফিকেশন প্যানেলের জন্য অ্যাডমিন ব্যবহারকারী, ভূমিকা এবং অনুমতি ব্যবস্থাপনা করুন।</p>
                        <div class="action-footer">
                            <span class="action-link">অ্যাডমিন পরিচালনা করুন <i class="bi bi-arrow-right ms-1"></i></span>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Show login alert modal on page load
        document.addEventListener('DOMContentLoaded', function() {
            const loginAlert = new bootstrap.Modal(document.getElementById('loginAlert'));
            <?php if (isset($_SESSION['first_login'])): ?>
                loginAlert.show();
                <?php unset($_SESSION['first_login']); ?>
            <?php endif; ?>
        });
    </script>
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