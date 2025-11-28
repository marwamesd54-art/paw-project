<?php
class Attendance {
    private $conn;
    private $table_name = "attendance";

    public function __construct($db) {
        $this->conn = $db;
    }

    // تسجيل الحضور والمشاركة معاً
    public function recordAttendanceWithParticipation($session_id, $student_id, $status, $participation_data = [], $notes = '') {
        try {
            $this->conn->beginTransaction();
            $attendance_query = "INSERT INTO " . $this->table_name . " 
                               (session_id, student_id, status, notes) 
                               VALUES (:session_id, :student_id, :status, :notes)
                               ON DUPLICATE KEY UPDATE 
                               status = VALUES(status), notes = VALUES(notes)";
            $stmt = $this->conn->prepare($attendance_query);
            $stmt->bindParam(":session_id", $session_id);
            $stmt->bindParam(":student_id", $student_id);
            $stmt->bindParam(":status", $status);
            $stmt->bindParam(":notes", $notes);
            $stmt->execute();
            if (!empty($participation_data)) {
                $participation_query = "INSERT INTO participation 
                                      (session_id, student_id, participation_type, score, notes) 
                                      VALUES (:session_id, :student_id, :type, :score, :notes)
                                      ON DUPLICATE KEY UPDATE 
                                      score = VALUES(score), notes = VALUES(notes)";
                $part_stmt = $this->conn->prepare($participation_query);
                foreach ($participation_data as $part) {
                    if (isset($part['type']) && isset($part['score'])) {
                        $part_stmt->bindParam(":session_id", $session_id);
                        $part_stmt->bindParam(":student_id", $student_id);
                        $part_stmt->bindParam(":type", $part['type']);
                        $part_stmt->bindParam(":score", $part['score']);
                        $part_stmt->bindParam(":notes", $part['notes'] ?? '');
                        $part_stmt->execute();
                    }
                }
            }
            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("Attendance recording error: " . $e->getMessage());
            return false;
        }
    }

    public function getStudentParticipation($session_id, $student_id) {
        $query = "SELECT participation_type, score, notes 
                  FROM participation 
                  WHERE session_id = :session_id AND student_id = :student_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":session_id", $session_id);
        $stmt->bindParam(":student_id", $student_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getStudentParticipationStats($student_id, $course_id) {
        $query = "SELECT 
                    p.participation_type,
                    AVG(p.score) as avg_score,
                    COUNT(p.id) as session_count
                  FROM participation p
                  JOIN sessions s ON p.session_id = s.id
                  WHERE p.student_id = :student_id AND s.course_id = :course_id
                  GROUP BY p.participation_type
                  ORDER BY p.participation_type";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":student_id", $student_id);
        $stmt->bindParam(":course_id", $course_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
