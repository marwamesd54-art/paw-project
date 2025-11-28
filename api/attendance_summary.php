<?php
// api/attendance_summary.php
// Returns JSON totals for presences / absences / participation.
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/db.php';

try {
    $db = (new Database())->getConnection();

    $course_id = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 0;

    if ($course_id) {
        // Get last 6 sessions for the course
        $s = $db->prepare("SELECT id FROM sessions WHERE course_id = :course_id ORDER BY session_date DESC LIMIT 6");
        $s->execute([':course_id' => $course_id]);
        $sessionIds = $s->fetchAll(PDO::FETCH_COLUMN);

        if (empty($sessionIds)) {
            echo json_encode(['success' => true, 'presences' => 0, 'absences' => 0, 'participation' => 0]);
            exit;
        }

        // Build IN clause safely
        $placeholders = implode(',', array_fill(0, count($sessionIds), '?'));
        $sql = "SELECT 
                    SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) AS presences,
                    SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) AS absences,
                    SUM(participation) AS participation
                FROM attendance_records
                WHERE session_id IN ($placeholders)";
        $stmt = $db->prepare($sql);
        $stmt->execute($sessionIds);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        // No course specified: aggregate across all attendance records
        $stmt = $db->query("SELECT 
                                SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) AS presences,
                                SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) AS absences,
                                SUM(participation) AS participation
                            FROM attendance_records");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    $presences = (int)($row['presences'] ?? 0);
    $absences = (int)($row['absences'] ?? 0);
    $participation = (int)($row['participation'] ?? 0);

    echo json_encode([
        'success' => true,
        'presences' => $presences,
        'absences' => $absences,
        'participation' => $participation
    ]);
    exit;

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error', 'error' => $e->getMessage()]);
    error_log('attendance_summary error: ' . $e->getMessage());
    exit;
}

?>
