<?php
// reject_leave_request.php - Reject a leave request
header('Content-Type: application/json');

include 'config.php';
session_start();

// Check authentication - only admin/hr can reject
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'hr')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$leaveId = isset($_POST['leaveId']) ? intval($_POST['leaveId']) : 0;
$comments = isset($_POST['comments']) ? $_POST['comments'] : '';

if (!$leaveId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Leave ID required']);
    exit;
}

// Update leave request status
$query = "UPDATE leave_requests 
          SET status = 'rejected', 
              approved_by = ?, 
              approval_comments = ? 
          WHERE id = ?";

$stmt = $conn->prepare($query);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error']);
    exit;
}

$stmt->bind_param('isi', $_SESSION['user_id'], $comments, $leaveId);

if ($stmt->execute()) {
    // Get leave details for activity log
    $selectQuery = "SELECT employee_id, start_date, end_date FROM leave_requests WHERE id = ?";
    $selectStmt = $conn->prepare($selectQuery);
    if ($selectStmt) {
        $selectStmt->bind_param('i', $leaveId);
        $selectStmt->execute();
        $result = $selectStmt->get_result();
        $row = $result->fetch_assoc();
        
        // Log activity
        $activityQuery = "INSERT INTO activity_log (user_id, activity_type, description) 
                          VALUES (?, ?, ?)";
        $activityStmt = $conn->prepare($activityQuery);
        if ($activityStmt) {
            $activityType = 'Leave Request Rejected';
            $description = 'Rejected leave request from ' . $row['start_date'] . ' to ' . $row['end_date'];
            $activityStmt->bind_param('iss', $_SESSION['user_id'], $activityType, $description);
            $activityStmt->execute();
            $activityStmt->close();
        }
        
        $selectStmt->close();
    }
    
    echo json_encode(['success' => true, 'message' => 'Leave request rejected']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error rejecting leave request']);
}

$stmt->close();
$conn->close();
?>
