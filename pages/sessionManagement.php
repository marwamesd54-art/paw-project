<?php
// restructured/pages/sessionManagement.php
// Only professor role can access this page
if ($_SESSION['role'] !== 'professor' && $_SESSION['role'] !== 'professeur') {
    http_response_code(403);
    echo '<main class="container"><div class="card"><h2>⛔ Accès Refusé</h2><p>Vous n\'avez pas la permission d\'accéder à cette page.</p></div></main>';
    exit;
}

require_once __DIR__ . '/../config/db.php';

$db = (new Database())->getConnection();
$course_id = $_GET['course_id'] ?? null;
$session_id = $_GET['session_id'] ?? null;

// Get course info
if ($course_id) {
    try {
        $stmt = $db->prepare("SELECT id, course_name, course_code FROM courses WHERE id = :course_id AND professor_id = :professor_id");
        $stmt->bindParam(':course_id', $course_id);
        $stmt->bindParam(':professor_id', $_SESSION['user_id']);
        $stmt->execute();
        $courseInfo = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $courseInfo = null;
    }
} else {
    $courseInfo = null;
}

// Get sessions for this course
$sessions = [];
if ($course_id && $courseInfo) {
    try {
        $stmt = $db->prepare("SELECT id, session_number, session_date, topic, is_open 
                              FROM sessions 
                              WHERE course_id = :course_id 
                              ORDER BY session_date DESC");
        $stmt->bindParam(':course_id', $course_id);
        $stmt->execute();
        $sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $sessions = [];
    }
}

    // Get students enrolled in this course (ensure $students is always defined)
    $students = [];
    if ($course_id && $courseInfo) {
      try {
        $stmt = $db->prepare("SELECT u.id, u.first_name, u.last_name, u.username
                    FROM users u
                    JOIN enrollments e ON e.student_id = u.id
                    WHERE e.course_id = :course_id
                    ORDER BY u.last_name, u.first_name");
        $stmt->bindParam(':course_id', $course_id);
        $stmt->execute();
        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
      } catch (Exception $e) {
        $students = [];
      }
    }
?>
<main class="container">
  <div class="card">

    <?php if (!$course_id || !$courseInfo): ?>
      <div style="padding:20px; background:#fef3c7; border-radius:8px; border-left:4px solid #5763caff;">
        <p>ℹ️ Sélectionnez un cours pour gérer les sessions et présences.</p>
        <p><a class="btn btn-primary" href="?page=professorHome">↩️ Retour à Mes Cours</a></p>
      </div>
    <?php else: ?>

      <?php
        // prepare lastSessions & attendanceMap for the table
        $lastSessions = array_slice($sessions, 0, 6);
        $attendanceMap = [];
        if (count($lastSessions) > 0 && count($students) > 0) {
          $sessionIds = array_column($lastSessions, 'id');
          $studentIds = array_column($students, 'id');
          $sessPlaceholders = implode(',', array_fill(0, count($sessionIds), '?'));
          $studPlaceholders = implode(',', array_fill(0, count($studentIds), '?'));
          try {
            $sql = "SELECT student_id, session_id, status, participation FROM attendance_records WHERE session_id IN ($sessPlaceholders) AND student_id IN ($studPlaceholders)";
            $stmt = $db->prepare($sql);
            $stmt->execute(array_merge($sessionIds, $studentIds));
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as $r) {
              $attendanceMap[$r['student_id']][$r['session_id']] = ['status' => $r['status'], 'participation' => (int)$r['participation']];
            }
          } catch (Exception $e) { /* ignore */ }
        }
      ?>

      <!-- include CDN jQuery -->
      <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

      <style>
        /* Modern design matching site theme */
        :root {
          --primary: #3b5bff;
          --accent: #9b5cff;
          --bg: #f4f7fb;
          --success: #e6fff0;
          --warn: #fff7e6;
          --danger: #fff0f2;
          --radius: 14px;
          --shadow: 0 10px 30px rgba(11,22,60,0.06);
        }
        
        h1 { 
          text-align:center; 
          color:#0f1724; 
          font-weight:700; 
          margin:20px 0; 
          font-size:28px;
        }
        
        .att-wrapper {
          background: white;
          border-radius: var(--radius);
          box-shadow: var(--shadow);
          padding: 20px;
          margin: 20px 0;
          overflow-x: auto;
        }
        
        table.att { 
          border-collapse:collapse; 
          width:100%; 
          background:white;
          min-width: 1000px;
        }
        
        table.att th { 
          background: #41229798; /* exact color requested */
          color: white;
          padding: 14px 8px;
          text-align: center;
          font-weight: 600;
          font-size: 13px;
          border: none;
        }
        
        table.att td { 
          border-bottom: 1px solid #f0f4ff;
          padding: 12px 8px;
          text-align: center;
          font-size: 14px;
        }
        
        table.att tbody tr {
          transition: all 0.3s ease;
        }
        
        table.att tbody tr:hover {
          background: #f8fbff;
        }
        
        tr.green { 
          background: #f0fff4;
          border-left: 4px solid #16a34a;
        }
        
        tr.yellow { 
          background: #fefce8;
          border-left: 4px solid #f59e0b;
        }
        
        tr.red { 
          background: #fef2f2;
          border-left: 4px solid #dc2626;
        }
        
        .att-cell, .part-cell {
          cursor: pointer;
          text-align: center;
          font-weight: 600;
          color: #0f1724;
          border-radius: 6px;
          transition: all 0.2s ease;
          padding: 10px !important;
        }
        
        .att-cell:hover, .part-cell:hover {
          background: #f0f4ff;
          transform: scale(1.05);
        }
        
        .abs-count, .part-count {
          font-weight: 600;
          color: #374151;
        }
        
        .msg-cell {
          text-align: left;
          color: #5f6771;
          font-size: 13px;
          font-weight: 500;
        }
        
        form.center { 
          margin: 30px auto; 
          max-width: 450px;
          background: white;
          padding: 28px;
          border-radius: var(--radius);
          box-shadow: var(--shadow);
        }
        
        form.center h2 {
          text-align: center;
          color: #0f1724;
          margin-top: 0;
          font-size: 22px;
          font-weight: 700;
        }
        
        form.center label { 
          display: block;
          margin-top: 16px;
          font-weight: 600;
          color: #374151;
          font-size: 14px;
        }
        
        form.center input { 
          width: 100%;
          padding: 11px 14px;
          margin-top: 6px;
          border: 1px solid #e5e7eb;
          border-radius: 8px;
          font-size: 14px;
          transition: all 0.3s ease;
          box-sizing: border-box;
        }
        
        form.center input:focus {
          outline: none;
          border-color: var(--primary);
          box-shadow: 0 0 0 3px rgba(59, 91, 255, 0.1);
        }
        
        form.center small { 
          color: #dc2626;
          display: none;
          margin-top: 4px;
          font-size: 12px;
          font-weight: 500;
        }
        
        form.center button { 
          margin-top: 22px;
          padding: 12px 24px;
          border: none;
          background: linear-gradient(90deg, var(--primary), var(--accent));
          color: white;
          border-radius: 8px;
          cursor: pointer;
          width: 100%;
          font-weight: 600;
          font-size: 15px;
          transition: all 0.3s ease;
        }
        
        form.center button:hover { 
          transform: translateY(-2px);
          box-shadow: 0 6px 20px rgba(59, 91, 255, 0.25);
        }
        
        .report { 
          margin-top: 40px;
          background: white;
          padding: 28px;
          border-radius: var(--radius);
          box-shadow: var(--shadow);
          text-align: center;
        }
        
        .report h2 {
          color: #0f1724;
          font-size: 22px;
          font-weight: 700;
          margin-top: 0;
        }
        
        .report p {
          color: #5f6771;
          font-size: 15px;
          margin: 12px 0;
        }
        
        .report b {
          color: var(--primary);
          font-weight: 700;
          font-size: 18px;
        }
        
        .controls { 
          text-align: center;
          margin-top: 30px;
          margin-bottom: 30px;
          display: flex;
          gap: 12px;
          flex-wrap: wrap;
          justify-content: center;
        }
        
        .controls button { 
          margin: 0;
          padding: 12px 24px;
          border: none;
          background: linear-gradient(90deg, var(--primary), var(--accent));
          color: white;
          border-radius: 8px;
          cursor: pointer;
          font-weight: 600;
          font-size: 14px;
          transition: all 0.3s ease;
        }
        
        .controls button:hover { 
          transform: translateY(-2px);
          box-shadow: 0 6px 20px rgba(59, 91, 255, 0.25);
        }
        
        /* showReportBtn removed (report is on attendanceSummary.php) */
        
        canvas { 
          margin-top: 30px;
          max-width: 400px;
          margin-left: auto;
          margin-right: auto;
        }
        
        #reportSection {
          background: linear-gradient(135deg, #f0fff4, #f0f9ff);
          padding: 20px;
          border-radius: 12px;
          border-left: 4px solid var(--primary);
          margin-top: 20px;
        }
      </style>

      <h1 style="text-align:center;">Attendance Management</h1>

      <!-- Attendance Table with horizontal scroll for all columns -->
      <div style="overflow-x:auto; margin:20px 0;">
      <table id="attendanceTable" class="att" style="min-width:1200px;">
        <thead>
          <tr>
            <th>Last Name</th>
            <th>First Name</th>
            <?php for ($i=1;$i<=6;$i++): ?><th>S<?= $i ?></th><?php endfor; ?>
            <?php for ($i=1;$i<=6;$i++): ?><th>P<?= $i ?></th><?php endfor; ?>
            <th style="width:80px;">Absences</th>
            <th style="width:100px;">Participation</th>
            <th>Message</th>
          </tr>
        </thead>
        <tbody>
          <?php if (count($students) === 0): ?>
            <tr><td colspan="18" style="padding:12px; text-align:center; color:#999;">No students enrolled in this course yet.</td></tr>
          <?php else: ?>
            <?php foreach ($students as $st): ?>
              <tr>
                <td><?= htmlspecialchars($st['last_name']) ?></td>
                <td><?= htmlspecialchars($st['first_name']) ?></td>
                <?php for ($s=0;$s<6;$s++):
                  $sessId = $lastSessions[$s]['id'] ?? null;
                  $att = $attendanceMap[$st['id']][$sessId] ?? null;
                  $present = ($att && ($att['status'] === 'present' || $att['status'] === '1')) ? '✓' : '';
                ?>
                  <td class="att-cell" style="cursor:pointer; text-align:center;"><?= $present ?></td>
                <?php endfor; ?>
                <?php for ($p=0;$p<6;$p++):
                  $sessId = $lastSessions[$p]['id'] ?? null;
                  $att = $attendanceMap[$st['id']][$sessId] ?? null;
                  $part = ($att && ($att['participation'] && (int)$att['participation'] > 0)) ? '✓' : '';
                ?>
                  <td class="part-cell" style="cursor:pointer; text-align:center;"><?= $part ?></td>
                <?php endfor; ?>
                <td class="abs-count" style="text-align:center;">6 Abs</td>
                <td class="part-count" style="text-align:center;">0 Par</td>
                <td class="msg-cell">Excluded – too many absences – You need to participate more</td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
      </div>

      <!-- Add Student Form -->
      <form id="addStudentForm" class="center">
        <h2 style="text-align:center;">Add New Student</h2>
        <label>Student ID:<input type="text" id="studentId"></label><small id="idError">ID must contain only numbers</small>
        <label>Last Name:<input type="text" id="lastName"></label><small id="lastError">Only letters allowed</small>
        <label>First Name:<input type="text" id="firstName"></label><small id="firstError">Only letters allowed</small>
        <label>Email:<input type="text" id="email"></label><small id="emailError">Invalid email format</small>
        <div style="text-align:center;"><button type="submit">Add Student</button></div>
      </form>

      <!-- Quick link to Attendance Summary (report page) -->
      <div class="report" style="text-align:center; margin-top:18px;">
        <a class="btn btn-primary" href="?page=attendanceSummary&course_id=<?= urlencode($course_id) ?>">📊 View Attendance Summary</a>
      </div>

      <div class="controls">
        <button id="highlightBtn">Highlight Excellent Students</button>
        <button id="resetBtn">Reset Colors</button>
      </div>

      <script>
      $(function(){
        // ===== EXERCISE 1: Count absences and participations, highlight rows, display messages =====
        function updateAttendance(){
          $('#attendanceTable tbody tr').each(function(){
            const cells = $(this).find('td');
            let presentCount = 0;
            let participationCount = 0;

            // Count S columns (indices 2-7 for S1-S6)
            for(let i = 2; i < 8; i++) {
              if($(cells[i]).text().trim() === '✓') presentCount++;
            }

            // Count P columns (indices 8-13 for P1-P6)
            for(let i = 8; i < 14; i++) {
              if($(cells[i]).text().trim() === '✓') participationCount++;
            }

            const absences = 6 - presentCount;

            // Update Absences column (index 14)
            $(cells[14]).text(absences + ' Abs');

            // Update Participation column (index 15)
            $(cells[15]).text(participationCount + ' Par');

            // Remove all row color classes
            $(this).removeClass('green yellow red');

            // Apply color based on absences
            if(absences < 3) {
              $(this).addClass('green');
            } else if(absences < 5) {
              $(this).addClass('yellow');
            } else {
              $(this).addClass('red');
            }

            // Create message based on absences and participation
            let msg = '';
            if(absences >= 5) {
              msg = 'Excluded – too many absences';
            } else if(absences >= 3) {
              msg = 'Warning – attendance low';
            } else {
              msg = 'Good attendance';
            }

            if(participationCount > 3) {
              msg += ' – Excellent participation';
            } else {
              msg += ' – You need to participate more';
            }

            // Update Message column (index 16)
            $(cells[16]).text(msg);
          });
        }

        // Initial update
        updateAttendance();

        // Click handler for attendance cells (S columns)
        $(document).on('click', '#attendanceTable .att-cell', function(){
          const text = $(this).text().trim();
          $(this).text(text === '✓' ? '' : '✓');
          updateAttendance();
        });

        $(document).on('click', '#attendanceTable .part-cell', function(){
          const text = $(this).text().trim();
          $(this).text(text === '✓' ? '' : '✓');
          updateAttendance();
        });

        // ===== EXERCISE 2 & 3: Form validation and add student to table =====
        $('#addStudentForm').on('submit', function(e){
          e.preventDefault();

          const id = $('#studentId').val().trim();
          const last = $('#lastName').val().trim();
          const first = $('#firstName').val().trim();
          const email = $('#email').val().trim();

          let valid = true;

          // Hide all error messages initially
          $('#idError, #lastError, #firstError, #emailError').hide();

          // Validate Student ID (numbers only)
          if(!/^[0-9]+$/.test(id) || id === '') {
            $('#idError').show();
            valid = false;
          }

          // Validate Last Name (letters only)
          if(!/^[A-Za-z]+$/.test(last) || last === '') {
            $('#lastError').show();
            valid = false;
          }

          // Validate First Name (letters only)
          if(!/^[A-Za-z]+$/.test(first) || first === '') {
            $('#firstError').show();
            valid = false;
          }

          // Validate Email format
          if(email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            $('#emailError').show();
            valid = false;
          }

          if(!valid) return;

          // Add new row to table with empty attendance cells (with proper classes)
          let newRow = '<tr><td>' + escapeHtml(last) + '</td><td>' + escapeHtml(first) + '</td>';
          for(let i = 0; i < 6; i++) {
            newRow += '<td class="att-cell" style="cursor:pointer;"></td>';
          }
          for(let i = 0; i < 6; i++) {
            newRow += '<td class="part-cell" style="cursor:pointer;"></td>';
          }
          newRow += '<td></td><td></td><td></td></tr>';

          $('#attendanceTable tbody').append(newRow);
          updateAttendance();
          alert('✅ Student added successfully!');
          this.reset();
        });

        // ===== EXERCISE 4: Show Report with Chart =====
        // Report functionality removed from this page; it now lives in `attendanceSummary.php`.

        // ===== EXERCISE 5: Row hover and click effects =====
        $('#attendanceTable tbody').on('mouseenter', 'tr', function(){
          // Highlight on hover
          $(this).css('background-color', '#e0f7fa');
        }).on('mouseleave', 'tr', function(){
          // Remove highlight and restore original color
          updateAttendance();
        }).on('click', 'tr', function(){
          // Display student info on click
          const lastNameCell = $(this).find('td').eq(0);
          const firstNameCell = $(this).find('td').eq(1);
          const absencesCell = $(this).find('td').eq(14);

          const lastName = lastNameCell.text() || '?';
          const firstName = firstNameCell.text() || '?';
          const absences = absencesCell.text() || '0';

          alert('Student: ' + lastName + ' ' + firstName + '\nAbsences: ' + absences);
        });

        // ===== EXERCISE 6: Highlight Excellent Students & Reset Colors =====
        $('#highlightBtn').on('click', function(){
          $('#attendanceTable tbody tr').each(function(){
            const absencesCell = $(this).find('td').eq(14);
            const abs = parseInt(absencesCell.text()) || 0;

            // Find students with fewer than 3 absences
            if(abs < 3) {
              // Animate with fade in/out
              $(this).fadeOut(300).fadeIn(300);
            }
          });
        });

        $('#resetBtn').on('click', function(){
          // Stop all animations and remove inline styles
          $('#attendanceTable tbody tr').stop(true, true).css('background', '');
          // Restore original colors based on attendance
          updateAttendance();
        });

        // Helper function to escape HTML
        function escapeHtml(text) {
          const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
          };
          return text.replace(/[&<>"']/g, function(m) { return map[m]; });
        }
      });
      </script>

    <?php endif; ?>
  </div>
</main>
