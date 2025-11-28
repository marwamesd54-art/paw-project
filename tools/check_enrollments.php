<?php
require_once __DIR__ . '/../config/db.php';
try {
    $db = (new Database())->getConnection();
    $sql = "SELECT c.id AS course_id, c.course_name, c.course_code, c.professor_id, COUNT(e.student_id) AS enrolled
            FROM courses c
            LEFT JOIN enrollments e ON c.id = e.course_id
            GROUP BY c.id
            ORDER BY c.created_at DESC";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Courses:\n";
    if (!$courses) {
        echo "  (no courses found)\n";
        exit(0);
    }
    foreach ($courses as $c) {
        echo " - ID: {$c['course_id']} | {$c['course_name']} ({$c['course_code']}) | professor_id: {$c['professor_id']} | enrolled: {$c['enrolled']}\n";
        // list students if any
        if ($c['enrolled'] > 0) {
            $s = $db->prepare("SELECT u.id, u.username, u.first_name, u.last_name, u.email FROM enrollments en JOIN users u ON en.student_id = u.id WHERE en.course_id = ?");
            $s->execute([$c['course_id']]);
            $students = $s->fetchAll(PDO::FETCH_ASSOC);
            foreach ($students as $stu) {
                echo "    * {$stu['id']} - {$stu['first_name']} {$stu['last_name']} ({$stu['username']}) - {$stu['email']}\n";
            }
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(2);
}
