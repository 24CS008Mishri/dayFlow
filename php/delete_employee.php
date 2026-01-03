<?php
// delete_employee.php - Delete an employee
header('Content-Type: application/json');

include 'config.php';
session_start();

// Check if user is logged in and is admin or hr
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'hr') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Only admin or HR can delete employees']);
    exit;
}

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get employee ID
$employeeId = isset($_POST['id']) ? intval($_POST['id']) : 0;

if ($employeeId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid employee ID']);
    exit;
}

// Delete from users table first (cascade won't work if there's a foreign key constraint)
$deleteUserQuery = "DELETE FROM users WHERE employee_id = ?";
$deleteUserStmt = $conn->prepare($deleteUserQuery);
if ($deleteUserStmt) {
    $deleteUserStmt->bind_param('i', $employeeId);
    $deleteUserStmt->execute();
    $deleteUserStmt->close();
}

// Delete salary info
$deleteSalaryQuery = "DELETE FROM salary_info WHERE employee_id = ?";
$deleteSalaryStmt = $conn->prepare($deleteSalaryQuery);
if ($deleteSalaryStmt) {
    $deleteSalaryStmt->bind_param('i', $employeeId);
    $deleteSalaryStmt->execute();
    $deleteSalaryStmt->close();
}

// Delete security info
$deleteSecurityQuery = "DELETE FROM security_info WHERE employee_id = ?";
$deleteSecurityStmt = $conn->prepare($deleteSecurityQuery);
if ($deleteSecurityStmt) {
    $deleteSecurityStmt->bind_param('i', $employeeId);
    $deleteSecurityStmt->execute();
    $deleteSecurityStmt->close();
}

// Delete employee
$deleteQuery = "DELETE FROM employees WHERE id = ?";
$deleteStmt = $conn->prepare($deleteQuery);

if (!$deleteStmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
    exit;
}

$deleteStmt->bind_param('i', $employeeId);

if ($deleteStmt->execute()) {
    if ($deleteStmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Employee deleted successfully']);
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Employee not found']);
    }
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error deleting employee: ' . $deleteStmt->error]);
}

$deleteStmt->close();
$conn->close();
?>
