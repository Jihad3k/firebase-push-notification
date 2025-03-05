<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$notificationId = $data['id'] ?? null;

if (!$notificationId) {
    echo json_encode(['success' => false, 'message' => 'Invalid notification ID']);
    exit();
}

try {
    $conn = connectDB();
    $stmt = $conn->prepare("DELETE FROM notification_history WHERE id = ?");
    $result = $stmt->execute([$notificationId]);
    
    echo json_encode([
        'success' => $result,
        'message' => $result ? 'Notification deleted successfully' : 'Failed to delete notification'
    ]);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
