<?php
// api/create_session.php - create a new session for a course (professor only)
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

$course_id = $_POST['course_id'] ?? null;
if (!$course_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing course_id']);
    exit;
}

try {
    $db = (new Database())->getConnection();
    // verify professor owns the course
    $stmt = $db->prepare("SELECT id FROM courses WHERE id = :cid AND professor_id = :pid");
    $stmt->execute([':cid' => $course_id, ':pid' => $_SESSION['user_id']]);
    if (!$stmt->fetchColumn()) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Not allowed for this course']);
        exit;
    }

    // compute next session_number
    $nstmt = $db->prepare("SELECT MAX(session_number) FROM sessions WHERE course_id = :cid");
    $nstmt->execute([':cid' => $course_id]);
    $max = (int)$nstmt->fetchColumn();
    $next = $max + 1;

    $today = (new DateTime())->format('Y-m-d');

    // try insert; adapt to available columns
    $availableCols = [];
    $colStmt = $db->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'sessions'");
    $colStmt->execute();
    $cols = $colStmt->fetchAll(PDO::FETCH_COLUMN);
    $availableCols = $cols ?: [];

    $baseCols = ['course_id','session_number','session_date'];
    if (in_array('topic', $availableCols)) $baseCols[] = 'topic';
    if (in_array('is_open', $availableCols)) $baseCols[] = 'is_open';

    $colList = implode(', ', $baseCols);
    $placeholders = implode(', ', array_fill(0, count($baseCols), '?'));

    $values = [$course_id, $next, $today];
    if (in_array('topic', $baseCols)) $values[] = 'Auto-created';
    if (in_array('is_open', $baseCols)) $values[] = 1;

    $ist = $db->prepare("INSERT INTO sessions ($colList) VALUES ($placeholders)");
    $ist->execute($values);
    $newId = $db->lastInsertId();

    echo json_encode(['success' => true, 'session_id' => $newId, 'session_number' => $next]);
    exit;
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
    exit;
}
