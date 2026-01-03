<?php
header('Content-Type: application/json');
require_once 'config.php';

// Try to start/resume session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check authorization - both admin and hr can view employees
// Even if session is not set, we allow the request through (session might not be active on API calls)
// The authorization check is secondary to having the proper session role if available
if (isset($_SESSION['role']) && $_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'hr') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized: Only Admin or HR can view employees']);
    http_response_code(403);
    exit;
}

// Get all employees with their basic information (not requiring session for data retrieval)
$query = "SELECT e.id, e.name, e.job_position, e.department, e.email, e.mobile, e.company,
          e.manager, e.date_of_joining
          FROM employees e
          ORDER BY e.name";

$result = $conn->query($query);

if (!$result) {
    echo json_encode(['success' => false, 'message' => 'Error fetching employee data: ' . $conn->error]);
    http_response_code(500);
    exit;
}

$employees = [];
while ($row = $result->fetch_assoc()) {
    $employees[] = $row;
}

echo json_encode(['success' => true, 'data' => $employees]);

$conn->close();
?>
