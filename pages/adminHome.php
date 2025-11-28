<?php
// restructured/pages/adminHome.php
// Only admin role can access this page
if ($_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo '<main class="container"><div class="card"><h2>⛔ Accès Refusé</h2><p>Vous n\'avez pas la permission d\'accéder à cette page.</p></div></main>';
    exit;
}

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Course.php';

$db = (new Database())->getConnection();
$userModel = new User($db);

// Get total counts for statistics
try {
    $stmt = $db->query("SELECT COUNT(*) as total FROM users WHERE role = 'student'");
    $totalStudents = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    $stmt = $db->query("SELECT COUNT(*) as total FROM users WHERE role = 'professor'");
    $totalProfessors = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    $stmt = $db->query("SELECT COUNT(*) as total FROM courses");
    $totalCourses = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
} catch (Exception $e) {
    $totalStudents = 0;
    $totalProfessors = 0;
    $totalCourses = 0;
}
?>
<main class="container">
  <div class="card">
    <h2>🎓 Tableau de Bord Administrateur</h2>
    <p style="color:var(--muted);">Bienvenue, <strong><?= htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']) ?></strong></p>
    
    <div style="margin:20px 0; display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:16px;">
      <div style="background:linear-gradient(135deg, #6473b4ff 0%, #4446a1ff 100%); color:white; padding:20px; border-radius:8px; text-align:center;">
        <div style="font-size:32px; font-weight:bold;"><?= $totalStudents ?></div>
        <div style="font-size:14px; margin-top:8px;">Étudiants</div>
      </div>
      <div style="background:linear-gradient(135deg, #6473b4ff 0%, #4446a1ff 100%); color:white; padding:20px; border-radius:8px; text-align:center;">
        <div style="font-size:32px; font-weight:bold;"><?= $totalProfessors ?></div>
        <div style="font-size:14px; margin-top:8px;">Professeurs</div>
      </div>
      <div style="background:linear-gradient(135deg, #6473b4ff 0%, #4446a1ff 100%); color:white; padding:20px; border-radius:8px; text-align:center;">
        <div style="font-size:32px; font-weight:bold;"><?= $totalCourses ?></div>
        <div style="font-size:14px; margin-top:8px;">Cours</div>
      </div>
    </div>

    <h3 style="margin-top: 28px; margin-bottom: 15px;">Actions d'Administration</h3>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px;">
      <div style="background: white; border: 2px solid #667eea; padding: 20px; border-radius: 8px; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.boxShadow='0 4px 15px rgba(102,126,234,0.2)'" onmouseout="this.style.boxShadow='none'" onclick="window.location.href='?page=statsPage'">
        <h4 style="color: #667eea; margin-bottom: 10px;">📊 Statistiques Globales</h4>
        <p style="color: #666; font-size: 14px;">Vue d'ensemble complète des données</p>
      </div>
      <div style="background: white; border: 2px solid #667eea; padding: 20px; border-radius: 8px; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.boxShadow='0 4px 15px rgba(102,126,234,0.2)'" onmouseout="this.style.boxShadow='none'" onclick="window.location.href='?page=studentManagement'">
        <h4 style="color: #667eea; margin-bottom: 10px;">👥 Gestion des Étudiants</h4>
        <p style="color: #666; font-size: 14px;">Importer / Exporter / Ajouter / Supprimer</p>
      </div>
      <div style="background: white; border: 2px solid #667eea; padding: 20px; border-radius: 8px; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.boxShadow='0 4px 15px rgba(102,126,234,0.2)'" onmouseout="this.style.boxShadow='none'" onclick="window.location.href='#'">
        <h4 style="color: #667eea; margin-bottom: 10px;">📚 Gestion des Cours</h4>
        <p style="color: #666; font-size: 14px;">Créer et configurer les cours</p>
      </div>
    </div>
  </div>
</main>
