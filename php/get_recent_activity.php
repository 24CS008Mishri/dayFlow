<?php
// get_recent_activity.php - Get recent activity for user
header('Content-Type: application/json');

include 'config.php';
session_start();

// Check authentication
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$userId = isset($_GET['userId']) ? intval($_GET['userId']) : $_SESSION['user_id'];

// Get recent activities (last 10)
$query = "SELECT activity_type, description, timestamp FROM activity_log 
          WHERE user_id = ? 
          ORDER BY timestamp DESC 
          LIMIT 10";

$stmt = $conn->prepare($query);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error']);
    exit;
}

$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();

$activities = [];
while ($row = $result->fetch_assoc()) {
    $activities[] = $row;
}

$stmt->close();
$conn->close();

echo json_encode([
    'success' => true,
    'activities' => $activities
]);
?>
