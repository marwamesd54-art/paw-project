<?php
// api/close_session.php - close (set is_open=0) a session
header('Content-Type: application/json; charset=utf-8');
session_start();
require_once __DIR__ . '/../config/db.php';

if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}
$role = $_SESSION['role'] ?? '';
if ($role !== 'professor' && $role !== 'professeur' && $role !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Forbidden']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$session_id = $input['session_id'] ?? null;
if (!$session_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing session_id']);
    exit;
}

try {
    $db = (new Database())->getConnection();
    // ensure professor owns the course for this session
    $stmt = $db->prepare("SELECT s.id FROM sessions s JOIN courses c ON s.course_id = c.id WHERE s.id = :sid AND c.professor_id = :pid");
    $stmt->execute([':sid' => $session_id, ':pid' => $_SESSION['user_id']]);
    $found = $stmt->fetchColumn();
    if (!$found) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Not allowed to close this session']);
        exit;
    }

    $upd = $db->prepare("UPDATE sessions SET is_open = 0 WHERE id = :sid");
    $upd->execute([':sid' => $session_id]);
    echo json_encode(['success' => true, 'message' => 'Session closed']);
    exit;
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
    exit;
}
