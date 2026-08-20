/* ===========================================================
   Parkir Gacoan — main.js
   Toggle sidebar mobile + auto-dismiss alert sukses/gagal
   =========================================================== */

document.addEventListener('DOMContentLoaded', function () {
    // Toggle sidebar di layar kecil
    var toggleBtn = document.getElementById('sidebarToggle');
    var sidebar = document.querySelector('.gacoan-sidebar');

    if (toggleBtn && sidebar) {
        toggleBtn.addEventListener('click', function () {
            sidebar.classList.toggle('show');
        });

        // Klik di luar sidebar -> tutup (khusus mobile)
        document.addEventListener('click', function (e) {
            if (window.innerWidth < 992 &&
                sidebar.classList.contains('show') &&
                !sidebar.contains(e.target) &&
                e.target !== toggleBtn) {
                sidebar.classList.remove('show');
            }
        });
    }

    // Auto-dismiss alert sukses/gagal setelah beberapa detik
    var alerts = document.querySelectorAll('.alert-auto-dismiss');
    alerts.forEach(function (alertEl) {
        setTimeout(function () {
            alertEl.style.transition = 'opacity .4s ease';
            alertEl.style.opacity = '0';
            setTimeout(function () {
                alertEl.remove();
            }, 400);
        }, 3500);
    });
});
