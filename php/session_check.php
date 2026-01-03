<?php
// session_check.php - Check if user is logged in
header('Content-Type: application/json');

session_start();

<<<<<<< HEAD
// Handle logout action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'logout') {
    session_destroy();
    echo json_encode(['success' => true, 'message' => 'Logged out successfully']);
    exit;
}

=======
>>>>>>> ea79794f4f442ffbcda7d39c1eadb07a0fc9c046
if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
    echo json_encode([
        'loggedIn' => true,
        'userId' => $_SESSION['user_id'],
        'loginId' => $_SESSION['login_id'] ?? null,
        'email' => $_SESSION['email'] ?? null,
        'role' => $_SESSION['role'] ?? null
    ]);
} else {
    echo json_encode([
        'loggedIn' => false
    ]);
}
?>
