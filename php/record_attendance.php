<?php
// record_attendance.php - Record employee check-in/check-out
header('Content-Type: application/json');

include 'config.php';
session_start();

// Check authentication
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$userId = isset($_POST['userId']) ? intval($_POST['userId']) : $_SESSION['user_id'];
$timestamp = isset($_POST['timestamp']) ? $_POST['timestamp'] : date('Y-m-d H:i:s');
$action = isset($_POST['action']) ? $_POST['action'] : 'check_in';

// Create attendance record
$query = "INSERT INTO attendance (employee_id, check_in, check_out, date, status) 
          VALUES (?, ?, NULL, CURDATE(), 'present')
          ON DUPLICATE KEY UPDATE check_in = ?, status = 'present'";

$stmt = $conn->prepare($query);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error']);
    exit;
}

$stmt->bind_param('iss', $userId, $timestamp, $timestamp);

if ($stmt->execute()) {
    // Log activity
    $activityQuery = "INSERT INTO activity_log (user_id, activity_type, description, timestamp) 
                      VALUES (?, ?, ?, NOW())";
    $activityStmt = $conn->prepare($activityQuery);
    if ($activityStmt) {
        $activityType = 'Check-In';
        $description = 'Marked attendance at ' . date('H:i:s');
        $activityStmt->bind_param('iss', $userId, $activityType, $description);
        $activityStmt->execute();
        $activityStmt->close();
    }
    
    echo json_encode(['success' => true, 'message' => 'Attendance recorded']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error recording attendance']);
}

$stmt->close();
$conn->close();
?>
