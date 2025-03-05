<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Include database and FCM configuration
require_once 'config/database.php';
require 'get-access-token.php';

try {
    // Get POST data
    $title = $_POST['title'] ?? '';
    $body = $_POST['body'] ?? '';
    $imageUrl = $_POST['imageUrl'] ?? '';
    $actionUrl = $_POST['actionUrl'] ?? '';

    // First, save to database
    $conn = connectDB();
    $stmt = $conn->prepare("
        INSERT INTO notification_history 
        (title, message, image_url, action_url, sent_by, status) 
        VALUES (?, ?, ?, ?, ?, 'success')
    ");
    
    $dbResult = $stmt->execute([
        $title,
        $body,
        $imageUrl,
        $actionUrl,
        $_SESSION['user_id']
    ]);

    if (!$dbResult) {
        throw new Exception('Failed to save notification to database');
    }

    // Now proceed with FCM notification
    // Get configuration from database
    $stmt = $conn->prepare("SELECT * FROM notification_configs ORDER BY id DESC LIMIT 1");
    $stmt->execute();
    $config = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$config) {
        throw new Exception('Notification configuration not found');
    }

    // Update google-services.json
    $serviceAccountKeyFile = 'google-services.json';
    if (!empty($config['google_services_json'])) {
        file_put_contents($serviceAccountKeyFile, $config['google_services_json']);
    }

    // Get FCM access token
    $accessToken = getAccessToken($serviceAccountKeyFile);
    if (!$accessToken) {
        throw new Exception('Failed to obtain access token');
    }

    // Prepare FCM request
    $url = "https://fcm.googleapis.com/v1/projects/" . $config['firebase_project_id'] . "/messages:send";
    
    $datamsg = array(
        'title'   => $title,
        'body'    => $body,
        'imageUrl'=> $imageUrl,
        'actionUrl'=> $actionUrl
    );

    $arrayToSend = array(
        'topic' => $config['firebase_topic'],
        'data' => $datamsg
    );

    $json = json_encode(['message' => $arrayToSend]);

    // Send FCM request
    $headers = array(
        'Content-Type: application/json',
        'Authorization: Bearer ' . $accessToken
    );

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($response === FALSE || $httpCode !== 200) {
        throw new Exception('Error sending FCM notification. HTTP Code: ' . $httpCode);
    }

    echo json_encode([
        'success' => true,
        'message' => 'Notification sent and saved successfully!'
    ]);

} catch(Exception $e) {
    // Update notification status to failed if database insert was successful
    if (isset($dbResult) && $dbResult) {
        $stmt = $conn->prepare("UPDATE notification_history SET status = 'failed' WHERE sent_by = ? ORDER BY id DESC LIMIT 1");
        $stmt->execute([$_SESSION['user_id']]);
    }

    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
