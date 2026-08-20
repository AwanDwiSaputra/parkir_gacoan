<?php
$current = basename($_SERVER['PHP_SELF']);
require_once __DIR__ . '/../../assets/logo.php';

// Logo Gacoan (file yang sama dipakai di halaman beranda/login)
// header.php ada di admin/template/, jadi naik dua folder ke img/logo-gacoan.png
$logoGacoan = BASE_URL . '/img/logo-gacoan.png';
$logoGacoanAda = is_file(__DIR__ . '/../../img/logo-gacoan.png');
?>
<div class="gacoan-sidebar">
    <div class="brand">
        <div class="logo-box">
            <?php if ($logoGacoanAda): ?>
                <img src="<?= htmlspecialchars($logoGacoan) ?>" alt="Logo Gacoan" style="height:40px;width:auto;display:block;">
            <?php else: ?>
                <?= gacoanLogo(40) ?>
            <?php endif; ?>
        </div>
        <div class="brand-text">
            <strong>GACOAN</strong>
            <small>PARKING SYSTEM</small>
        </div>
    </div>
    <div class="nav-section">Admin</div>
    <a href="<?= BASE_URL ?>admin/index.php" class="nav-link <?= $current === 'index.php' ? 'active' : '' ?>">
        <i class="bi bi-speedometer2"></i> Dashboard
    </a>
    <a href="<?= BASE_URL ?>admin/kelola_user.php" class="nav-link <?= $current === 'kelola_user.php' ? 'active' : '' ?>">
        <i class="bi bi-people"></i> Kelola User
    </a>
    <a href="<?= BASE_URL ?>admin/kelola_tarif.php" class="nav-link <?= $current === 'kelola_tarif.php' ? 'active' : '' ?>">
        <i class="bi bi-cash-coin"></i> Kelola Tarif
    </a>
    <a href="<?= BASE_URL ?>admin/kelola_area.php" class="nav-link <?= $current === 'kelola_area.php' ? 'active' : '' ?>">
        <i class="bi bi-geo-alt"></i> Area Parkir
    </a>
    <a href="<?= BASE_URL ?>admin/moderasi_testimoni.php" class="nav-link <?= $current === 'moderasi_testimoni.php' ? 'active' : '' ?>">
        <i class="bi bi-chat-square-heart"></i> Moderasi Testimoni
    </a>
    <a href="<?= BASE_URL ?>admin/log_aktivitas.php" class="nav-link <?= $current === 'log_aktivitas.php' ? 'active' : '' ?>">
        <i class="bi bi-journal-text"></i> Log Aktivitas
    </a>
    <div class="nav-section">Akun</div>
    <a href="<?= BASE_URL ?>admin/profil.php" class="nav-link <?= $current === 'profil.php' ? 'active' : '' ?>">
        <i class="bi bi-person-circle"></i> Edit Profil
    </a>
    <a href="<?= BASE_URL ?>auth/logout.php" class="nav-link">
        <i class="bi bi-box-arrow-right"></i> Logout
    </a>
</div>

<div class="gacoan-content">
    <div class="gacoan-topbar">
        <div class="d-flex align-items-center gap-3">
            <button id="sidebarToggle" class="btn btn-sm btn-outline-gacoan d-lg-none"><i class="bi bi-list"></i></button>
            <h1 class="page-title"><?= isset($page_title) ? htmlspecialchars($page_title) : 'Dashboard' ?></h1>
        </div>
        <div class="dropdown">
            <div class="user-chip" role="button" data-bs-toggle="dropdown">
                <div class="avatar-mini">
                    <?php if (!empty($_SESSION['foto'])): ?>
                        <img src="<?= BASE_URL ?>uploads/profil/<?= htmlspecialchars($_SESSION['foto']) ?>"
                             alt="Foto Profil" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">
                    <?php else: ?>
                        <?= strtoupper(substr($_SESSION['nama_lengkap'], 0, 1)) ?>
                    <?php endif; ?>
                </div>
                <div>
                    <div style="font-size:.85rem;font-weight:700;line-height:1;"><?= htmlspecialchars($_SESSION['nama_lengkap']) ?></div>
                    <div style="font-size:.7rem;color:#8d7f74;">Admin</div>
                </div>
                <i class="bi bi-chevron-down small"></i>
            </div>
            <ul class="dropdown-menu dropdown-menu-end mt-2">
                <li><a class="dropdown-item" href="<?= BASE_URL ?>admin/profil.php"><i class="bi bi-person-circle me-2"></i>Edit Profil</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="<?= BASE_URL ?>auth/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
            </ul>
        </div>
    </div>
    <div class="gacoan-body">
        <?php if (isset($_GET['sukses'])): ?>
            <div class="alert alert-success alert-auto-dismiss"><i class="bi bi-check-circle me-1"></i> <?= htmlspecialchars($_GET['sukses']) ?></div>
        <?php endif; ?>
        <?php if (isset($_GET['gagal'])): ?>
            <div class="alert alert-danger alert-auto-dismiss"><i class="bi bi-x-circle me-1"></i> <?= htmlspecialchars($_GET['gagal']) ?></div>
        <?php endif; ?>