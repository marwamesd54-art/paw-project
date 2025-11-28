<?php
// restructured/pages/professorHome.php
// Only professor role can access this page
if ($_SESSION['role'] !== 'professor' && $_SESSION['role'] !== 'professeur') {
    http_response_code(403);
    echo '<main class="container"><div class="card"><h2>⛔ Accès Refusé</h2><p>Vous n\'avez pas la permission d\'accéder à cette page.</p></div></main>';
    exit;
}

require_once __DIR__ . '/../config/db.php';

$db = (new Database())->getConnection();

// Get professor's courses
try {
    $stmt = $db->prepare("SELECT c.id, c.course_name, c.course_code, c.group_name, COUNT(DISTINCT e.student_id) as student_count
                          FROM courses c
                          LEFT JOIN enrollments e ON c.id = e.course_id
                          WHERE c.professor_id = :professor_id
                          GROUP BY c.id
                          ORDER BY c.course_name");
    $stmt->bindParam(':professor_id', $_SESSION['user_id']);
    $stmt->execute();
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $courses = [];
}
?>
<main class="container">
  <div class="card">
    <h2>👨‍🏫 Mes Cours</h2>
    <p style="color: #666; margin-bottom: 20px;">Bienvenue, <strong><?= htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']) ?></strong></p>

    <?php if (count($courses) > 0): ?>
    <div class="courses-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; margin-top: 20px;">
      <?php foreach ($courses as $course): ?>
      <div class="course-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); cursor: pointer; transition: transform 0.2s, box-shadow 0.2s;" onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 8px 25px rgba(0,0,0,0.2)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(0,0,0,0.1)'">
        <h3 style="margin-bottom: 10px; font-size: 18px;"><?= htmlspecialchars($course['course_name']) ?></h3>
        <p style="font-size: 14px; opacity: 0.9; margin: 5px 0;">📌 <?= htmlspecialchars($course['course_code']) ?></p>
        <p style="font-size: 14px; opacity: 0.9; margin: 5px 0;">👥 Groupe: <?= htmlspecialchars($course['group_name']) ?></p>
        <p style="font-size: 14px; opacity: 0.9; margin: 5px 0;">📊 <?= $course['student_count'] ?> étudiants inscrits</p>
        
        <div style="margin-top: 15px; display: flex; gap: 10px;">
          <button class="btn btn-light" style="flex: 1; padding: 8px; border: none; border-radius: 6px; background: white; color: #667eea; font-weight: bold; cursor: pointer;" onclick="window.location.href='?page=sessionManagement&course_id=<?= $course['id'] ?>'">📝 Sessions</button>
          <button class="btn btn-light" style="flex: 1; padding: 8px; border: none; border-radius: 6px; background: white; color: #667eea; font-weight: bold; cursor: pointer;" onclick="window.location.href='?page=attendanceSummary&course_id=<?= $course['id'] ?>'">📊 Résumé</button>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div style="padding: 20px; background: #fef3c7; border-radius: 8px; border-left: 4px solid #f59e0b;">
      <p>📚 Aucun cours assigné. Contactez l'administrateur.</p>
      <div style="margin-top:12px;">
        <button id="seedDataBtn" class="btn btn-primary" style="padding:10px 14px; border-radius:6px;">➕ Remplir des données de test</button>
        <small style="display:block; color:#555; margin-top:8px;">(Crée une course, 6 sessions, 3 étudiants et des présences pour démonstration.)</small>
      </div>
    </div>
    <?php endif; ?>
  </div>
</main>

<script>
  document.getElementById && document.getElementById('seedDataBtn') && document.getElementById('seedDataBtn').addEventListener('click', function(){
    if (!confirm('Créer des données de test pour votre compte ? Cela ajoutera une course, sessions et étudiants.')) return;
    const btn = this;
    btn.disabled = true;
    btn.textContent = '⏳ Création en cours...';

    fetch('/attendance_system/api/seed_sample.php', { method: 'POST', credentials: 'same-origin' })
    <!-- Quick Interface moved to sessionManagement.php -->
          $('#stuId,#stuLast,#stuFirst,#stuEmail').val('');
          // recalc
          $('#attTable tbody tr').last().each(function(){ recalcRow($(this)); });
          setTimeout(()=>$('#addSuccess').fadeOut(),2500);
        } else {
          alert('Error adding student: ' + (res.message || 'Unknown'));
        }
      }).catch(err => { console.error(err); alert('Network error'); }).finally(()=>{ btn.prop('disabled', false).text('Add Student'); });
    });

    // Show Report
    let chart = null;
    $('#showReport').on('click', function(){
      const rows = $('#attTable tbody tr');
      const total = rows.length; let presentCount=0, participated=0;
      rows.each(function(){ const abs = parseInt($(this).find('.abs-cell').text()||'0',10); if(abs===0) presentCount++; const partCount = parseInt($(this).find('.part-count').text()||'0',10); if(partCount>0) participated++; });
      $('#reportSummary').html('<b>Total:</b> '+total+' — <b>All Present:</b> '+presentCount+' — <b>Participated:</b> '+participated);

      const ctx = document.getElementById('reportChart').getContext('2d');
      const data = { labels:['Present (no abs)','With Participation','Others'], datasets:[{ label:'Students', data:[presentCount, participated, Math.max(0,total-presentCount)], backgroundColor:['#16a34a','#0284c7','#f59e0b'] }] };
      if(chart) chart.destroy();
      chart = new Chart(ctx, { type:'bar', data: data, options:{ responsive:true, maintainAspectRatio:false } });
    });

    // Highlight Excellent Students
    $('#highlightExcellent').on('click', function(){ $('#attTable tbody tr').each(function(){ const abs = parseInt($(this).find('.abs-cell').text()||'0',10); if(abs<3){ $(this).animate({ backgroundColor:'#d1fae5' }, 600).animate({ backgroundColor:'' }, 600); } }); });
    $('#resetColors').on('click', function(){ $('#attTable tbody tr').css('background',''); });

    // Search
    $('#searchName').on('input', function(){ const q = $(this).val().toLowerCase(); $('#attTable tbody tr').each(function(){ const last = $(this).find('td').eq(0).text().toLowerCase(); const first = $(this).find('td').eq(1).text().toLowerCase(); const show = last.indexOf(q)!==-1 || first.indexOf(q)!==-1; $(this).toggle(show); }); });

    // Sort functions
    function sortRows(compareFn){ const rows = $('#attTable tbody tr').get(); rows.sort(compareFn); $('#attTable tbody').append(rows); }
    $('#sortAbsBtn').on('click', function(){ sortRows(function(a,b){ return parseInt($(a).find('.abs-cell').text()||'0') - parseInt($(b).find('.abs-cell').text()||'0'); }); });
    $('#sortPartBtn').on('click', function(){ sortRows(function(a,b){ return parseInt($(b).find('.part-count').text()||'0') - parseInt($(a).find('.part-count').text()||'0'); }); });

  })();
</script>
