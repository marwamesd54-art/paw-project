<?php
session_start();
require_once __DIR__ . '/config/db.php';

$message = '';
$error = '';

// Check if form submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'create_test_student') {
        try {
            $db = (new Database())->getConnection();
            $username = 'teststudent';
            $email = 'teststudent@university.dz';
            $password = 'test123';
            
            $check = $db->prepare("SELECT id FROM users WHERE username = :u OR email = :e");
            $check->execute([':u' => $username, ':e' => $email]);
            
            if ($check->rowCount() > 0) {
                $message = '✅ حساب الاختبار موجود بالفعل. استخدم: ' . $username . ' / ' . $password;
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $ins = $db->prepare("INSERT INTO users (username, password, email, first_name, last_name, role, group_name) 
                                     VALUES (:u, :p, :e, 'Test', 'Student', 'student', 'G1')");
                $ins->execute([':u' => $username, ':p' => $hash, ':e' => $email]);
                $message = '✅ تم إنشاء حساب اختبار! استخدم: ' . $username . ' / ' . $password;
            }
        } catch (Exception $e) {
            $error = '❌ خطأ: ' . $e->getMessage();
        }
    }
    
    if ($action === 'reset_student2') {
        try {
            $db = (new Database())->getConnection();
            $newPass = 'student2pass';
            $hash = password_hash($newPass, PASSWORD_DEFAULT);
            $upd = $db->prepare("UPDATE users SET password = :p WHERE username = 'student2' OR email = 'student2@university.dz'");
            $upd->execute([':p' => $hash]);
            
            if ($upd->rowCount() > 0) {
                $message = '✅ تم تحديث كلمة مرور student2! استخدم: student2 / ' . $newPass;
            } else {
                $error = '❌ لم يتم العثور على student2';
            }
        } catch (Exception $e) {
            $error = '❌ خطأ: ' . $e->getMessage();
        }
    }
    
    if ($action === 'reset_sara') {
        try {
            $db = (new Database())->getConnection();
            $newPass = 'sara123';
            $hash = password_hash($newPass, PASSWORD_DEFAULT);
            $upd = $db->prepare("UPDATE users SET password = :p WHERE username = 'sara.ahmed' OR email = 'student2@university.dz' OR first_name = 'Sara'");
            $upd->execute([':p' => $hash]);
            
            if ($upd->rowCount() > 0) {
                $message = '✅ تم تحديث كلمة مرور سارة! استخدم: sara.ahmed أو البريد / ' . $newPass;
            } else {
                $error = '❌ لم يتم العثور على سارة';
            }
        } catch (Exception $e) {
            $error = '❌ خطأ: ' . $e->getMessage();
        }
    }
    
    if ($action === 'list_users') {
        try {
            $db = (new Database())->getConnection();
            $stmt = $db->prepare("SELECT id, username, email, first_name, last_name, role FROM users LIMIT 50");
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $message = '<pre style="background:#f3f3f3; padding:12px; border-radius:6px; overflow-x:auto; font-size:12px;">';
            $message .= "ID | Username | Email | First | Last | Role\n";
            $message .= str_repeat('-', 100) . "\n";
            foreach ($rows as $r) {
                $message .= sprintf("%3d | %-20s | %-25s | %-10s | %-10s | %10s\n",
                    $r['id'], $r['username'], $r['email'], $r['first_name'], $r['last_name'], $r['role']);
            }
            $message .= '</pre>';
        } catch (Exception $e) {
            $error = '❌ خطأ: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>أداة الدخول - نظام الحضور</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family: Arial, sans-serif; background: linear-gradient(90deg, #5867ff, #9b5cff); min-height:100vh; display:flex; align-items:center; justify-content:center; padding:20px; }
        .container { background:white; border-radius:14px; padding:32px; box-shadow: 0 20px 50px rgba(11,22,60,0.18); max-width:500px; width:100%; }
        h1 { color:#3b5bff; font-size:24px; margin-bottom:24px; text-align:center; }
        .form-row { margin:16px 0; }
        button { width:100%; padding:12px; border:none; border-radius:8px; font-weight:700; cursor:pointer; transition:all 160ms ease; font-size:14px; }
        .btn-primary { background:linear-gradient(90deg, #3b5bff, #9b5cff); color:white; }
        .btn-primary:hover { transform:translateY(-2px); box-shadow: 0 10px 20px rgba(59,91,255,0.2); }
        .btn-secondary { background:#eef2ff; color:#3b5bff; border:1px solid #3b5bff; margin-bottom:8px; }
        .btn-secondary:hover { background:#dde7ff; }
        .message { padding:12px; border-radius:6px; margin-bottom:16px; font-size:14px; }
        .success { background:#d1fae5; color:#065f46; border:1px solid #a7f3d0; }
        .error { background:#fee2e2; color:#b91c1c; border:1px solid #fca5a5; }
        hr { margin:20px 0; border:none; border-top:1px solid #e5e7eb; }
        .info { font-size:12px; color:#666; margin-top:8px; background:#f9fafb; padding:8px; border-radius:4px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔓 أداة الدخول السريعة</h1>
        
        <?php if ($message): ?>
            <div class="message success"><?= $message ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="message error"><?= $error ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-row">
                <button type="submit" name="action" value="create_test_student" class="btn-primary">
                    ✅ إنشاء حساب اختبار
                </button>
                <div class="info">اسم: teststudent | كلمة المرور: test123</div>
            </div>
            
            <div class="form-row">
                <button type="submit" name="action" value="reset_student2" class="btn-secondary">
                    🔄 تحديث student2
                </button>
                <div class="info">الكلمة الجديدة: student2pass</div>
            </div>
            
            <div class="form-row">
                <button type="submit" name="action" value="reset_sara" class="btn-secondary">
                    🔄 تحديث سارة أحمد
                </button>
                <div class="info">الكلمة الجديدة: sara123</div>
            </div>
            
            <div class="form-row">
                <button type="submit" name="action" value="list_users" class="btn-secondary">
                    📋 عرض جميع المستخدمين
                </button>
            </div>
        </form>
        
        <hr>
        
        <div style="text-align:center; font-size:12px; color:#666;">
            بعد إنشاء الحساب، اذهب إلى:<br>
            <strong style="color:#3b5bff;">http://localhost/attendance_system/public/?page=login</strong>
        </div>
    </div>
</body>
</html>
