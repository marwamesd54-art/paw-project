<?php
// restructured/api/get_students.php
header('Content-Type: application/json; charset=utf-8');
session_start();

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/User.php';

if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$role = $_SESSION['role'] ?? '';
try {
    $db = (new Database())->getConnection();
    $user = new User($db);

    // Professor: return students in their group
    if ($role === 'professor') {
        $group = $_SESSION['group_name'] ?? '';
        $stmt = $user->getStudentsByGroup($group);
        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'students' => $students]);
        exit;
    }

    // Admin: optional ?group= parameter or all students
    if ($role === 'admin') {
        $group = $_GET['group'] ?? null;
        if ($group) {
            $stmt = $user->getStudentsByGroup($group);
            $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'students' => $students]);
            exit;
        } else {
            $stmt = $user->getAllUsers();
            $all = $stmt->fetchAll(PDO::FETCH_ASSOC);
            // filter to students only
            $students = array_values(array_filter($all, function($u){ return ($u['role'] ?? '') === 'student'; }));
            echo json_encode(['success' => true, 'students' => $students]);
            exit;
        }
    }

    // Students cannot list other students
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Forbidden']);
    exit;

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error']);
    error_log('get_students error: ' . $e->getMessage());
    exit;
}

?>
