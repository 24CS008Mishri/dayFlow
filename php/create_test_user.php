<?php
// create_test_user.php - Create test users

include 'config.php';

// 1. Create sample employee first
$empName = 'John Developer';
$empEmail = 'john.dev@company.com';
$empMobile = '+91-9876543210';
$empPosition = 'Senior Developer';
$empCompany = 'TechFlow Solutions';
$empDept = 'Engineering';
$empManager = 'Admin User';
$empDOB = '1990-05-15';
$empAddress = '123 Tech Street, Silicon Valley';
$empNationality = 'Indian';
$empGender = 'Male';

$empQuery = "INSERT INTO employees (name, job_position, email, mobile, company, department, manager, date_of_birth, residing_address, nationality, gender, marital_status, date_of_joining) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

$empStmt = $conn->prepare($empQuery);
if (!$empStmt) {
    die('Prepare error: ' . $conn->error);
}

$empMaritalStatus = 'Single';
$empStmt->bind_param('ssssssssssss', $empName, $empPosition, $empEmail, $empMobile, $empCompany, $empDept, $empManager, $empDOB, $empAddress, $empNationality, $empGender, $empMaritalStatus);

if ($empStmt->execute()) {
    $employeeId = $conn->insert_id;
    echo "✓ Employee created: " . $empName . " (ID: " . $employeeId . ")\n";
} else {
    echo "Error creating employee: " . $empStmt->error . "\n";
    $employeeId = null;
}
$empStmt->close();

// 2. Create admin user
$adminLoginId = 'admin';
$adminEmail = 'admin@dayflow.com';
$adminPassword = 'admin123';
$adminRole = 'admin';
$passwordHash = password_hash($adminPassword, PASSWORD_BCRYPT);

$adminQuery = "INSERT INTO users (login_id, email, password_hash, role, first_login) 
               VALUES (?, ?, ?, ?, 1)";

$adminStmt = $conn->prepare($adminQuery);
if (!$adminStmt) {
    die('Prepare error: ' . $conn->error);
}

$adminStmt->bind_param('ssss', $adminLoginId, $adminEmail, $passwordHash, $adminRole);

if ($adminStmt->execute()) {
    echo "\n✓ Admin user created successfully!\n";
    echo "  Login ID: " . $adminLoginId . "\n";
    echo "  Email: " . $adminEmail . "\n";
    echo "  Password: " . $adminPassword . "\n";
} else {
    echo "Error creating admin: " . $adminStmt->error . "\n";
}
$adminStmt->close();

// 3. Create employee user
if ($employeeId) {
    $empLoginId = 'john.dev';
    $empPassword = 'emp123';
    $empPasswordHash = password_hash($empPassword, PASSWORD_BCRYPT);
    $empRole = 'employee';
    
    $userQuery = "INSERT INTO users (login_id, email, password_hash, role, first_login, employee_id) 
                  VALUES (?, ?, ?, ?, 1, ?)";
    
    $userStmt = $conn->prepare($userQuery);
    if (!$userStmt) {
        die('Prepare error: ' . $conn->error);
    }
    
    $userStmt->bind_param('ssssi', $empLoginId, $empEmail, $empPasswordHash, $empRole, $employeeId);
    
    if ($userStmt->execute()) {
        echo "\n✓ Employee user created successfully!\n";
        echo "  Login ID: " . $empLoginId . "\n";
        echo "  Email: " . $empEmail . "\n";
        echo "  Password: " . $empPassword . "\n";
    } else {
        echo "Error creating employee user: " . $userStmt->error . "\n";
    }
    $userStmt->close();
}

echo "\n=====================================\n";
echo "DATABASE SETUP COMPLETE!\n";
echo "=====================================\n";
echo "\nYou can now login with these credentials:\n";
echo "\nADMIN USER:\n";
echo "  Login ID: admin\n";
echo "  Password: admin123\n";
echo "\nEMPLOYEE USER:\n";
echo "  Login ID: john.dev\n";
echo "  Password: emp123\n";
echo "\n=====================================\n";

$conn->close();
?>
