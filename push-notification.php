<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

try {
    $conn = connectDB();
    // Get user data
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Get notification history with proper ordering and user info
    $stmt = $conn->prepare("
        SELECT 
            nh.*,
            u.name as sent_by_name
        FROM notification_history nh
        LEFT JOIN users u ON nh.sent_by = u.id
        ORDER BY nh.sent_at DESC
        LIMIT 10
    ");
    $stmt->execute();
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("ডাটাবেস ত্রুটি: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>পুশ নোটিফিকেশন সেন্টার</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/hscbmt/cd@latest/style.css">
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
                        <a class="nav-link active" href="push-notification.php">
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

    <div class="container py-4">
        <!-- Welcome section -->
        <div class="welcome-section">
            <h1 class="h3 mb-2">পুশ নোটিফিকেশন সেন্টার</h1>
            <p class="mb-0">আপনার অ্যাপ ব্যবহারকারীদের কাছে টার্গেটেড নোটিফিকেশন পাঠান।</p>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="notification-card">
                    <h2 class="section-title">নতুন নোটিফিকেশন পাঠান</h2>
                    <form id="notificationForm">
                        <div class="mb-3">
                            <label for="title" class="form-label">শিরোনাম</label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label for="body" class="form-label">বিবরণ</label>
                            <textarea class="form-control" id="body" name="body" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="imageUrl" class="form-label">ছবি (ঐচ্ছিক)</label>
                            <input type="url" class="form-control" id="imageUrl" name="imageUrl">
                        </div>
                        <div class="mb-3">
                            <label for="actionUrl" class="form-label">অ্যাকশন URL (ঐচ্ছিক)</label>
                            <input type="url" class="form-control" id="actionUrl" name="actionUrl">
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-send me-2"></i>নোটিফিকেশন পাঠান
                        </button>
                    </form>
                    <div id="statusMessage" class="mt-3" style="display: none;"></div>
                </div>

                <div class="notification-card">
                    <h3 class="section-title">সাম্প্রতিক নোটিফিকেশন</h3>
                    <div id="notificationHistory">
                        <?php foreach ($notifications as $notification): ?>
                            <div class="history-item compact-card card p-3 mb-3 shadow-sm">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h5 class="card-title fs-6 mb-2"><?php echo htmlspecialchars($notification['title']); ?></h5>
                                            <p class="card-text text-muted small mb-2"><?php echo htmlspecialchars($notification['message']); ?></p>
                                            <div class="mt-2 pt-2 border-top text-end text-muted small">
                                                <i class="bi bi-person-circle me-1"></i>
                                                <?php echo htmlspecialchars($notification['sent_by_name']); ?> •
                                                <i class="bi bi-clock me-1"></i>
                                                <?php echo date('M j, Y H:i', strtotime($notification['sent_at'])); ?>
                                            </div>
                                        </div>
                                        <div class="action-buttons">
                                            <button class="btn btn-sm btn-outline-primary duplicate-btn" 
                                                    data-notification='<?php echo json_encode($notification); ?>'>
                                                <i class="bi bi-clipboard"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger delete-btn" 
                                                    data-id="<?php echo $notification['id']; ?>">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <?php if ($notification['image_url']): ?>
                                        <img src="<?php echo htmlspecialchars($notification['image_url']); ?>" 
                                             alt="Notification Image" 
                                             class="mt-2 rounded preview-image">
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="notification-card">
                    <h3 class="section-title">প্রিভিউ</h3>
                    <div class="mobile-preview">
                        <div class="status-bar">
                            <span>9:41</span>
                            <div>
                                <i class="bi bi-wifi"></i>
                                <i class="bi bi-battery-full ms-2"></i>
                            </div>
                        </div>
                        <div class="mobile-screen">
                            <div class="notification-preview">
                                <div class="d-flex align-items-center">
                                    <img src="assets/images/logo.png" alt="App Icon" class="app-icon">
                                    <div>
                                        <div class="app-name">আপনার অ্যাপ নাম</div>
                                        <div class="notification-time">এখন</div>
                                    </div>
                                </div>
                                <div class="notification-title" id="previewTitle">নোটিফিকেশন শিরোনাম</div>
                                <div class="notification-body" id="previewBody">নোটিফিকেশন বার্তা এখানে প্রদর্শিত হবে</div>
                                <img src="" alt="Notification Image" class="notification-image" id="previewImage">
                            </div>
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

        document.getElementById('title').addEventListener('input', updatePreview);
        document.getElementById('body').addEventListener('input', updatePreview);
        document.getElementById('imageUrl').addEventListener('input', updatePreview);

        function updatePreview() {
            const title = document.getElementById('title').value || 'নোটিফিকেশন শিরোনাম';
            const body = document.getElementById('body').value || 'নোটিফিকেশন বার্তা এখানে প্রদর্শিত হবে';
            const imageUrl = document.getElementById('imageUrl').value;

            document.getElementById('previewTitle').textContent = title;
            document.getElementById('previewBody').textContent = body;

            const previewImage = document.getElementById('previewImage');
            if (imageUrl) {
                previewImage.src = imageUrl;
                previewImage.classList.add('show');
            } else {
                previewImage.classList.remove('show');
            }

            document.querySelector('.notification-preview').classList.add('show');
        }

        document.getElementById('notificationForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const form = this;
            const submitButton = form.querySelector('button[type="submit"]');
            const statusMessage = document.getElementById('statusMessage');
            
            // Disable submit button
            submitButton.disabled = true;
            submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>পাঠানো হচ্ছে...';

            const formData = new FormData(form);
            try {
                const response = await fetch('sendNotification.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();
                
                // Show status message
                statusMessage.style.display = 'block';
                if (result.success) {
                    statusMessage.className = 'alert alert-success mt-3';
                    statusMessage.innerHTML = '<i class="bi bi-check-circle me-2"></i>' + result.message;
                    
                    // Reset form
                    form.reset();
                    
                    // Refresh notification history
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                } else {
                    statusMessage.className = 'alert alert-danger mt-3';
                    statusMessage.innerHTML = '<i class="bi bi-exclamation-triangle me-2"></i>' + result.message;
                }
            } catch (error) {
                statusMessage.style.display = 'block';
                statusMessage.className = 'alert alert-danger mt-3';
                statusMessage.innerHTML = '<i class="bi bi-exclamation-triangle me-2"></i>নোটিফিকেশন পাঠাতে একটি ত্রুটি ঘটেছে।';
            } finally {
                // Re-enable submit button
                submitButton.disabled = false;
                submitButton.innerHTML = '<i class="bi bi-send me-2"></i>নোটিফিকেশন পাঠান';
            }
        });

        // Handle duplicate button clicks
        document.querySelectorAll('.duplicate-btn').forEach(button => {
            button.addEventListener('click', function() {
                const notificationData = JSON.parse(this.dataset.notification);
                document.getElementById('title').value = notificationData.title;
                document.getElementById('body').value = notificationData.message;
                document.getElementById('imageUrl').value = notificationData.image_url || '';
                document.getElementById('actionUrl').value = notificationData.action_url || '';
                updatePreview();
                window.scrollTo({top: 0, behavior: 'smooth'});
            });
        });

        // Handle delete button clicks
        document.querySelectorAll('.delete-btn').forEach(button => {
            button.addEventListener('click', async function() {
                if (confirm('আপনি কি নিশ্চিত যে আপনি এই নোটিফিকেশনটি মুছে ফেলতে চান?')) {
                    const notificationId = this.dataset.id;
                    try {
                        const response = await fetch('deleteNotification.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({ id: notificationId })
                        });
                        const result = await response.json();
                        if (result.success) {
                            this.closest('.history-item').remove();
                        } else {
                            alert('নোটিফিকেশন মুছে ফেলতে একটি ত্রুটি ঘটেছে: ' + result.message);
                        }
                    } catch (error) {
                        alert('নোটিফিকেশন মুছে ফেলতে একটি ত্রুটি ঘটেছে।');
                    }
                }
            });
        });
    </script>
</body>
</html>
