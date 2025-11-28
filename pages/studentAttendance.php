<?php
// restructured/pages/studentAttendance.php
// Only student role can access this page
if ($_SESSION['role'] !== 'student' && $_SESSION['role'] !== 'étudiant') {
    http_response_code(403);
    echo '<main class="container"><div class="card"><h2>⛔ Accès Refusé</h2><p>Vous n\'avez pas la permission d\'accéder à cette page.</p></div></main>';
    exit;
}

require_once __DIR__ . '/../config/db.php';

$db = (new Database())->getConnection();
$course_id = $_GET['course_id'] ?? null;

// Get course info
if ($course_id) {
    try {
        $stmt = $db->prepare("SELECT c.id, c.course_name, u.first_name, u.last_name
                              FROM courses c
                              JOIN enrollments e ON c.id = e.course_id
                              LEFT JOIN users u ON c.professor_id = u.id
                              WHERE c.id = :course_id AND e.student_id = :student_id
                              LIMIT 1");
        $stmt->bindParam(':course_id', $course_id);
        $stmt->bindParam(':student_id', $_SESSION['user_id']);
        $stmt->execute();
        $courseInfo = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $courseInfo = null;
    }
} else {
    $courseInfo = null;
}

// Get attendance details for this course
if ($course_id && $courseInfo) {
    try {
        $stmt = $db->prepare("SELECT s.id, s.session_number, s.session_date, s.topic, ar.status, ar.participation
                              FROM sessions s
                              LEFT JOIN attendance_records ar ON s.id = ar.session_id AND ar.student_id = :student_id
                              WHERE s.course_id = :course_id
                              ORDER BY s.session_date DESC");
        $stmt->bindParam(':course_id', $course_id);
        $stmt->bindParam(':student_id', $_SESSION['user_id']);
        $stmt->execute();
        $attendance = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $attendance = [];
    }
} else {
    $attendance = [];
}
?>
<main class="container">
  <div class="card">
    <h2>📊 Détails de Présence<?php if ($courseInfo): ?> - <?= htmlspecialchars($courseInfo['course_name']) ?><?php endif; ?></h2>
    
    <?php if (!$course_id || !$courseInfo): ?>
    <div style="padding:20px; background:#fef3c7; border-radius:8px; border-left:4px solid #f59e0b;">
      <p>ℹ️ Sélectionnez un cours pour voir les détails de présence.</p>
      <p><a class="btn btn-primary" href="?page=studentHome">Retour à Mes Cours</a></p>
    </div>
    <?php else: ?>
    
    <div style="margin:16px 0; padding:16px; background:#f0f9ff; border-radius:8px;">
      <p><strong>Cours:</strong> <?= htmlspecialchars($courseInfo['course_name']) ?></p>
      <p><strong>Professeur:</strong> <?= htmlspecialchars($courseInfo['first_name'] . ' ' . $courseInfo['last_name']) ?></p>
    </div>

    <div class="controls" style="margin:20px 0;">
      <div class="search-box"><input type="text" placeholder="Rechercher une session..."></div>
      <a class="btn btn-warning" href="#">Justifier une Absence</a>
    </div>
    
    <?php if (count($attendance) > 0): ?>
    <div class="table-wrap">
      <table>
        <thead><tr><th>Session</th><th>Date</th><th>Sujet</th><th>Statut</th><th>Participation</th><th>Actions</th></tr></thead>
        <tbody>
          <?php foreach ($attendance as $record): 
            $status = $record['status'] ?? 'absent';
            $statusClass = $status === 'present' ? 'student-excellent' : 'student-critical';
            $statusDisplay = $status === 'present' ? '✅ Présent' : ($status === 'late' ? '⏱️ Retard' : '❌ Absent');
          ?>
          <tr class="<?= $statusClass ?>">
            <td>#<?= $record['session_number'] ?></td>
            <td><?= htmlspecialchars($record['session_date'] ?? 'N/A') ?></td>
            <td><?= htmlspecialchars($record['topic'] ?? '-') ?></td>
            <td class="<?= $status === 'present' ? 'status-excellent' : 'status-critical' ?>"><?= $statusDisplay ?></td>
            <td><?= ($record['participation'] ?? 0) . '%' ?></td>
            <td><?php if ($status === 'absent'): ?><a class="btn btn-warning" href="#">Justifier</a><?php else: ?>-<?php endif; ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php else: ?>
    <div style="padding:20px; background:#f3f4f6; border-radius:8px; text-align:center;">
      <p style="color:var(--muted);">Aucune session enregistrée pour ce cours.</p>
    </div>
    <?php endif; ?>
    
    <?php endif; ?>
  </div>
</main>
