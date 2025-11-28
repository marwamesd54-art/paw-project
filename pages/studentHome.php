<?php
// restructured/pages/studentHome.php
// Only student role can access this page
if ($_SESSION['role'] !== 'student' && $_SESSION['role'] !== 'étudiant') {
    http_response_code(403);
    echo '<main class="container"><div class="card"><h2>⛔ Accès Refusé</h2><p>Vous n\'avez pas la permission d\'accéder à cette page.</p></div></main>';
    exit;
}

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/Attendance.php';

$db = (new Database())->getConnection();

// Get student's enrolled courses with attendance
try {
    $stmt = $db->prepare("SELECT c.id, c.course_name, c.course_code, u.first_name, u.last_name,
                                 COUNT(DISTINCT s.id) as total_sessions,
                                 SUM(CASE WHEN ar.status = 'present' THEN 1 ELSE 0 END) as present_count
                          FROM courses c
                          JOIN enrollments e ON c.id = e.course_id
                          LEFT JOIN sessions s ON c.id = s.course_id
                          LEFT JOIN attendance_records ar ON s.id = ar.session_id AND e.student_id = ar.student_id
                          LEFT JOIN users u ON c.professor_id = u.id
                          WHERE e.student_id = :student_id
                          GROUP BY c.id
                          ORDER BY c.course_name");
    $stmt->bindParam(':student_id', $_SESSION['user_id']);
    $stmt->execute();
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $courses = [];
}

// Calculate overall attendance percentage
$totalAttendance = 0;
$totalSessions = 0;
foreach ($courses as $course) {
    $totalAttendance += ($course['present_count'] ?? 0);
    $totalSessions += ($course['total_sessions'] ?? 0);
}
$attendancePercentage = $totalSessions > 0 ? round(($totalAttendance / $totalSessions) * 100) : 0;
?>
<main class="container">
  <div class="card">
    <h2>📚 Mon Espace Étudiant</h2>
    <p style="color:var(--muted);">Bienvenue, <strong><?= htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']) ?></strong></p>
    
    <div style="margin:20px 0; background:linear-gradient(135deg, #667eea 0%, #764ba2 100%); color:white; padding:20px; border-radius:8px;">
      <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
        <div>
          <div style="font-size:14px; opacity:0.9;">Assiduité Globale</div>
          <div style="font-size:32px; font-weight:bold; margin-top:8px;"><?= $attendancePercentage ?>%</div>
        </div>
        <div>
          <div style="font-size:14px; opacity:0.9;">Présences / Sessions</div>
          <div style="font-size:32px; font-weight:bold; margin-top:8px;"><?= $totalAttendance ?> / <?= $totalSessions ?></div>
        </div>
      </div>
    </div>

    <h3 style="margin-top:28px;">Mes Cours Inscrits</h3>
    <?php if (count($courses) > 0): ?>
    <div class="card-grid">
      <?php foreach ($courses as $course): 
        $courseAttendance = ($course['total_sessions'] > 0) ? round(($course['present_count'] / $course['total_sessions']) * 100) : 0;
        $statusClass = $courseAttendance >= 80 ? 'excellent' : ($courseAttendance >= 60 ? 'good' : 'warning');
      ?>
      <div class="tile">
        <h3><?= htmlspecialchars($course['course_name']) ?></h3>
        <p>👨‍🏫 <?= htmlspecialchars($course['first_name'] . ' ' . $course['last_name']) ?></p>
        <div style="margin:12px 0; display:flex; justify-content:space-between; align-items:center;">
          <small>Assiduité:</small>
          <strong style="color:<?= $statusClass === 'excellent' ? '#16a34a' : ($statusClass === 'good' ? '#0ea5e9' : '#dc2626') ?>"><?= $courseAttendance ?>%</strong>
        </div>
        <small style="display:block; margin-top:8px; color:var(--muted);"><?= $course['present_count'] ?> / <?= $course['total_sessions'] ?> sessions</small>
        <div style="margin-top:10px;">
          <a class="btn btn-primary" href="?page=studentAttendance&course_id=<?= $course['id'] ?>">Voir Détails</a>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div style="padding:20px; background:#f3f4f6; border-radius:8px; text-align:center;">
      <p style="color:var(--muted);">Vous n'êtes inscrit à aucun cours. Contactez l'administrateur.</p>
    </div>
    <?php endif; ?>
  </div>
</main>
