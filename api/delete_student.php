<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin','professor','professeur'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true) ?: $_POST;

$student_id = isset($data['student_id']) ? intval($data['student_id']) : 0;

if (!$student_id) {
    echo json_encode(['success' => false, 'message' => 'Missing student_id']);
    exit;
}

try {
    $db = (new Database())->getConnection();

    // Delete enrollments
    $delEn = $db->prepare("DELETE FROM enrollments WHERE student_id = :sid");
    $delEn->execute([':sid' => $student_id]);

    // Optionally delete attendance records if desired
    $delAtt = $db->prepare("DELETE FROM attendance_records WHERE student_id = :sid");
    $delAtt->execute([':sid' => $student_id]);

    // Delete user
    $delUser = $db->prepare("DELETE FROM users WHERE id = :sid");
    $delUser->execute([':sid' => $student_id]);

    echo json_encode(['success' => true, 'message' => 'Student deleted']);
    exit;
} catch (Exception $e) {
    error_log('delete_student error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server error']);
    exit;
}

?>
