<?php
// restructured/pages/studentManagement.php
// Only admin role can access this page
if ($_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo '<main class="container"><div class="card"><h2>⛔ Accès Refusé</h2><p>Vous n\'avez pas la permission d\'accéder à cette page.</p></div></main>';
    exit;
}

require_once __DIR__ . '/../config/db.php';

$db = (new Database())->getConnection();

// Get all students
try {
    $stmt = $db->query("SELECT id, username, email, first_name, last_name, group_name FROM users WHERE role = 'student' ORDER BY last_name, first_name");
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $students = [];
}
?>
<main class="container">
  <div class="card">
    <h2>👥 Gestion des Étudiants</h2>
    <div class="controls">
      <a id="importExcelBtn" class="btn btn-primary" href="#"> Importer Excel</a>
      <a id="exportExcelBtn" class="btn btn-success" href="#"> Exporter CSV</a>
      <a id="addStudentBtn" class="btn btn-warning" href="#">➕ Ajouter Étudiant</a>
      <a id="bulkActionsBtn" class="btn btn-danger" href="#"> Actions Groupées</a>
      <input id="importFile" type="file" accept=".csv" style="display:none">
    </div>

    <style>
      :root { --primary: #3b5bff; --accent: #9b5cff; --card: #ffffff; --radius:14px; --shadow:0 10px 30px rgba(11,22,60,0.06); }
      .table-wrap { overflow-x:auto; margin-top:16px; }
      .students-card { background:var(--card); padding:16px; border-radius:var(--radius); box-shadow:var(--shadow); }
      table.students-table { width:100%; border-collapse:collapse; min-width:900px; }
      table.students-table thead th { background: linear-gradient(90deg,var(--accent),var(--primary)); color:#fff; padding:12px 10px; text-align:left; font-weight:700; font-size:13px; }
      table.students-table tbody td { padding:12px 10px; border-bottom:1px solid #f0f4ff; color:#0f1724; }
      table.students-table tr:hover { background:#f8fbff; }
      .controls .btn { box-shadow: 0 6px 18px rgba(59,91,255,0.08); }
      .btn-ghost{ background:#f8fafc; color:#374151; border:1px solid #e5e7eb; padding:6px 10px; border-radius:8px; }
      .edit-btn, .delete-btn { padding:6px 8px; font-size:14px; }
      @media (max-width:800px){ table.students-table { min-width:700px; } }
    </style>
    <?php if (count($students) > 0): ?>
    <div class="table-wrap students-card">
      <table class="students-table">
        <thead><tr><th>ID</th><th>Nom</th><th>Prénom</th><th>Email</th><th>Groupe</th><th>Actions</th></tr></thead>
        <tbody>
          <?php foreach ($students as $student): ?>
          <tr>
            <td><?= $student['id'] ?></td>
            <td><?= htmlspecialchars($student['last_name']) ?></td>
            <td><?= htmlspecialchars($student['first_name']) ?></td>
            <td><?= htmlspecialchars($student['email']) ?></td>
            <td><?= htmlspecialchars($student['group_name'] ?? '-') ?></td>
            <td><a class="btn btn-ghost edit-btn" href="#">✏️</a> <a class="btn btn-danger delete-btn" href="#">🗑️</a></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <style>
      /* Use global variables where available; fallback values kept for safety */
      :root { --site-primary: var(--primary, #3b5bff); --site-accent: var(--accent, #9b5cff); --card: var(--card, #fff); --radius:14px; --shadow:0 10px 30px rgba(11,22,60,0.06); }

      .table-wrap { overflow-x:auto; margin-top:18px; }
      .students-card { background:var(--card); padding:18px; border-radius:var(--radius); box-shadow:var(--shadow); }

      table.students-table { width:100%; border-collapse:collapse; min-width:900px; font-family:inherit; }
      table.students-table thead th { background: linear-gradient(90deg,var(--site-accent),var(--site-primary)); color:#fff; padding:12px 14px; text-align:left; font-weight:700; font-size:14px; border: none; }
      table.students-table thead th:first-child { border-top-left-radius:12px; }
      table.students-table thead th:last-child { border-top-right-radius:12px; }

      table.students-table tbody td { padding:12px 14px; border-bottom:1px solid #f0f4ff; color:#0f1724; vertical-align:middle; }
      table.students-table tbody tr:hover { background:#f8fbff; }

      /* Controls styles to match site buttons */
      .controls .btn { display:inline-flex; align-items:center; gap:8px; padding:10px 16px; border-radius:10px; font-weight:700; box-shadow:0 8px 24px rgba(16,32,96,0.08); }
      .controls .btn-primary { background: linear-gradient(90deg,var(--site-primary),var(--site-accent)); color:#fff; }
      .controls .btn-success { background: linear-gradient(90deg,#16a34a,#15803d); color:#fff; }
      .controls .btn-warning { background: linear-gradient(90deg,#f59e0b,#fbbf24); color:#fff; }
      .controls .btn-danger { background: linear-gradient(90deg,#ef4444,#dc2626); color:#fff; }

      /* Action icon buttons inside table */
      .action-icon { display:inline-flex; align-items:center; justify-content:center; width:38px; height:38px; border-radius:10px; text-decoration:none; cursor:pointer; }
      .edit-btn.action-icon { background:#fff; border:1px solid #eef2ff; color:var(--site-accent); box-shadow:0 6px 18px rgba(16,32,96,0.06); }
      .delete-btn.action-icon { background: linear-gradient(90deg,#ff7aa6,#ec4899); color:#fff; }

      /* small adjustments */
      .btn-ghost{ background:#fff; color:#374151; border:1px solid #eef2ff; padding:8px 10px; border-radius:10px; }
      @media (max-width:800px){ table.students-table { min-width:700px; } }
    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    $(function(){
      // Export table to CSV
      function tableToCSV($table){
        var rows = [];
        $table.find('tr').each(function(){
          var cols = [];
          $(this).find('th, td').each(function(){
            var txt = $(this).text().trim();
            cols.push('"' + txt.replace(/"/g,'""') + '"');
          });
          rows.push(cols.join(','));
        });
        return rows.join('\n');
      }

      $('#exportExcelBtn').on('click', function(e){
        e.preventDefault();
        var csv = tableToCSV($('table.students-table'));
        var blob = new Blob([csv], {type: 'text/csv;charset=utf-8;'});
        var url = URL.createObjectURL(blob);
        var a = document.createElement('a');
        a.href = url; a.download = 'students.csv'; document.body.appendChild(a); a.click(); a.remove(); URL.revokeObjectURL(url);
      });

      // Import CSV: simple client-side append
      $('#importExcelBtn').on('click', function(e){ e.preventDefault(); $('#importFile').click(); });
      $('#importFile').on('change', function(){
        var file = this.files[0]; if(!file) return;
        var reader = new FileReader();
        reader.onload = function(e){
          var lines = e.target.result.split(/\r?\n/).filter(Boolean);
          lines.forEach(function(line, idx){
            if(idx===0) return; // skip header
            var cols = line.split(',').map(function(c){ return c.replace(/^"|"$/g,'').trim(); });
            if(cols.length < 4) return;
            var id = cols[0] || '';
            var last = cols[1] || '';
            var first = cols[2] || '';
            var email = cols[3] || '';
            var group = cols[4] || '-';
            var row = '<tr><td>'+id+'</td><td>'+escapeHtml(last)+'</td><td>'+escapeHtml(first)+'</td><td>'+escapeHtml(email)+'</td><td>'+escapeHtml(group)+'</td><td><a class="btn btn-ghost edit-btn" href="#">✏️</a> <a class="btn btn-danger delete-btn" href="#">🗑️</a></td></tr>';
            $('table tbody').append(row);
          });
          alert('Import terminé (client-side).');
          $('#importFile').val('');
        };
        reader.readAsText(file);
      });

      // Add student (client-side prompt + server call)
      $('#addStudentBtn').on('click', function(e){
        e.preventDefault();
        var username = prompt('Username (unique identifier, e.g. jdoe):'); if(!username) return;
        var last = prompt('Nom (last name):'); if(!last) return;
        var first = prompt('Prénom (first name):'); if(!first) return;
        var email = prompt('Email (optional):');

        fetch('../api/admin_create_student.php', {
          method: 'POST', headers: {'Content-Type':'application/json'},
          body: JSON.stringify({ username: username, lastName: last, firstName: first, email: email })
        }).then(r => r.json()).then(function(json){
          if(json && json.success){
            var id = json.student_db_id || 'N/A';
            var row = '<tr><td>'+id+'</td><td>'+escapeHtml(last)+'</td><td>'+escapeHtml(first)+'</td><td>'+escapeHtml(email||'')+'</td><td>-</td><td><a class="btn btn-ghost edit-btn" href="#">✏️</a> <a class="btn btn-danger delete-btn" href="#">🗑️</a></td></tr>';
            $('table tbody').append(row);
            alert('✅ Étudiant créé et ajouté.');
          } else {
            alert('Erreur: ' + (json.message || 'server error'));
          }
        }).catch(function(){ alert('Erreur réseau lors de la création du compte.'); });
      });

      // Delegate edit/delete
      $('table').on('click', '.delete-btn', function(e){
        e.preventDefault();
        if(!confirm('Confirmer la suppression de cet étudiant ?')) return;
        var $tr = $(this).closest('tr');
        var sid = $tr.find('td').eq(0).text().trim();
        if(!sid || sid === 'N/A') { $tr.remove(); return; }

        fetch('../api/delete_student.php', {
          method: 'POST', headers: {'Content-Type':'application/json'},
          body: JSON.stringify({ student_id: sid })
        }).then(r => r.json()).then(function(json){
          if(json && json.success){
            $tr.remove();
            alert('✅ Étudiant supprimé.');
          } else {
            alert('Erreur: ' + (json.message || 'server error'));
          }
        }).catch(function(){ alert('Erreur réseau lors de la suppression.'); });
      });

      $('table').on('click', '.edit-btn', function(e){
        e.preventDefault();
        var $tr = $(this).closest('tr');
        var last = $tr.find('td').eq(1).text();
        var first = $tr.find('td').eq(2).text();
        var email = $tr.find('td').eq(3).text();
        var newLast = prompt('Modifier Nom:', last); if(newLast===null) return;
        var newFirst = prompt('Modifier Prénom:', first); if(newFirst===null) return;
        var newEmail = prompt('Modifier Email:', email); if(newEmail===null) return;
        $tr.find('td').eq(1).text(newLast); $tr.find('td').eq(2).text(newFirst); $tr.find('td').eq(3).text(newEmail);
        alert('Modifications appliquées (client-side).');
      });

      // Bulk actions placeholder
      $('#bulkActionsBtn').on('click', function(e){ e.preventDefault(); alert('Actions groupées: fonctionnalité client-side simple (sélection non implémentée).'); });

      function escapeHtml(text){ return String(text).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
    });
    </script>

        <?php else: ?>
        <div style="padding:20px; background:#f3f4f6; border-radius:8px; text-align:center;">
          <p style="color:var(--muted);">Aucun étudiant trouvé.</p>
        </div>
        <?php endif; ?>
      </div>
    </main>
