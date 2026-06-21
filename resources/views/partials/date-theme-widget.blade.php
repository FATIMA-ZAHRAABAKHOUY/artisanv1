<div style="display:flex;align-items:center;gap:8px;flex-shrink:0;">

  <div id="dateWidget"
       style="background:var(--sable);border-radius:24px;padding:8px 16px;
              font-size:13px;color:var(--texte);font-weight:500;
              display:flex;align-items:center;gap:6px;white-space:nowrap;">
    <i class="bi bi-calendar3" style="color:var(--gris-doux);font-size:13px;"></i>
    <span id="currentDateText"></span>
  </div>

  <button id="darkModeToggle"
          type="button"
          aria-label="Basculer le mode sombre"
          style="width:36px;height:36px;border-radius:50%;border:none;
                 background:var(--sable-dark);cursor:pointer;
                 display:flex;align-items:center;justify-content:center;
                 font-size:15px;transition:all 0.2s ease;flex-shrink:0;">
    <i class="bi bi-moon-stars" id="darkModeIcon"
       style="color:var(--gris-doux);"></i>
  </button>

</div>

@once
@push('scripts')
<script>
(function () {
  function formatDate() {
    const now = new Date();
    const options = { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' };
    let formatted = now.toLocaleDateString('en-GB', options);
    formatted = formatted.replace(',', '');
    return formatted;
  }

  const dateEl = document.getElementById('currentDateText');
  if (dateEl) {
    dateEl.textContent = formatDate();
  }

  const toggleBtn = document.getElementById('darkModeToggle');
  const toggleIcon = document.getElementById('darkModeIcon');
  const STORAGE_KEY = 'tissu-artisanal-dark-mode';

  function applyDarkMode(isDark) {
    document.documentElement.classList.toggle('dark-mode', isDark);
    if (toggleIcon) {
      toggleIcon.className = isDark ? 'bi bi-sun' : 'bi bi-moon-stars';
    }
    if (toggleBtn) {
      toggleBtn.style.background = isDark ? 'var(--indigo)' : 'var(--sable-dark)';
      toggleIcon.style.color = isDark ? '#fff' : 'var(--gris-doux)';
    }
  }

  const saved = localStorage.getItem(STORAGE_KEY) === 'true';
  applyDarkMode(saved);

  if (toggleBtn) {
    toggleBtn.addEventListener('click', function () {
      const isDark = !document.documentElement.classList.contains('dark-mode');
      applyDarkMode(isDark);
      localStorage.setItem(STORAGE_KEY, isDark);
    });
  }
})();
</script>
@endpush
@endonce
