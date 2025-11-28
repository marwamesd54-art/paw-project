<?php
// restructured/includes/header.php
// Minimal header template — links to public assets in `public/assets/`
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Système d'Assiduité — Prototype</title>
  <link rel="stylesheet" href="/attendance_system/public/assets/css/style.css">
  <style>
    /* Off-canvas sidebar styling */
    .menu-wrap{ position:relative; }
    #menuToggle{ background:rgba(255,255,255,0.12); color:white; padding:8px 12px; border-radius:10px; border:1px solid rgba(255,255,255,0.08); }
    .sidebar{ position:fixed; left:-280px; top:0; bottom:0; width:280px; background:linear-gradient(180deg, #fafbfd 0%, #ffffff 100%); z-index:9999; box-shadow:0 24px 80px rgba(11,22,60,0.18); padding:28px 16px; transition:left .28s cubic-bezier(.2,.9,.2,1); overflow:auto; }
    .sidebar.open{ left:0; }
    .sidebar .sidebar-nav{ display:flex; flex-direction:column; padding-top:30px; gap:0; padding-right:0; }
    .sidebar .nav-link{ display:block !important; padding:12px 14px; color:#0f1724; border-radius:10px; text-decoration:none; margin:4px 0; font-weight:700; font-size:14px; transition:all .15s ease; cursor:pointer; width:100%; box-sizing:border-box; }
    .sidebar .nav-link:hover{ background:rgba(59,91,255,0.08); transform:translateX(4px); color:var(--primary); }
    .sidebar .nav-link.active{ background:rgba(59,91,255,0.1); border-left:4px solid rgba(59,91,255,0.9); padding-left:10px; color:var(--primary); font-weight:800; }
    #app.shifted{ transform: none; }
    .menu-overlay{ display:none; position:fixed; inset:0; background: rgba(2,6,23,0.38); z-index:9998; transition:opacity .2s ease; }
    .menu-overlay.open{ display:block; opacity:1; }
  </style>
</head>
<body>
  <div id="app">
    <header>
      <div class="container">
        <div class="topbar">
          <div>
            <div class="brand">Système d'Assiduité — Université d'Alger</div>
            <div style="font-size:14px; opacity:0.9;" id="currentRoleDisplay"></div>
          </div>
          <div class="user-info">
<?php if (!empty($_SESSION['user_id'])): ?>
            <span id="userNameDisplay"><?php echo htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']); ?></span>
            <span class="role-badge" id="roleBadge"><?php echo htmlspecialchars($_SESSION['role']); ?></span>
            <form method="post" action="/attendance_system/api/logout.php" style="display:inline">
              <button type="submit" class="btn" style="background:rgba(255,255,255,0.2); color:white; margin-left:10px;">Déconnexion</button>
            </form>
<?php else: ?>
            <a href="?page=login" class="btn" style="background:rgba(255,255,255,0.2); color:white; margin-left:10px;">Se connecter</a>
<?php endif; ?>
          </div>
        </div>

        <!-- Off-canvas sidebar menu (role-based) -->
        <div id="mainNav" class="menu-wrap">
          <button id="menuToggle" class="btn" aria-expanded="false" aria-controls="sidebar">☰ Menu</button>
          <div id="sidebar" class="sidebar" aria-hidden="true">
            <nav class="sidebar-nav">
              <a class="nav-link" href="?page=home">🏠 Accueil</a>
<?php
// Generate role-specific navigation
$role = $_SESSION['role'] ?? '';
if ($role === 'admin'):
?>
              <hr style="border:none; border-top:1px solid #e5e7eb; margin:10px 0; opacity:0.5;">
              <a class="nav-link" href="?page=adminHome">📊 Tableau de Bord</a>
              <a class="nav-link" href="?page=statsPage">📈 Statistiques</a>
              <a class="nav-link" href="?page=studentManagement">👥 Gestion des Étudiants</a>
<?php elseif ($role === 'professor' || $role === 'professeur'): ?>
              <hr style="border:none; border-top:1px solid #e5e7eb; margin:10px 0; opacity:0.5;">
              <a class="nav-link" href="?page=professorHome">📚 Mes Cours</a>
              <a class="nav-link" href="?page=sessionManagement">👥 Gestion des Jours</a>
              <a class="nav-link" href="?page=attendanceSummary">📈 Résumés</a>
<?php elseif ($role === 'student' || $role === 'étudiant'): ?>
              <hr style="border:none; border-top:1px solid #e5e7eb; margin:10px 0; opacity:0.5;">
              <a class="nav-link" href="?page=studentHome">📚 Mes Cours</a>
              <a class="nav-link" href="?page=studentAttendance">📊 Mon Assiduité</a>
<?php endif; ?>
            </nav>
          </div>
          <div id="menuOverlay" class="menu-overlay" tabindex="-1"></div>
        </div>
      </div>
    </header>
