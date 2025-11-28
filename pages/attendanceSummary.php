<?php
// restructured/pages/attendanceSummary.php
?>
<main class="container">
  
    <div class="chart-wrap">
      <div class="chart-controls" style="margin-bottom:12px; display:flex; gap:8px; align-items:center; justify-content:space-between;">
        <div style="display:flex; gap:8px; align-items:center;">
          <button id="refresh-summary" class="btn btn-secondary">🔄 Rafraîchir</button>
          <small id="chart-note" style="color:#666;">Affiche la répartition des Présences / Absences / Participation</small>
        </div>
      </div>

      <div id="attendance-summary-cards" style="display:flex; gap:12px; margin-bottom:18px;">
        <div class="card" style="flex:1; text-align:center;">
          <h3 style="margin:8px 0 6px 0; color:var(--primary);">Présences</h3>
          <div id="card-presences" style="font-size:22px; font-weight:700; color:var(--primary);">0</div>
        </div>
        <div class="card" style="flex:1; text-align:center;">
          <h3 style="margin:8px 0 6px 0; color:#ec4899;">Absences</h3>
          <div id="card-absences" style="font-size:22px; font-weight:700; color:#ec4899;">0</div>
        </div>
        <div class="card" style="flex:1; text-align:center;">
          <h3 style="margin:8px 0 6px 0; color:var(--accent);">Participation</h3>
          <div id="card-participation" style="font-size:22px; font-weight:700; color:var(--accent);">0</div>
        </div>
      </div>

      <div id="attendanceDonut" style="width:360px; margin:0 auto 8px; position:relative;">
        <div id="donut" style="width:320px; height:320px; border-radius:50%; margin:0 auto; background: conic-gradient(var(--primary) 0 33%, #ec4899 33% 66%, var(--accent) 66% 100%);"></div>
        <div id="donut-center" style="position:absolute; left:50%; top:50%; transform:translate(-50%,-50%); text-align:center; font-weight:700; color:#0f1724;">
          Total
          <div id="donut-total" style="font-size:20px; margin-top:6px;">0</div>
        </div>
      </div>

      <!-- Embedded sample totals as JSON. Server can replace these values dynamically. -->
      <script type="application/json" id="attendance-data">{"presences":34,"absences":8,"participation":9}</script>
    </div>
    <script>
      (function(){
        const dataEl = document.getElementById('attendance-data');

        async function fetchSummary(){
          try{
            const urlParams = new URLSearchParams(window.location.search);
            const courseId = urlParams.get('course_id') || urlParams.get('id') || '';
            const apiUrl = '/api/attendance_summary.php' + (courseId ? ('?course_id='+encodeURIComponent(courseId)) : '');
            const res = await fetch(apiUrl,{cache:'no-store'});
            if(res.ok){
              const json = await res.json();
              if(json && (json.presences !== undefined || json.absences !== undefined || json.participation !== undefined)){
                return { presences: Number(json.presences)||0, absences: Number(json.absences)||0, participation: Number(json.participation)||0 };
              }
            }
          }catch(e){ /* fallback to embedded data below */ }
          try{ const fallback = JSON.parse(dataEl.textContent); return fallback; }catch(e){ return {presences:0,absences:0,participation:0}; }
        }

        function renderDonut(totals){
          const pres = Number(totals.presences) || 0;
          const abs = Number(totals.absences) || 0;
          const part = Number(totals.participation) || 0;
          const total = pres + abs + part || 1;

          const pPct = (pres/total) * 100;
          const aPct = (abs/total) * 100;
          const partPct = 100 - pPct - aPct;

          // Use site colors: --primary, red for absences, --accent
          const grad = `conic-gradient(var(--primary) 0 ${pPct}%, #cc6ba0ff ${pPct}% ${pPct+aPct}%, var(--accent) ${pPct+aPct}% 100%)`;
          document.getElementById('donut').style.background = grad;
          document.getElementById('donut-total').textContent = total;
          document.getElementById('card-presences').textContent = pres;
          document.getElementById('card-absences').textContent = abs;
          document.getElementById('card-participation').textContent = part;
        }

        // initial render
        fetchSummary().then(renderDonut);

        // Refresh button
        document.getElementById('refresh-summary').addEventListener('click', async function(){
          this.disabled = true;
          this.textContent = '⏳ Rafraîchissement...';
          const totals = await fetchSummary();
          renderDonut(totals);
          this.disabled = false;
          this.textContent = '🔄 Rafraîchir';
        });
      })();
    </script>
  </div>
</main>
