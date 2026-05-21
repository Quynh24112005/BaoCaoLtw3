// Sidebar toggle
function toggleSidebar() {
  document.getElementById('sidebar').classList.toggle('open');
}

// Auto-dismiss flash messages
document.addEventListener('DOMContentLoaded', function () {
  // Initialize Lucide Icons
  if (typeof lucide !== 'undefined') {
    lucide.createIcons();
  }

  document.querySelectorAll('.flash-msg').forEach(function (el) {
    setTimeout(function () {
      el.style.transition = 'opacity .4s';
      el.style.opacity = '0';
      setTimeout(function () { el.remove(); }, 400);
    }, 3500);
  });
});
