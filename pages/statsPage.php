<?php
// restructured/pages/statsPage.php
// Only admin role can access this page
if ($_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo '<main class="container"><div class="card"><h2>⛔ Accès Refusé</h2><p>Vous n\'avez pas la permission d\'accéder à cette page.</p></div></main>';
    exit;
}

require_once __DIR__ . '/../config/db.php';

$db = (new Database())->getConnection();

// Get statistics
try {
    $stmt = $db->query("SELECT COUNT(*) as total FROM users WHERE role = 'student'");
    $totalStudents = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    $stmt = $db->query("SELECT COUNT(*) as total FROM courses");
    $totalCourses = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    $stmt = $db->query("SELECT COUNT(*) as total FROM users WHERE role = 'professor'");
    $totalProfessors = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Average attendance
    $stmt = $db->query("SELECT AVG(CASE WHEN status = 'present' THEN 100 ELSE 0 END) as avg_attendance FROM attendance");
    $avgAttendance = round($stmt->fetch(PDO::FETCH_ASSOC)['avg_attendance'] ?? 0);
} catch (Exception $e) {
    $totalStudents = 0;
    $totalCourses = 0;
    $totalProfessors = 0;
    $avgAttendance = 0;
}
?>
<main class="container">
  <div class="card">
    <h2>📈 Statistiques Globales</h2>
    <div class="controls">
      <a class="btn btn-primary" href="#">🔄 Actualiser</a>
      <a class="btn btn-success" href="#">📤 Exporter Stats</a>
      <a class="btn btn-warning" href="#">📋 Rapport Global</a>
    </div>
    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:16px; margin:20px 0;">
      <div class="card"><h3><?= number_format($totalStudents) ?></h3><p>Étudiants total</p></div>
      <div class="card"><h3><?= $avgAttendance ?>%</h3><p>Présence moyenne</p></div>
      <div class="card"><h3><?= number_format($totalCourses) ?></h3><p>Cours actifs</p></div>
      <div class="card"><h3><?= number_format($totalProfessors) ?></h3><p>Professeurs</p></div>
    </div>
  </div>
</main>
