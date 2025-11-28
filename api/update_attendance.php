<?php
// api/update_attendance.php - Handle attendance form submission
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

if (empty($_POST['session_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing session_id']);
    exit;
}

try {
    $db = (new Database())->getConnection();
    $session_id = $_POST['session_id'];
    $processed = 0;
    $errors = [];

    // Process each student's attendance
    if (!empty($_POST['attendance']) && is_array($_POST['attendance'])) {
        foreach ($_POST['attendance'] as $student_id => $data) {
            $status = $data['status'] ?? 'absent';
            $participation = (int)($data['participation'] ?? 0);
            $behavior = (int)($data['behavior'] ?? 0);

            // Validate inputs
            if (!in_array($status, ['present', 'absent', 'late'])) {
                $errors[] = "Invalid status for student $student_id";
                continue;
            }
            if ($participation < 0 || $participation > 100 || $behavior < 0 || $behavior > 100) {
                $errors[] = "Invalid participation/behavior score for student $student_id";
                continue;
            }

            try {
                // Insert or update attendance record
                $stmt = $db->prepare(
                    "INSERT INTO attendance_records 
                     (session_id, student_id, status, participation, behavior, recorded_at)
                     VALUES (:session_id, :student_id, :status, :participation, :behavior, NOW())
                     ON DUPLICATE KEY UPDATE
                     status = VALUES(status),
                     participation = VALUES(participation),
                     behavior = VALUES(behavior),
                     recorded_at = NOW()"
                );
                
                $stmt->execute([
                    ':session_id' => $session_id,
                    ':student_id' => $student_id,
                    ':status' => $status,
                    ':participation' => $participation,
                    ':behavior' => $behavior
                ]);

                $processed++;
            } catch (Exception $e) {
                $errors[] = "Error processing student $student_id: " . $e->getMessage();
            }
        }
    }

    // Return success response
    echo json_encode([
        'success' => true,
        'processed' => $processed,
        'errors' => $errors,
        'message' => "$processed enregistrement(s) mises à jour avec succès"
    ]);
    exit;

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
    error_log('update_attendance error: ' . $e->getMessage());
    exit;
}

?>
