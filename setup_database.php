<?php
// setup_database.php - Initialize database schema and seed data
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Database - Système d'Assiduité</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container { 
            background: white; 
            border-radius: 12px; 
            padding: 40px; 
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            max-width: 600px;
            width: 100%;
        }
        h2 { margin: 20px 0 10px; color: #333; }
        h3 { margin: 15px 0 10px; color: #555; font-size: 16px; }
        ul { margin-left: 20px; }
        li { margin: 8px 0; padding: 8px; background: #f5f5f5; border-radius: 6px; }
        .success { color: #27ae60; }
        .error { color: #e74c3c; }
        p { margin: 10px 0; color: #666; }
        a { color: #667eea; text-decoration: none; font-weight: bold; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
<div class="container">
    <h1>🎓 Système d'Assiduité - Configuration</h1>
<?php
session_start();

$host = "localhost";
$username = "root";
$password = "";

try {
    // Connect without specifying database first
    $conn = new PDO("mysql:host=" . $host, $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Read and execute schema
    $schema = file_get_contents(__DIR__ . '/schema.sql');
    $conn->exec($schema);
    
    echo "<h2 class='success'>✅ Schéma de base de données créé avec succès!</h2>";
    
    // Connect to the database now that it exists
    $conn = new PDO("mysql:host=" . $host . ";dbname=university_attendance", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Read and execute seed data
    $seedData = file_get_contents(__DIR__ . '/seed_data.sql');
    $conn->exec($seedData);
    
    echo "<h2 class='success'>✅ Données d'exemple insérées avec succès!</h2>";
    
    // Test login data
    $passwords = [
        'admin' => 'password',
        'professor' => 'password',
        'student1' => 'password',
        'student2' => 'password',
        'student3' => 'password'
    ];
    
    echo "<h3>📝 Identifiants de test:</h3>";
    echo "<ul>";
    foreach ($passwords as $user => $pass) {
        $hash = password_hash($pass, PASSWORD_BCRYPT);
        echo "<li><strong>" . htmlspecialchars($user) . "</strong> / <strong>" . htmlspecialchars($pass) . "</strong></li>";
        // Update password hash in database
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE username = ?");
        $stmt->execute([$hash, $user]);
    }
    echo "</ul>";
    
    echo "<h3 class='success'>✅ Configuration complète!</h3>";
    echo "<p>Cliquez ci-dessous pour accéder à la page de connexion:</p>";
    echo "<p><a href='public/?page=login' style='font-size: 18px; padding: 10px 20px; display: inline-block; background: #667eea; color: white; border-radius: 6px;'>Aller à la page de connexion →</a></p>";
    
} catch (PDOException $e) {
    echo "<h2 class='error'>❌ Erreur PDO: " . htmlspecialchars($e->getMessage()) . "</h2>";
    echo "<p>Assurez-vous que MySQL est en cours d'exécution et que les identifiants sont corrects.</p>";
} catch (Exception $e) {
    echo "<h2 class='error'>❌ Erreur: " . htmlspecialchars($e->getMessage()) . "</h2>";
}
?>
</div>
</body>
</html>
