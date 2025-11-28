<?php
/**
 * Test file to verify role-based redirect and page access
 * Run this file after login to verify the system
 * 
 * Testing checklist:
 * 1. Login as admin - should see adminHome
 * 2. Login as professor - should see professorHome  
 * 3. Login as student - should see studentHome
 * 4. Try to access admin pages as student - should get 403
 * 5. Check sidebar menu shows only relevant links
 */

session_start();

if (empty($_SESSION['user_id'])) {
    echo "Not logged in. <a href='/attendance_system/public/?page=login'>Go to login</a>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test - Role-Based Access</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; max-width: 900px; margin: 0 auto; }
        .test-card { border: 1px solid #ddd; padding: 15px; margin: 10px 0; border-radius: 8px; }
        .pass { background: #d4edda; border-color: #c3e6cb; }
        .fail { background: #f8d7da; border-color: #f5c6cb; }
        .info { background: #d1ecf1; border-color: #bee5eb; }
        h2 { color: #333; }
        .user-info { background: #f0f0f0; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .link { display: inline-block; margin-top: 10px; padding: 8px 15px; background: #667eea; color: white; text-decoration: none; border-radius: 4px; }
        .link:hover { background: #5568d3; }
    </style>
</head>
<body>
    <h1>🧭ѧ Test du Système de Rôles et Permissions</h1>
    
    <div class="user-info">
        <h3>Informations de l'utilisateur actuel:</h3>
        <p><strong>Nom:</strong> <?php echo htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']); ?></p>
        <p><strong>Rôle:</strong> <?php echo htmlspecialchars($_SESSION['role']); ?></p>
        <p><strong>Utilisateur:</strong> <?php echo htmlspecialchars($_SESSION['username']); ?></p>
    </div>

    <h2>Liste des tests:</h2>

<?php
$role = $_SESSION['role'] ?? '';

// Test 1: Check current role
echo '<div class="test-card info">';
echo '<h3>✓ Votre rôle actuel: <strong>' . htmlspecialchars(strtoupper($_SESSION['role'])) . '</strong></h3>';
echo '</div>';

// Test 2: Check if role matches home page
$expectedPages = [
    'admin' => 'adminHome',
    'professor' => 'professorHome',
    'professeur' => 'professorHome',
    'student' => 'studentHome',
    'étudiant' => 'studentHome'
];

$expectedPage = $expectedPages[$role] ?? null;

echo '<div class="test-card ' . ($expectedPage ? 'pass' : 'fail') . '">';
echo '<h3>Page d\'accueil prévue:</h3>';
echo '<p>Page: <strong>' . htmlspecialchars($expectedPage ?? 'Unknown') . '</strong></p>';
echo '<a class="link" href="?page=' . htmlspecialchars($expectedPage ?? '') . '">Aller à votre page d\'accueil</a>';
echo '</div>';

// Test 3: List accessible pages based on role
echo '<div class="test-card pass">';
echo '<h3>Pages accessibles pour vous:</h3>';
echo '<ul>';

if ($role === 'admin') {
    echo '<li><a href="?page=adminHome">📊 Tableau de Bord Administrateur</a></li>';
    echo '<li><a href="?page=statsPage">📈 Statistiques Globales</a></li>';
    echo '<li><a href="?page=studentManagement">👥 Gestion des Étudiants</a></li>';
} elseif ($role === 'professor' || $role === 'professeur') {
    echo '<li><a href="?page=professorHome">📚 Mes Cours</a></li>';
    echo '<li><a href="?page=sessionManagement">👥 Gestion des Sessions</a></li>';
    echo '<li><a href="?page=attendanceSummary">📈 Résumés</a></li>';
} elseif ($role === 'student' || $role === 'étudiant') {
    echo '<li><a href="?page=studentHome">📚 Mes Cours</a></li>';
    echo '<li><a href="?page=studentAttendance">📊 Ma Présence</a></li>';
}

echo '</ul>';
echo '</div>';

// Test 4: Restricted access attempts
echo '<div class="test-card info">';
echo '<h3>Testez l\'accès restreint:</h3>';
echo '<p>Essayez d\'accéder à des pages protégées qui ne correspondent pas à votre rôle (vous devriez obtenir une erreur 403):</p>';

if ($role !== 'admin') {
    echo '<p><a class="link" href="?page=statsPage" style="background: #f5676d;">Essayer Statistiques (Admin uniquement)</a></p>';
    echo '<p><a class="link" href="?page=studentManagement" style="background: #f5676d;">Essayer Gestion des Étudiants (Admin uniquement)</a></p>';
}

if ($role !== 'professor' && $role !== 'professeur') {
    echo '<p><a class="link" href="?page=sessionManagement" style="background: #f5676d;">Essayer Gestion des Sessions (Professor uniquement)</a></p>';
}

if ($role !== 'student' && $role !== 'étudiant') {
    echo '<p><a class="link" href="?page=studentAttendance" style="background: #f5676d;">Essayer Page de Présence (Student uniquement)</a></p>';
}

echo '</div>';

// Test 5: Secure logout
echo '<div class="test-card info">';
echo '<h3>Déconnexion sécurisée:</h3>';
echo '<form method="post" action="/attendance_system/api/logout.php" style="display:inline">';
echo '<button type="submit" class="link" style="background: #667eea; border: none; cursor: pointer;">Se Déconnecter</button>';
echo '</form>';
echo '</div>';
?>

    <hr>
    <p style="color: #666; font-size: 12px;">
        Cette page est uniquement à des fins de test. N\'oubliez pas de vérifier la barre de navigation latérale - elle ne devrait afficher que les options appropriées à votre rôle.
    </p>
</body>
</html>
