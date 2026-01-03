<?php
// submit_leave_request.php - Submit a leave request
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

// Get form data
$employeeId = isset($_POST['employeeId']) ? intval($_POST['employeeId']) : 0;
$leaveType = isset($_POST['leaveType']) ? $_POST['leaveType'] : 'paid';
$startDate = isset($_POST['startDate']) ? $_POST['startDate'] : '';
$endDate = isset($_POST['endDate']) ? $_POST['endDate'] : '';
$reason = isset($_POST['reason']) ? $_POST['reason'] : '';

// Validate input
if (!$employeeId || !$startDate || !$endDate) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

// Verify date range
$start = strtotime($startDate);
$end = strtotime($endDate);
if ($start > $end) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'End date must be after start date']);
    exit;
}

// Submit leave request
$query = "INSERT INTO leave_requests (employee_id, leave_type, start_date, end_date, reason, status) 
          VALUES (?, ?, ?, ?, ?, 'pending')";

$stmt = $conn->prepare($query);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error']);
    exit;
}

$stmt->bind_param('issss', $employeeId, $leaveType, $startDate, $endDate, $reason);

if ($stmt->execute()) {
    // Log activity
    $activityQuery = "INSERT INTO activity_log (user_id, activity_type, description) 
                      VALUES (?, ?, ?)";
    $activityStmt = $conn->prepare($activityQuery);
    if ($activityStmt) {
        $activityType = 'Leave Request Submitted';
        $description = ucfirst($leaveType) . ' leave request from ' . $startDate . ' to ' . $endDate;
        $activityStmt->bind_param('iss', $_SESSION['user_id'], $activityType, $description);
        $activityStmt->execute();
        $activityStmt->close();
    }
    
    echo json_encode([
        'success' => true, 
        'message' => 'Leave request submitted successfully',
        'leaveId' => $conn->insert_id
    ]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error submitting leave request']);
}

$stmt->close();
$conn->close();
?>
