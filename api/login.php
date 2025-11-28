<?php
// restructured/api/login.php
header('Content-Type: application/json; charset=utf-8');
session_start();

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/User.php';

$input = $_POST;
if (empty($input['username']) || empty($input['password'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing username or password']);
    exit;
}

try {
    $db = (new Database())->getConnection();
    $user = new User($db);
    $user->username = $input['username'];
    $user->password = $input['password'];

    if ($user->login()) {
        // populate session
        $_SESSION['user_id'] = $user->id;
        $_SESSION['username'] = $user->username;
        $_SESSION['first_name'] = $user->first_name;
        $_SESSION['last_name'] = $user->last_name;
        $_SESSION['role'] = $user->role;
        $_SESSION['group_name'] = $user->group_name;

        echo json_encode([
            'success' => true,
            'message' => 'Authenticated',
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'role' => $user->role,
                'group_name' => $user->group_name
            ]
        ]);
        exit;
    } else {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Invalid credentials']);
        exit;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error']);
    error_log('Login error: ' . $e->getMessage());
    exit;
}
?>
