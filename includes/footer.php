<?php
// restructured/includes/footer.php
?>
    <footer style="padding:20px; text-align:center; color:#6b7280;">
      <div class="container">Prototype restructured copy — not production.</div>
    </footer>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="/attendance_system/public/assets/js/app.js"></script>
    <script>
      // Highlight active nav link based on `page` query param and add menu toggle behavior
      (function(){
        function getQueryParam(name){
          const params = new URLSearchParams(window.location.search);
          return params.get(name);
        }
        const page = getQueryParam('page') || 'home';
        const links = document.querySelectorAll('#mainNav .nav-link');
        links.forEach(a => {
          try{
            const url = new URL(a.href, window.location.origin);
            const p = url.searchParams.get('page') || '';
            if(p === page) a.classList.add('active');
          } catch(e){ /* ignore */ }
        });

        // Off-canvas sidebar toggle
        const menuToggle = document.getElementById('menuToggle');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('menuOverlay');
        const appRoot = document.getElementById('app');

        function openSidebar(){
          sidebar.classList.add('open');
          overlay.classList.add('open');
          appRoot.classList.add('shifted');
          sidebar.setAttribute('aria-hidden', 'false');
          menuToggle.setAttribute('aria-expanded', 'true');
        }
        function closeSidebar(){
          sidebar.classList.remove('open');
          overlay.classList.remove('open');
          appRoot.classList.remove('shifted');
          sidebar.setAttribute('aria-hidden', 'true');
          menuToggle.setAttribute('aria-expanded', 'false');
        }

        if(menuToggle && sidebar && overlay && appRoot){
          menuToggle.addEventListener('click', function(e){
            if (sidebar.classList.contains('open')) closeSidebar(); else openSidebar();
          });
          overlay.addEventListener('click', function(){ closeSidebar(); });
          // Auto-close sidebar when clicking any nav link
          const navLinks = sidebar.querySelectorAll('.nav-link');
          navLinks.forEach(link => {
            link.addEventListener('click', function(){
              setTimeout(() => closeSidebar(), 100);
            });
          });
          document.addEventListener('keydown', function(e){ if (e.key === 'Escape') closeSidebar(); });
        }
      })();
    </script>
  </div>
</body>
</html>
