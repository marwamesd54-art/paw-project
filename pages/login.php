<?php
// restructured/pages/login.php
// Full-page styled login (uses prototype CSS)
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Connexion — Système d'Assiduité</title>
  <link rel="stylesheet" href="/attendance_system/public/assets/css/style.css">
  <style>
    /* Slight overrides to center the login as in the screenshot */
    body{ background: linear-gradient(90deg,#5867ff 0%, #9b5cff 100%); height:100vh; display:flex; align-items:center; justify-content:center; }
    .login-card{ width:520px; max-width:94%; border-radius:14px; padding:34px; box-shadow:0 20px 50px rgba(11,22,60,0.18); background:white; }
    .brand-title{ color:var(--primary); font-weight:800; font-size:24px; text-align:center; }
    .subtle{ text-align:center; color:var(--muted); margin-top:8px; }
    .input{ width:100%; padding:14px 12px; border-radius:8px; border:1px solid #e6eefc; background:#f3f8ff; }
    label{ display:block; font-weight:600; margin-bottom:6px; color:#374151; }
    .form-row{ margin:18px 0; }
    .login-cta{ display:flex; justify-content:center; margin-top:8px; }
  </style>
</head>
<body>
  <div class="login-card">
    <div style="text-align:center;">
      <div style="font-size:28px;">🎓</div>
      <div class="brand-title">Système d'Assiduité</div>
      <div class="subtle">Université d'Alger</div>
    </div>

    <form id="loginForm" method="post" action="/attendance_system/api/login.php">
      <div class="form-row">
        <label for="username">Nom d'utilisateur</label>
        <input id="username" name="username" class="input" required value="">
      </div>
      <div class="form-row">
        <label for="password">Mot de passe</label>
        <input id="password" name="password" type="password" class="input" required>
      </div>
      <div class="login-cta">
        <button type="submit" class="btn btn-primary">Se Connecter</button>
      </div>
      <div id="loginMsg" style="margin-top:12px; color:#b91c1c; text-align:center;"></div>
    </form>
  </div>

  <script>
    // AJAX login with redirect on success
    (function(){
      const form = document.getElementById('loginForm');
      form.addEventListener('submit', function(e){
        e.preventDefault();
        const fd = new FormData(form);
        fetch(form.action, { method: 'POST', body: fd, credentials: 'same-origin' })
          .then(r => r.json())
          .then(data => {
            if (data && data.success) {
              // Redirect based on role
              const role = data.user.role.toLowerCase();
              if (role === 'admin') {
                window.location.href = '/attendance_system/public/?page=adminHome';
              } else if (role === 'professor' || role === 'professeur') {
                window.location.href = '/attendance_system/public/?page=professorHome';
              } else if (role === 'student' || role === 'étudiant') {
                window.location.href = '/attendance_system/public/?page=studentHome';
              } else {
                window.location.href = '/attendance_system/public/';
              }
            } else {
              document.getElementById('loginMsg').innerText = data.message || 'Échec de connexion';
            }
          })
          .catch(err => { document.getElementById('loginMsg').innerText = 'Erreur serveur'; });
      });
    })();
  </script>
</body>
</html>
