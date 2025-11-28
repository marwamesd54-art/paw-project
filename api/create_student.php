<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['professor','professeur'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true) ?: $_POST;

$student_identifier = isset($data['studentId']) ? trim($data['studentId']) : '';
$last = isset($data['lastName']) ? trim($data['lastName']) : '';
$first = isset($data['firstName']) ? trim($data['firstName']) : '';
$email = isset($data['email']) ? trim($data['email']) : '';
$course_id = isset($data['courseId']) ? intval($data['courseId']) : 0;

if (!$student_identifier || !$last || !$first || !$course_id) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

try {
    $db = (new Database())->getConnection();

    // Check if user already exists (by username or email)
    $stmt = $db->prepare("SELECT id FROM users WHERE username = :username OR email = :email LIMIT 1");
    $stmt->execute([':username' => $student_identifier, ':email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $student_db_id = $user['id'];
    } else {
        $password = password_hash('changeme', PASSWORD_DEFAULT);
        $ins = $db->prepare("INSERT INTO users (username, password, email, first_name, last_name, role) VALUES (:username, :password, :email, :first, :last, 'student')");
        $ins->execute([':username' => $student_identifier, ':password' => $password, ':email' => $email, ':first' => $first, ':last' => $last]);
        $student_db_id = $db->lastInsertId();
    }

    // Enroll student in course if not already enrolled
    $en = $db->prepare("SELECT id FROM enrollments WHERE course_id = :course_id AND student_id = :student_id LIMIT 1");
    $en->execute([':course_id' => $course_id, ':student_id' => $student_db_id]);
    $exists = $en->fetch(PDO::FETCH_ASSOC);
    if (!$exists) {
        $insEn = $db->prepare("INSERT INTO enrollments (course_id, student_id) VALUES (:course_id, :student_id)");
        $insEn->execute([':course_id' => $course_id, ':student_id' => $student_db_id]);
    }

    echo json_encode(['success' => true, 'student_db_id' => $student_db_id, 'message' => 'Student created and enrolled.']);
    exit;
} catch (Exception $e) {
    error_log('create_student error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server error']);
    exit;
}

?>
