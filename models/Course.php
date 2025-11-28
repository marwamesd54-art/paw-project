<?php
class Course {
    private $conn;
    private $table_name = "courses";

    public $id;
    public $course_code;
    public $course_name;
    public $description;
    public $professor_id;
    public $group_name;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getProfessorCourses($professor_id) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE professor_id = :professor_id 
                  ORDER BY course_name";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":professor_id", $professor_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getStudentCourses($student_id) {
        $query = "SELECT c.*, e.enrolled_at 
                  FROM " . $this->table_name . " c
                  JOIN enrollments e ON c.id = e.course_id
                  WHERE e.student_id = :student_id
                  ORDER BY c.course_name";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":student_id", $student_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTotalCourses() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }

    public function getCourseById($course_id) {
        $query = "SELECT c.*, u.first_name, u.last_name 
                  FROM " . $this->table_name . " c
                  LEFT JOIN users u ON c.professor_id = u.id
                  WHERE c.id = :course_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":course_id", $course_id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
