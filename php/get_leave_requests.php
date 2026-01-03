<?php
// get_leave_requests.php - Get leave requests (for HR/Admin approval)
header('Content-Type: application/json');

include 'config.php';
session_start();

// Check authentication
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'hr')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Get status filter (optional)
$status = isset($_GET['status']) ? $_GET['status'] : '';

// Build query
$query = "SELECT lr.id, lr.employee_id, lr.leave_type, lr.start_date, lr.end_date, 
          lr.reason, lr.status, lr.approval_comments, e.name, e.email, e.department
          FROM leave_requests lr
          JOIN employees e ON lr.employee_id = e.id";

if (!empty($status)) {
    $query .= " WHERE lr.status = ?";
}

$query .= " ORDER BY lr.created_at DESC";

$stmt = $conn->prepare($query);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error']);
    exit;
}

if (!empty($status)) {
    $stmt->bind_param('s', $status);
}

$stmt->execute();
$result = $stmt->get_result();

$requests = [];
while ($row = $result->fetch_assoc()) {
    $requests[] = $row;
}

$stmt->close();
$conn->close();

echo json_encode([
    'success' => true,
    'requests' => $requests
]);
?>
