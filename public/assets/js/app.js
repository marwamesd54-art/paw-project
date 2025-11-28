// restructured public JS (copied from prototype)
const systemState = {
  currentUser: null,
  currentRole: null,
  currentPage: 'home'
};

const demoUsers = {
  student: { name: "Ahmed Sara", role: "student" },
  professor: { name: "Dr. Bensalem Ahmed", role: "professor" },
  admin: { name: "Admin System", role: "admin" }
};

$(document).ready(function() {
  initializeLogin();
  initializeButtonHandlers();
});

function initializeLogin() {
  let selectedRole = null;
  $('.role-btn').click(function() {
    $('.role-btn').removeClass('selected');
    $(this).addClass('selected');
    selectedRole = $(this).data('role');
  });
  $('#loginBtn').click(function() {
    if (!selectedRole) { alert('Veuillez sélectionner un rôle'); return; }
    systemState.currentUser = demoUsers[selectedRole];
    systemState.currentRole = selectedRole;
    $('#loginPage').addClass('hidden'); $('#app').removeClass('hidden');
    initializeUserInterface();
  });
  $('#logoutBtn').click(function() { systemState.currentUser = null; systemState.currentRole = null; $('#app').addClass('hidden'); $('#loginPage').removeClass('hidden'); $('.role-btn').removeClass('selected'); });
}

function initializeButtonHandlers() {
  $(document).on('click', '.session-cell', function() {
    const $cell = $(this);
    if ($cell.hasClass('present')) { $cell.removeClass('present').addClass('absent').text('✗'); }
    else { $cell.removeClass('absent').addClass('present').text('✓'); }
    updateStudentStatus($cell.closest('tr'));
  });
  $('#btnHighlightExcellent').click(function() { $('.student-excellent').css('background', '#f0fff4').css('border-left', '4px solid #16a34a'); setTimeout(() => { $('.student-excellent').css('background', '').css('border-left', ''); }, 2000); });
  $('.btn-justify').click(function() { const studentName = $(this).closest('tr').find('td:first').text(); alert(`Justification d'absence pour ${studentName} - Fonctionnalité à implémenter`); });
  // Only show prototype-alerts for elements explicitly marked with .btn-debug
  $('.btn-debug').click(function() { const btnText = $(this).text(); alert(`Bouton "${btnText}" cliqué - Fonctionnalité à implémenter`); });
}

function updateStudentStatus($row) {
  const presentCount = $row.find('.session-cell.present').length;
  const totalSessions = $row.find('.session-cell').length;
  const absences = totalSessions - presentCount;
  $row.find('td').eq(9).text(absences);
  $row.removeClass('student-excellent student-good student-warning student-critical');
  if (absences === 0) { $row.addClass('student-excellent'); $row.find('td').eq(10).text('Excellent').removeClass().addClass('status-excellent'); }
  else if (absences <= 2) { $row.addClass('student-good'); $row.find('td').eq(10).text('Bon').removeClass().addClass('status-good'); }
  else if (absences <= 4) { $row.addClass('student-warning'); $row.find('td').eq(10).text('Attention').removeClass().addClass('status-warning'); }
  else { $row.addClass('student-critical'); $row.find('td').eq(10).text('Critique').removeClass().addClass('status-critical'); }
}

function initializeUserInterface() {
  const user = systemState.currentUser;
  $('#userNameDisplay').text(user.name);
  $('#roleBadge').text(user.role.toUpperCase());
  $('#currentRoleDisplay').text(`Connecté en tant que ${user.role}`);
  $(`#studentPages, #professorPages, #adminPages`).addClass('hidden');
  $(`#${user.role}Pages`).removeClass('hidden');
  showPage(`${user.role}Home`);
  setupNavigation();
}

function setupNavigation() {
  const navConfig = { student: [ { id: 'studentHome', label: 'Mes Cours', icon: '📚' }, { id: 'studentAttendance', label: 'Mes Présences', icon: '📊' } ], professor: [ { id: 'professorHome', label: 'Mes Cours', icon: '🎯' }, { id: 'sessionManagement', label: 'Sessions', icon: '👥' }, { id: 'attendanceSummary', label: 'Résumés', icon: '📈' } ], admin: [ { id: 'adminHome', label: 'Tableau de Bord', icon: '🏠' }, { id: 'statsPage', label: 'Statistiques', icon: '📊' }, { id: 'studentManagement', label: 'Étudiants', icon: '👥' } ] };
  const $nav = $('#mainNav').empty();
  const navItems = navConfig[systemState.currentRole];
  navItems.forEach(item => { $nav.append(`<span class="nav-link" data-target="${item.id}">${item.icon} ${item.label}</span>`); });
  $('.nav-link').click(function() { const target = $(this).data('target'); showPage(target); $('.nav-link').removeClass('active'); $(this).addClass('active'); });
  $('.tile').click(function() { const target = $(this).data('target'); if (target) { showPage(target); $(`.nav-link[data-target="${target}"]`).click(); } });
}

function showPage(pageId) { const rolePages = $(`#${systemState.currentRole}Pages section`); rolePages.addClass('hidden'); $(`#${pageId}`).removeClass('hidden'); systemState.currentPage = pageId; }
