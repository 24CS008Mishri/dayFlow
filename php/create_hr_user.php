<?php
// create_hr_user.php
include 'config.php';

// Create HR user
$hrLoginId = 'hr';
$hrEmail = 'hr@dayflow.com';
$hrPassword = 'hr123';
$hrRole = 'hr';
$passwordHash = password_hash($hrPassword, PASSWORD_BCRYPT);

$query = 'INSERT INTO users (login_id, email, password_hash, role, first_login) VALUES (?, ?, ?, ?, 1)';
$stmt = $conn->prepare($query);
$stmt->bind_param('ssss', $hrLoginId, $hrEmail, $passwordHash, $hrRole);

if ($stmt->execute()) {
    echo "✓ HR user created successfully!\n";
    echo "  Login ID: " . $hrLoginId . "\n";
    echo "  Email: " . $hrEmail . "\n";
    echo "  Password: " . $hrPassword . "\n";
} else {
    echo "Error: " . $stmt->error . "\n";
}
$stmt->close();
$conn->close();
?>
