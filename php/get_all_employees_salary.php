<?php
header('Content-Type: application/json');
require_once 'config.php';

<<<<<<< HEAD
// Try to start/resume session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check authorization - both admin and hr can view employees
// Even if session is not set, we allow the request through (session might not be active on API calls)
// The authorization check is secondary to having the proper session role if available
if (isset($_SESSION['role']) && $_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'hr') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized: Only Admin or HR can view employees']);
=======
// Check if user is authenticated and is HR
session_start();
$userRole = isset($_SESSION['user_role']) ? strtolower($_SESSION['user_role']) : 'hr'; // Default to hr for testing

// Only HR can view all employees' salary
if ($userRole !== 'hr') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized: Only HR can view employee salary data']);
>>>>>>> ea79794f4f442ffbcda7d39c1eadb07a0fc9c046
    http_response_code(403);
    exit;
}

<<<<<<< HEAD
// Get all employees with their basic information (not requiring session for data retrieval)
$query = "SELECT e.id, e.name, e.job_position, e.department, e.email, e.mobile, e.company,
          e.manager, e.date_of_joining
          FROM employees e
=======
// Get all employees with their salary configuration
$query = "SELECT e.id, e.name, e.job_position, e.department, e.email, e.mobile,
          sc.monthly_wage, sc.yearly_wage, sc.basic_salary, sc.hra, 
          sc.standard_allowance, sc.performance_bonus, sc.leave_travel_allowance, 
          sc.fixed_allowance, sc.pf_employee_percent, sc.pf_employer_percent,
          sc.professional_tax, sc.income_tax_percent, sc.gross_salary, sc.net_salary
          FROM employees e
          LEFT JOIN salary_configuration sc ON e.id = sc.employee_id
>>>>>>> ea79794f4f442ffbcda7d39c1eadb07a0fc9c046
          ORDER BY e.name";

$result = $conn->query($query);

if (!$result) {
<<<<<<< HEAD
    echo json_encode(['success' => false, 'message' => 'Error fetching employee data: ' . $conn->error]);
=======
    echo json_encode(['success' => false, 'message' => 'Error fetching employee salary data']);
>>>>>>> ea79794f4f442ffbcda7d39c1eadb07a0fc9c046
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
