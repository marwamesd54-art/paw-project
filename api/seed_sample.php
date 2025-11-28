<?php
// api/seed_sample.php
// Create sample course, sessions, students and attendance for the current professor (for demo/testing)
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';

// Ensure session is available
if (session_status() === PHP_SESSION_NONE) session_start();

if (empty($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['professor','professeur'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Permission denied']);
    exit;
}

$db = (new Database())->getConnection();
$professor_id = $_SESSION['user_id'];

try {
    $db->beginTransaction();

    // 1) create a sample course
    $course_code = 'TEST-' . time();
    $course_name = 'Course Demo ' . date('Y-m-d H:i');
    $group_name = 'TD-A';

    $stmt = $db->prepare("INSERT INTO courses (course_code, course_name, professor_id, group_name) VALUES (?, ?, ?, ?)");
    $stmt->execute([$course_code, $course_name, $professor_id, $group_name]);
    $course_id = $db->lastInsertId();

    // 2) create 6 sessions (recent dates)
    $today = new DateTime();
    $sessions = [];
    // detect available columns in `sessions` to avoid failing if schema differs
    $availableCols = [];
    try {
        $colStmt = $db->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'sessions'");
        $colStmt->execute();
        $cols = $colStmt->fetchAll(PDO::FETCH_COLUMN);
        $availableCols = $cols ?: [];
    } catch (Exception $e) {
        $availableCols = [];
    }

    // decide which columns to insert
    $baseCols = ['course_id', 'session_number', 'session_date'];
    if (in_array('topic', $availableCols)) $baseCols[] = 'topic';
    if (in_array('is_open', $availableCols)) $baseCols[] = 'is_open';

    $colList = implode(', ', $baseCols);
    $placeholders = implode(', ', array_fill(0, count($baseCols), '?'));

    for ($i = 1; $i <= 6; $i++) {
        $d = clone $today;
        $d->modify('-' . ($i-1)*7 . ' days'); // weekly spacing
        $session_date = $d->format('Y-m-d');
        $topic = 'Session demo #' . $i;

        $values = [$course_id, $i, $session_date];
        if (in_array('topic', $baseCols)) $values[] = $topic;
        if (in_array('is_open', $baseCols)) $values[] = 1;

        $stmt = $db->prepare("INSERT INTO sessions ($colList) VALUES ($placeholders)");
        $stmt->execute($values);
        $sessions[] = $db->lastInsertId();
    }

    // 3) create 3 demo students
    $studentIds = [];
    for ($s = 1; $s <= 3; $s++) {
        $username = 'demo_student_' . time() . '_' . $s;
        $passwordHash = password_hash('password', PASSWORD_DEFAULT);
        $first = 'Student' . $s;
        $last = 'Demo' . $s;
        $email = 'demo' . $s . '@example.local';

        $stmt = $db->prepare("INSERT INTO users (username, password, email, first_name, last_name, role) VALUES (?, ?, ?, ?, ?, 'student')");
        $stmt->execute([$username, $passwordHash, $email, $first, $last]);
        $studentIds[] = $db->lastInsertId();
    }

    // 4) enroll students
    $enrollStmt = $db->prepare("INSERT INTO enrollments (course_id, student_id) VALUES (?, ?)");
    foreach ($studentIds as $sid) {
        $enrollStmt->execute([$course_id, $sid]);
    }

    // 5) create attendance records with varied statuses and participations
    $attStmt = $db->prepare("INSERT INTO attendance_records (session_id, student_id, status, participation, behavior) VALUES (?, ?, ?, ?, ?)");
    foreach ($sessions as $index => $sessId) {
        foreach ($studentIds as $si => $stuId) {
            // create some variation
            if ($si === 0) {
                // mostly present, good participation
                $status = ($index % 5 === 0) ? 'absent' : 'present';
                $part = 80 - ($index * 5);
            } elseif ($si === 1) {
                // mixed
                $status = ($index % 2 === 0) ? 'present' : 'late';
                $part = 40 + ($index * 5);
            } else {
                // more absences
                $status = ($index >= 3) ? 'absent' : 'present';
                $part = ($index >= 3) ? 0 : 30 + ($index * 5);
            }
            $behavior = min(100, max(0, $part - 5));
            $attStmt->execute([$sessId, $stuId, $status, $part, $behavior]);
        }
    }

    $db->commit();
    echo json_encode(['success' => true, 'message' => 'Sample data created', 'course_id' => $course_id]);
    exit;
} catch (Exception $e) {
    if ($db->inTransaction()) $db->rollBack();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error creating sample data: ' . $e->getMessage()]);
    exit;
}
