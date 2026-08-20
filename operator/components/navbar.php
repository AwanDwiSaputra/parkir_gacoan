<?php $current = basename($_SERVER['PHP_SELF']); ?>
<div class="m21-sidebar">
    <div class="brand">
        <div class="logo-box">M21</div>
        <div class="brand-text">
            <strong>M21 FITNESS</strong>
            <small>PARKING SYSTEM</small>
        </div>
    </div>
    <div class="nav-section">Petugas Parkir</div>
    <a href="<?= BASE_URL ?>operator/index.php" class="nav-link <?= $current === 'index.php' ? 'active' : '' ?>">
        <span class="icon">&#9632;</span> Dashboard
    </a>
    <a href="<?= BASE_URL ?>operator/transaksi_masuk.php" class="nav-link <?= $current === 'transaksi_masuk.php' ? 'active' : '' ?>">
        <span class="icon">&#8594;</span> Kendaraan Masuk
    </a>
    <a href="<?= BASE_URL ?>operator/transaksi_keluar.php" class="nav-link <?= $current === 'transaksi_keluar.php' ? 'active' : '' ?>">
        <span class="icon">&#8592;</span> Kendaraan Keluar
    </a>
    <a href="<?= BASE_URL ?>operator/riwayat_transaksi.php" class="nav-link <?= $current === 'riwayat_transaksi.php' ? 'active' : '' ?>">
        <span class="icon">&#8635;</span> Riwayat Transaksi
    </a>
    <div class="nav-section">Akun</div>
    <a href="<?= BASE_URL ?>operator/edit_profil.php" class="nav-link <?= $current === 'edit_profil.php' ? 'active' : '' ?>">
        <span class="icon">&#9679;</span> Edit Profil
    </a>
    <a href="<?= BASE_URL ?>auth/logout.php" class="nav-link">
        <span class="icon">&#8594;</span> Logout
    </a>
</div>

<div class="m21-content">
    <div class="m21-topbar">
        <div class="d-flex align-items-center gap-3">
            <button id="sidebarToggle" class="btn btn-sm btn-light mobile-only">&#9776;</button>
            <h1 class="page-title"><?= isset($page_title) ? htmlspecialchars($page_title) : 'Dashboard' ?></h1>
        </div>
        <div class="m21-dropdown">
            <div class="user-chip" id="userChipToggle" role="button">
                <div class="avatar-mini">
                    <?php if (!empty($_SESSION['foto'])): ?>
                        <img src="<?= BASE_URL ?>uploads/profil/<?= htmlspecialchars($_SESSION['foto']) ?>"
                             alt="Foto Profil"
                             style="width:100%;height:100%;object-fit:cover;border-radius:50%;">
                    <?php else: ?>
                        <?= strtoupper(substr($_SESSION['nama_lengkap'], 0, 1)) ?>
                    <?php endif; ?>
                </div>
                <div>
                    <div style="font-size:.85rem;font-weight:600;line-height:1;"><?= htmlspecialchars($_SESSION['nama_lengkap']) ?></div>
                    <div style="font-size:.7rem;color:#8a8f98;">Petugas Parkir</div>
                </div>
                <span class="icon small">&#9662;</span>
            </div>
            <ul class="m21-dropdown-menu mt-2" id="userChipMenu">
                <li><a class="dropdown-item" href="<?= BASE_URL ?>operator/edit_profil.php"><span class="icon me-2">&#9679;</span>Edit Profil</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="<?= BASE_URL ?>auth/logout.php"><span class="icon me-2">&#8594;</span>Logout</a></li>
            </ul>
        </div>
    </div>
    <div class="m21-body">
        <?php if (isset($_GET['sukses'])): ?>
            <div class="alert alert-success alert-auto-dismiss">&#10003; <?= htmlspecialchars($_GET['sukses']) ?></div>
        <?php endif; ?>
        <?php if (isset($_GET['gagal'])): ?>
            <div class="alert alert-danger alert-auto-dismiss">&#10005; <?= htmlspecialchars($_GET['gagal']) ?></div>
        <?php endif; ?>

<script>
// Dropdown user chip (pengganti Bootstrap data-bs-toggle)
(function () {
    var toggle = document.getElementById('userChipToggle');
    var menu = document.getElementById('userChipMenu');
    if (!toggle || !menu) return;
    toggle.addEventListener('click', function (e) {
        e.stopPropagation();
        menu.classList.toggle('show');
    });
    document.addEventListener('click', function () {
        menu.classList.remove('show');
    });
})();

// Toggle sidebar di layar kecil (pengganti d-lg-none Bootstrap)
(function () {
    var btn = document.getElementById('sidebarToggle');
    var sidebar = document.querySelector('.m21-sidebar');
    if (!btn || !sidebar) return;
    btn.addEventListener('click', function () {
        sidebar.classList.toggle('show-mobile');
    });
})();

// Alert auto-dismiss (pengganti Bootstrap alert-dismissible)
(function () {
    document.querySelectorAll('.alert-auto-dismiss').forEach(function (el) {
        setTimeout(function () {
            el.style.transition = 'opacity .4s ease';
            el.style.opacity = '0';
            setTimeout(function () { el.remove(); }, 400);
        }, 4000);
    });
})();
</script>