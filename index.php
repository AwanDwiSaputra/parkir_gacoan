<?php
require_once __DIR__ . '/config/koneksi.php';
require_once __DIR__ . '/assets/logo.php';

// Jika sudah login, langsung arahkan ke dashboard sesuai role
if (isset($_SESSION['id_user'])) {
    header('Location: ' . getDashboardUrl());
    exit;
}

// ===== Ambil testimoni yang sudah disetujui (approved) dari database =====
$daftarTestimoni = [];
try {
    $stmtTesti = $koneksi->prepare(
        "SELECT nama, role, rating, komentar, created_at
         FROM testimoni
         WHERE status = 'approved'
         ORDER BY created_at DESC
         LIMIT 6"
    );
    $stmtTesti->execute();
    $daftarTestimoni = $stmtTesti->fetchAll();
} catch (PDOException $e) {
    // Tabel testimoni mungkin belum dibuat -> tampilkan data contoh (fallback) di bawah
    $daftarTestimoni = [];
}

// ===== Ambil data kepadatan parkir per jam untuk grafik =====
// Ganti query di bawah sesuai struktur tabel transaksi Anda,
// misalnya menghitung jumlah kendaraan masuk per jam hari ini.
$labelJam = ['08:00','10:00','12:00','14:00','16:00','18:00','20:00'];
$dataKepadatan = [12, 28, 45, 38, 52, 67, 30]; // data contoh (fallback)

try {
    $stmtGrafik = $koneksi->prepare(
        "SELECT DATE_FORMAT(waktu_masuk, '%H:00') AS jam, COUNT(*) AS jumlah
         FROM transaksi
         WHERE DATE(waktu_masuk) = CURDATE()
         GROUP BY jam
         ORDER BY jam ASC"
    );
    $stmtGrafik->execute();
    $hasilGrafik = $stmtGrafik->fetchAll();

    if (!empty($hasilGrafik)) {
        $labelJam = array_column($hasilGrafik, 'jam');
        $dataKepadatan = array_map('intval', array_column($hasilGrafik, 'jumlah'));
    }
} catch (PDOException $e) {
    // Tabel transaksi mungkin belum tersedia/berbeda struktur -> gunakan data contoh di atas
}

// Statistik kecil dipakai di bawah hero (dihitung dari data yang sudah ada, tanpa query baru)
$totalHariIni = array_sum($dataKepadatan);
$jamSibuk = '-';
if (!empty($dataKepadatan)) {
    $idxMax = array_keys($dataKepadatan, max($dataKepadatan))[0];
    $jamSibuk = $labelJam[$idxMax] ?? '-';
}

// ===== Foto parkiran untuk hero =====
// Letakkan file foto asli di assets/img/kir.png (atau ganti nama/path di bawah).
$fotoParkiran = BASE_URL . '/img/kir.png';

// ===== Logo Gacoan (untuk navbar & footer) =====
// Letakkan file logo di assets/img/logo-gacoan.png
$logoGacoan = BASE_URL . 'assets/img/logo-gacoan.png';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Gacoan Parking System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@500;600;700;800&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="<?= BASE_URL ?>assets/js/sound.js"></script>
    <style>
        :root {
            --gc-navy: #14306b;
            --gc-navy-deep: #030a1f;
            --gc-navy-soft: #1c3d84;
            --gc-gold: #f4b400;
            --gc-gold-soft: #ffd873;
            --gc-bg: #081431;
            --gc-bg-alt: #0b1f4d;
            --gc-surface: rgba(255,255,255,.055);
            --gc-surface-solid: #0e2555;
            --gc-line: rgba(255,255,255,.12);
            --gc-muted: rgba(233,238,250,.62);
            --gc-teal: #2fd0c8;
            --gc-text: #eef1fa;
        }
        html { scroll-behavior: smooth; }
        body {
            margin: 0;
            font-family: 'Poppins', system-ui, sans-serif;
            background:
                radial-gradient(circle at 15% 0%, rgba(244,180,0,.08), transparent 40%),
                radial-gradient(circle at 100% 20%, rgba(47,208,200,.07), transparent 45%),
                var(--gc-bg);
            color: var(--gc-text);
        }
        h1, h2, h3, h4, h5, h6 { font-family: 'Plus Jakarta Sans', 'Poppins', sans-serif; }
        section[id] { scroll-margin-top: 96px; }
        .text-muted { color: var(--gc-muted) !important; }
        h5, h6 { color: #fff; }

        /* ===== Navbar ===== */
        .navbar-gacoan {
            background: rgba(8,20,49,.85);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--gc-line);
            padding: 12px 0;
            position: sticky;
            top: 0;
            z-index: 1030;
        }
        .navbar-gacoan .brand {
            color: #fff;
            font-weight: 800;
            font-size: 1.25rem;
        }
        .navbar-gacoan .menu-pill {
            background: rgba(255,255,255,.06);
            border: 1px solid var(--gc-line);
            border-radius: 50px;
            padding: 6px;
            display: inline-flex;
            gap: 2px;
        }
        .navbar-gacoan .menu-pill a {
            color: rgba(255,255,255,.82);
            font-weight: 600;
            font-size: .92rem;
            padding: 8px 18px;
            border-radius: 50px;
            text-decoration: none;
            transition: background .15s ease, color .15s ease;
        }
        .navbar-gacoan .menu-pill a:hover { background: rgba(244,180,0,.14); color: var(--gc-gold); }
        .btn-gacoan {
            background: var(--gc-gold);
            color: var(--gc-navy-deep);
            border: none;
            font-weight: 700;
            border-radius: 10px;
            box-shadow: 0 6px 18px rgba(244,180,0,.25);
            transition: transform .15s ease, box-shadow .15s ease, background .15s ease;
        }
        .btn-gacoan:hover { color: var(--gc-navy-deep); transform: translateY(-2px); background: var(--gc-gold-soft); }
        .btn-outline-gacoan {
            border: 1.5px solid rgba(255,255,255,.5);
            color: #fff;
            font-weight: 700;
            border-radius: 10px;
            background: transparent;
        }
        .btn-outline-gacoan:hover { background: rgba(255,255,255,.1); color: #fff; border-color: #fff; }

        /* ===== Hero: teks + foto berdampingan, statistik di baris bawah ===== */
        .hero {
            background: var(--gc-bg);
            padding: 64px 0 40px;
        }
        .hero-panel {
            background: linear-gradient(150deg, var(--gc-navy-soft) 0%, var(--gc-bg-alt) 55%, var(--gc-navy-deep) 100%);
            border: 1px solid var(--gc-line);
            border-radius: 28px;
            padding: 56px 48px;
            color: #fff;
            position: relative;
            overflow: hidden;
            box-shadow: 0 30px 60px rgba(0,0,0,.35);
            display: flex;
            align-items: center;
            gap: 40px;
            flex-wrap: wrap;
        }
        .hero-panel::after {
            content: "";
            position: absolute;
            width: 380px;
            height: 380px;
            right: -120px;
            top: -140px;
            background: radial-gradient(circle, rgba(244,180,0,.18), transparent 65%);
        }
        .hero-panel-text { flex: 1 1 380px; min-width: 280px; position: relative; z-index: 1; }
        .hero-panel-photo { flex: 1 1 320px; min-width: 260px; position: relative; z-index: 1; }
        .hero-panel-photo img {
            width: 100%;
            height: auto;
            display: block;
            border-radius: 18px;
            box-shadow: 0 20px 45px rgba(0,0,0,.4);
            border: 1px solid rgba(255,255,255,.14);
        }
        .hero-panel-photo-fallback {
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            gap: 10px; min-height: 220px; border-radius: 18px;
            background: rgba(255,255,255,.06); border: 1px dashed rgba(255,255,255,.25);
            color: rgba(255,255,255,.55); text-align: center; font-size: .85rem; padding: 20px;
        }
        .hero-panel-photo-fallback i { font-size: 2rem; color: var(--gc-gold); }
        .hero-panel .badge-tag {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(244,180,0,.14);
            border: 1px solid rgba(244,180,0,.4);
            color: var(--gc-gold);
            padding: 6px 16px;
            border-radius: 50px;
            font-size: .7rem;
            letter-spacing: 2px;
            text-transform: uppercase;
            font-weight: 700;
        }
        .hero-panel h1 {
            font-weight: 800;
            font-size: 2.7rem;
            line-height: 1.15;
            margin: 18px 0;
            max-width: 520px;
        }
        .hero-panel h1 span { color: var(--gc-gold); }
        .hero-panel p.lead { color: rgba(255,255,255,.72); max-width: 480px; font-weight: 400; font-size: 1rem; }

        .hero-stat-row { margin-top: 20px; }
        .hero-stat-card {
            background: var(--gc-surface);
            backdrop-filter: blur(8px);
            border-radius: 20px;
            padding: 22px 24px;
            border: 1px solid var(--gc-line);
            box-shadow: 0 10px 26px rgba(0,0,0,.2);
            display: flex;
            align-items: center;
            gap: 16px;
            height: 100%;
        }
        .hero-stat-card .ic {
            width: 48px; height: 48px; border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.3rem; flex-shrink: 0;
        }
        .hero-stat-card.ic-navy .ic { background: rgba(255,255,255,.1); color: #fff; }
        .hero-stat-card.ic-gold .ic { background: rgba(244,180,0,.18); color: var(--gc-gold); }
        .hero-stat-card.ic-teal .ic { background: rgba(47,208,200,.16); color: var(--gc-teal); }
        .hero-stat-card h4 { margin: 0; font-weight: 800; font-size: 1.3rem; color: #fff; }
        .hero-stat-card p { margin: 0; color: var(--gc-muted); font-size: .82rem; font-weight: 600; }

        /* ===== Section heading kiri ===== */
        .section-head { display: flex; align-items: flex-end; justify-content: space-between; gap: 20px; margin-bottom: 40px; flex-wrap: wrap; }
        .section-head .eyebrow { color: var(--gc-gold); font-weight: 800; letter-spacing: 2px; text-transform: uppercase; font-size: .72rem; }
        .section-head h2 { font-weight: 800; font-size: 2rem; color: #fff; margin: 6px 0 0; }
        .section-head p { color: var(--gc-muted); margin: 8px 0 0; max-width: 480px; }

        /* ===== Fitur: bento grid ===== */
        .bento-wrap { display: grid; grid-template-columns: 1.3fr 1fr; gap: 20px; }
        .bento-col { display: flex; flex-direction: column; gap: 20px; }
        .bento-card {
            background: var(--gc-surface);
            backdrop-filter: blur(8px);
            border-radius: 22px;
            border: 1px solid var(--gc-line);
            padding: 34px;
            cursor: pointer;
            transition: transform .18s ease, box-shadow .18s ease, background .18s ease;
            position: relative;
            color: var(--gc-text);
        }
        .bento-card h5 { color: #fff; }
        .bento-card:hover { transform: translateY(-5px); box-shadow: 0 20px 40px rgba(0,0,0,.3); background: rgba(255,255,255,.08); }
        .bento-card.big { background: linear-gradient(160deg, var(--gc-navy-soft), var(--gc-navy-deep)); color: #fff; display: flex; flex-direction: column; justify-content: space-between; min-height: 380px; border-color: rgba(244,180,0,.25); }
        .bento-card.big .feature-icon { background: rgba(244,180,0,.14); color: var(--gc-gold); }
        .bento-card.big p { color: rgba(255,255,255,.7); }
        .feature-icon {
            width: 52px; height: 52px; border-radius: 14px;
            background: linear-gradient(135deg, var(--gc-navy), var(--gc-navy-soft));
            display: flex; align-items: center; justify-content: center;
            color: var(--gc-gold); font-size: 1.35rem; margin-bottom: 16px;
        }
        .feature-more { font-size: .84rem; font-weight: 700; color: var(--gc-gold); margin: 14px 0 0; }
        .bento-card.big .feature-more { color: var(--gc-gold); }

        .modal-content { border-radius: 18px; border: 1px solid var(--gc-line); background: var(--gc-surface-solid); color: var(--gc-text); }
        .modal-content h5, .modal-content h6, .modal-content strong { color: #fff; }
        .modal-header { background: var(--gc-navy); color: #fff; border-radius: 17px 17px 0 0; border-bottom: none; }
        .modal-header .btn-close { filter: invert(1) grayscale(100%) brightness(200%); }
        .modal-body .form-control, .modal-body textarea.form-control {
            background: rgba(255,255,255,.06); border: 1px solid var(--gc-line); color: #fff;
        }
        .modal-body .form-control:focus { background: rgba(255,255,255,.09); color: #fff; border-color: var(--gc-gold); box-shadow: none; }
        .modal-body .form-control::placeholder { color: rgba(255,255,255,.4); }
        .modal-body .form-label { color: #fff; }
        .modal-icon-lg {
            width: 60px; height: 60px; border-radius: 16px;
            background: linear-gradient(135deg, var(--gc-navy), var(--gc-navy-soft));
            display: flex; align-items: center; justify-content: center;
            color: var(--gc-gold); font-size: 1.7rem; margin-bottom: 18px;
        }

        /* ===== Role: tab horizontal ===== */
        .role-tabs .nav-link {
            border-radius: 14px; padding: 16px 22px; margin-right: 10px;
            color: var(--gc-text); font-weight: 700; background: var(--gc-surface);
            border: 1px solid var(--gc-line); text-align: left;
        }
        .role-tabs .nav-link i { color: var(--gc-gold); margin-right: 8px; }
        .role-tabs .nav-link.active { background: var(--gc-gold); color: var(--gc-navy-deep); border-color: var(--gc-gold); }
        .role-tabs .nav-link.active i { color: var(--gc-navy-deep); }
        .role-panel {
            background: linear-gradient(155deg, var(--gc-navy-soft), var(--gc-navy-deep));
            border: 1px solid var(--gc-line);
            border-radius: 22px; padding: 40px; color: #fff; min-height: 220px;
        }
        .role-panel h4 { font-weight: 800; color: var(--gc-gold); }
        .role-panel ul { margin: 0; padding-left: 20px; }
        .role-panel ul li { margin-bottom: 8px; opacity: .9; }

        /* ===== Video area ===== */
        .video-parkir-wrap {
            border-radius: 22px; overflow: hidden;
            box-shadow: 0 20px 45px rgba(11,31,77,.18); background: #000;
        }
        .video-parkir-wrap video { width: 100%; height: 100%; display: block; object-fit: cover; }
        .video-side-list { display: flex; flex-direction: column; gap: 16px; margin-top: 24px; }
        .video-side-list .vs-item { display: flex; gap: 14px; align-items: flex-start; }
        .video-side-list .vs-item strong { color: #fff; }
        .video-side-list .vs-item i { color: var(--gc-gold); background: rgba(244,180,0,.12); width: 38px; height: 38px; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }

        /* ===== Informasi ===== */
        .info-section { background: transparent; }
        .info-stat-row { background: linear-gradient(120deg, var(--gc-navy-soft), var(--gc-navy)); border: 1px solid var(--gc-line); border-radius: 22px; padding: 30px 10px; color: #fff; }
        .info-stat { text-align: center; padding: 10px; border-right: 1px solid rgba(255,255,255,.12); }
        .info-stat:last-child { border-right: none; }
        .info-stat h3 { font-weight: 800; font-size: 2.1rem; color: var(--gc-gold); margin-bottom: 2px; }
        .info-stat p { margin: 0; opacity: .78; font-weight: 600; font-size: .85rem; }
        .grafik-card { background: var(--gc-surface); backdrop-filter: blur(8px); border-radius: 20px; padding: 26px; border: 1px solid var(--gc-line); height: 100%; }
        .grafik-card h6 { color: #fff; }
        .grafik-card canvas { max-height: 280px; }
        .info-box { background: var(--gc-surface); backdrop-filter: blur(8px); border-radius: 18px; padding: 24px; border: 1px solid var(--gc-line); height: 100%; }
        .info-box i { color: var(--gc-gold); font-size: 1.3rem; margin-right: 8px; }
        .info-box.info-box-clickable { cursor: pointer; transition: transform .18s ease, box-shadow .18s ease, background .18s ease; }
        .info-box.info-box-clickable:hover { transform: translateY(-5px); box-shadow: 0 16px 34px rgba(0,0,0,.25); background: rgba(255,255,255,.08); }

        /* ===== Testimoni: carousel horizontal-scroll ===== */
        .testi-section { background: transparent; }
        .testi-scroll { display: flex; gap: 20px; overflow-x: auto; padding: 6px 6px 18px; scroll-snap-type: x mandatory; }
        .testi-scroll::-webkit-scrollbar { height: 6px; }
        .testi-scroll::-webkit-scrollbar-thumb { background: var(--gc-line); border-radius: 10px; }
        .testi-card {
            background: var(--gc-surface); backdrop-filter: blur(8px); border-radius: 18px; padding: 26px;
            border: 1px solid var(--gc-line); min-width: 300px; max-width: 320px;
            scroll-snap-align: start; flex-shrink: 0;
        }
        .testi-card .stars { color: var(--gc-gold); margin-bottom: 10px; }
        .testi-card p.testi-text { color: rgba(238,241,250,.8); font-style: italic; min-height: 80px; }
        .testi-user { display: flex; align-items: center; margin-top: 16px; }
        .testi-user h6 { color: #fff; }
        .testi-avatar {
            width: 44px; height: 44px; border-radius: 50%;
            background: linear-gradient(135deg, var(--gc-navy), var(--gc-navy-soft));
            display: flex; align-items: center; justify-content: center;
            color: var(--gc-gold); font-weight: 800; margin-right: 12px; flex-shrink: 0;
        }
        .testi-user h6 { margin: 0; font-weight: 700; }
        .testi-user small { color: var(--gc-muted); }

        .rating-input { display: flex; flex-direction: row-reverse; justify-content: flex-end; gap: 4px; }
        .rating-input input { display: none; }
        .rating-input label { font-size: 2rem; color: rgba(255,255,255,.25); cursor: pointer; transition: color .15s ease; }
        .rating-input input:checked ~ label, .rating-input label:hover, .rating-input label:hover ~ label { color: var(--gc-gold); }

        .cta-section {
            background: linear-gradient(120deg, var(--gc-navy), var(--gc-navy-deep));
            color: #fff; border-radius: 26px; padding: 56px 44px;
            margin: 20px 0 70px; display: flex; align-items: center; justify-content: space-between;
            gap: 30px; flex-wrap: wrap;
        }

        footer { background: var(--gc-navy-deep); color: rgba(255,255,255,.6); padding: 40px 0 20px; font-size: .9rem; }
        footer h6 { color: #fff; font-weight: 700; }
        footer a { color: rgba(255,255,255,.6); text-decoration: none; }
        footer a:hover { color: var(--gc-gold); }
        footer hr { border-color: rgba(255,255,255,.1); margin: 24px 0 16px; }

        .help-float-btn {
            position: fixed; right: 24px; bottom: 24px; width: 58px; height: 58px;
            border-radius: 50%; background: var(--gc-navy); color: var(--gc-gold);
            display: flex; align-items: center; justify-content: center; font-size: 1.5rem;
            box-shadow: 0 10px 24px rgba(11,31,77,.4); z-index: 1040; animation: help-pulse 2.4s infinite;
        }
        .help-float-btn:hover { color: var(--gc-gold); }
        @keyframes help-pulse {
            0% { box-shadow: 0 10px 24px rgba(11,31,77,.4), 0 0 0 0 rgba(244,180,0,.4); }
            70% { box-shadow: 0 10px 24px rgba(11,31,77,.4), 0 0 0 14px rgba(244,180,0,0); }
            100% { box-shadow: 0 10px 24px rgba(11,31,77,.4), 0 0 0 0 rgba(244,180,0,0); }
        }
        #modalBantuan .accordion-item { background: transparent; border-color: var(--gc-line); }
        #modalBantuan .accordion-button { background: rgba(255,255,255,.05); color: var(--gc-text); }
        #modalBantuan .accordion-button::after { filter: invert(1) grayscale(100%) brightness(200%); }
        #modalBantuan .accordion-button:not(.collapsed) { background: rgba(244,180,0,.12); color: var(--gc-gold); box-shadow: none; font-weight: 700; }
        #modalBantuan .accordion-button:focus { box-shadow: none; }
        #modalBantuan .accordion-body { background: rgba(255,255,255,.02); }
        .bantuan-contact-item {
            display: flex; align-items: center; gap: 12px; padding: 12px 14px; border-radius: 14px;
            background: rgba(255,255,255,.05); border: 1px solid var(--gc-line); margin-bottom: 10px;
            text-decoration: none; color: var(--gc-text);
        }
        .bantuan-contact-item:hover { background: var(--gc-gold); color: var(--gc-navy-deep); }
        .bantuan-contact-item i { font-size: 1.3rem; color: var(--gc-gold); }
        .bantuan-contact-item:hover i { color: var(--gc-navy-deep); }
        .navbar-logo-wrap { display: inline-flex; align-items: center; }
        .navbar-logo-wrap img { display: block; width: auto; object-fit: contain; }

        @media (max-width: 991px) {
            .navbar-gacoan .menu-pill { display: none; }
            .bento-wrap { grid-template-columns: 1fr; }
            .info-stat { border-right: none; border-bottom: 1px solid rgba(255,255,255,.12); }
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-gacoan">
    <div class="container">
        <a class="brand navbar-brand d-flex align-items-center gap-2" href="#beranda">
            <span class="navbar-logo-wrap">
                <?php if (is_file(__DIR__ . '/assets/img/logo-gacoan.png')): ?>
                    <img src="<?= htmlspecialchars($logoGacoan) ?>" alt="Logo Gacoan" style="height:36px;">
                <?php else: ?>
                    <?= gacoanLogo(36) ?>
                <?php endif; ?>
            </span>
            GACOAN
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarGacoan">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarGacoan">
            <div class="menu-pill mx-lg-auto my-3 my-lg-0">
                <a href="#beranda">Beranda</a>
                <a href="#fitur">Fitur</a>
                <a href="#video-parkir">Area Parkir</a>
                <a href="#informasi">Informasi</a>
                <a href="#testimoni">Testimoni</a>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <a href="<?= BASE_URL ?>auth/register.php" class="btn btn-outline-gacoan btn-sm px-4 py-2">
                    Daftar <i class="bi bi-person-plus"></i>
                </a>
                <a href="<?= BASE_URL ?>auth/login.php" class="btn btn-gacoan btn-sm px-4 py-2">
                    Login <i class="bi bi-box-arrow-in-right"></i>
                </a>
            </div>
        </div>
    </div>
</nav>

<!-- ===== HERO: teks & foto parkiran berdampingan, statistik di baris bawah ===== -->
<section class="hero" id="beranda">
    <div class="container">
        <div class="row g-4">
            <div class="col-12">
                <div class="hero-panel">
                    <div class="hero-panel-text">
                        <span class="badge-tag">GACOAN PARKING SYSTEM</span>
                        <h1>Kelola Parkir <span>Gacoan</span> Lebih Mudah &amp; Real-Time</h1>
                        <p class="lead">
                            Sistem manajemen parkir untuk member &amp; tamu Gacoan.
                            Cepat, rapi, dan terpantau real-time untuk Admin, Petugas, dan Owner.
                        </p>
                        <div class="d-flex gap-3 mt-4 flex-wrap">
                            <a href="<?= BASE_URL ?>auth/login.php" class="btn btn-gacoan px-4 py-2">
                                Masuk ke Sistem <i class="bi bi-arrow-right-circle"></i>
                            </a>
                            <a href="#fitur" class="btn btn-outline-gacoan px-4 py-2" style="border-color:rgba(255,255,255,.5); color:#fff;">
                                Lihat Fitur
                            </a>
                        </div>
                    </div>
                    <div class="hero-panel-photo">
                        <?php if (is_file(__DIR__ . '/img/kir.png')): ?>
                            <img src="<?= htmlspecialchars($fotoParkiran) ?>" alt="Denah/foto area parkir Gacoan">
                        <?php else: ?>
                            <div class="hero-panel-photo-fallback">
                                <i class="bi bi-image"></i>
                                <span>Foto parkiran belum dipasang<br><small>letakkan file di assets/img/kir.png</small></span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-12">
                <div class="row g-3 hero-stat-row">
                    <div class="col-md-4">
                        <div class="hero-stat-card ic-navy">
                            <div class="ic"><i class="bi bi-car-front"></i></div>
                            <div>
                                <h4><?= (int)$totalHariIni ?></h4>
                                <p>Kendaraan Tercatat Hari Ini</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="hero-stat-card ic-gold">
                            <div class="ic"><i class="bi bi-clock"></i></div>
                            <div>
                                <h4><?= htmlspecialchars($jamSibuk) ?></h4>
                                <p>Jam Paling Sibuk</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="hero-stat-card ic-teal">
                            <div class="ic"><i class="bi bi-shield-check"></i></div>
                            <div>
                                <h4>3 Peran</h4>
                                <p>Admin · Petugas · Owner</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ===== FITUR: bento grid ===== -->
<section class="container py-5" id="fitur">
    <div class="section-head">
        <div>
            <span class="eyebrow">Fitur Unggulan</span>
            <h2>Semua yang Anda Butuhkan</h2>
            <p>Satu sistem untuk seluruh operasional parkir Gacoan, dari akses pengguna hingga rekap laporan.</p>
        </div>
    </div>
    <div class="bento-wrap">
        <div class="bento-card big" data-bs-toggle="modal" data-bs-target="#modalFitur1">
            <div>
                <div class="feature-icon"><i class="bi bi-shield-check"></i></div>
                <h5 class="fw-bold">Akses Berbasis Peran</h5>
                <p class="mb-0">Role-based access untuk Admin, Petugas, dan Owner, masing-masing dengan tampilan dan hak akses tersendiri sehingga tidak ada yang tumpang tindih.</p>
            </div>
            <p class="feature-more">Lihat detail <i class="bi bi-arrow-right"></i></p>
        </div>
        <div class="bento-col">
            <div class="bento-card" data-bs-toggle="modal" data-bs-target="#modalFitur2">
                <div class="feature-icon"><i class="bi bi-p-circle"></i></div>
                <h5 class="fw-bold">Pantau Slot Real-Time</h5>
                <p class="text-muted mb-0">Ketahui ketersediaan area parkir secara langsung, kapan saja dibutuhkan.</p>
                <p class="feature-more">Lihat detail <i class="bi bi-arrow-right"></i></p>
            </div>
            <div class="bento-card" data-bs-toggle="modal" data-bs-target="#modalFitur3">
                <div class="feature-icon"><i class="bi bi-receipt"></i></div>
                <h5 class="fw-bold">Struk &amp; Rekap Otomatis</h5>
                <p class="text-muted mb-0">Cetak struk transaksi dan rekap laporan parkir secara otomatis.</p>
                <p class="feature-more">Lihat detail <i class="bi bi-arrow-right"></i></p>
            </div>
        </div>
    </div>
</section>

<!-- Modal Fitur 1: Akses Berbasis Peran -->
<div class="modal fade" id="modalFitur1" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Akses Berbasis Peran</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="modal-icon-lg"><i class="bi bi-shield-check"></i></div>
                <p>Sistem membagi hak akses ke dalam tiga peran, masing-masing dengan tampilan dan kewenangan berbeda:</p>
                <ul>
                    <li><strong>Admin</strong> &mdash; kelola akun pengguna, master data, dan konfigurasi sistem.</li>
                    <li><strong>Petugas</strong> &mdash; proses transaksi parkir harian dan cetak struk.</li>
                    <li><strong>Owner</strong> &mdash; pantau laporan dan performa operasional secara keseluruhan.</li>
                </ul>
                <p class="mb-0 text-muted">Setiap login otomatis diarahkan ke dashboard sesuai peran, sehingga tidak ada akses yang tumpang tindih.</p>
            </div>
        </div>
    </div>
</div>

<!-- Modal Fitur 2: Pantau Slot Real-Time -->
<div class="modal fade" id="modalFitur2" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Pantau Slot Real-Time</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="modal-icon-lg"><i class="bi bi-p-circle"></i></div>
                <p>Ketersediaan area parkir dapat dipantau secara langsung, sehingga petugas dan owner selalu tahu:</p>
                <ul>
                    <li>Jumlah slot yang masih kosong.</li>
                    <li>Kendaraan mana saja yang sedang parkir.</li>
                    <li>Estimasi kepadatan area parkir pada jam tertentu.</li>
                </ul>
                <p class="mb-0 text-muted">Membantu menghindari penumpukan kendaraan dan mempercepat pengambilan keputusan operasional.</p>
            </div>
        </div>
    </div>
</div>

<!-- Modal Fitur 3: Struk & Rekap Otomatis -->
<div class="modal fade" id="modalFitur3" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Struk &amp; Rekap Otomatis</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="modal-icon-lg"><i class="bi bi-receipt"></i></div>
                <p>Setiap transaksi parkir tercatat otomatis dan dapat langsung dicetak dalam bentuk struk. Sistem juga menyediakan:</p>
                <ul>
                    <li>Rekap transaksi harian, mingguan, hingga bulanan.</li>
                    <li>Ringkasan pendapatan yang siap dilihat owner.</li>
                    <li>Riwayat transaksi yang mudah ditelusuri kembali.</li>
                </ul>
                <p class="mb-0 text-muted">Mengurangi pencatatan manual dan risiko kesalahan hitung.</p>
            </div>
        </div>
    </div>
</div>

<!-- ===== ROLE: tab horizontal ===== -->
<section class="container py-4">
    <div class="section-head">
        <div>
            <span class="eyebrow">Untuk Setiap Peran</span>
            <h2>Dibuat Sesuai Tanggung Jawab</h2>
        </div>
    </div>
    <div class="row g-4">
        <div class="col-lg-4">
            <div class="nav flex-column role-tabs" id="roleTabList" role="tablist" aria-orientation="vertical">
                <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#roleAdmin" type="button">
                    <i class="bi bi-person-gear"></i> Admin
                </button>
                <button class="nav-link" data-bs-toggle="pill" data-bs-target="#rolePetugas" type="button">
                    <i class="bi bi-person-badge"></i> Petugas
                </button>
                <button class="nav-link" data-bs-toggle="pill" data-bs-target="#roleOwner" type="button">
                    <i class="bi bi-graph-up-arrow"></i> Owner
                </button>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="tab-content h-100">
                <div class="tab-pane fade show active h-100" id="roleAdmin">
                    <div class="role-panel h-100">
                        <h4>Admin</h4>
                        <p class="opacity-75">Kendali penuh atas sistem dan pengguna.</p>
                        <ul>
                            <li>Kelola akun Admin, Petugas, dan Owner.</li>
                            <li>Atur master data dan konfigurasi sistem.</li>
                            <li>Tinjau &amp; setujui testimoni yang masuk.</li>
                        </ul>
                    </div>
                </div>
                <div class="tab-pane fade h-100" id="rolePetugas">
                    <div class="role-panel h-100">
                        <h4>Petugas</h4>
                        <p class="opacity-75">Operasional harian di lapangan.</p>
                        <ul>
                            <li>Proses transaksi parkir masuk &amp; keluar.</li>
                            <li>Cetak struk transaksi secara instan.</li>
                            <li>Input dan verifikasi data kendaraan.</li>
                        </ul>
                    </div>
                </div>
                <div class="tab-pane fade h-100" id="roleOwner">
                    <div class="role-panel h-100">
                        <h4>Owner</h4>
                        <p class="opacity-75">Pantau performa dari mana saja.</p>
                        <ul>
                            <li>Lihat laporan &amp; rekap pendapatan.</li>
                            <li>Pantau performa operasional parkir.</li>
                            <li>Analisis kepadatan berdasarkan jam.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ===== VIDEO: teks di samping (tidak diubah) ===== -->
<section class="container py-5" id="video-parkir">
    <div class="row g-5 align-items-center">
        <div class="col-lg-6">
            <div class="ratio ratio-16x9 video-parkir-wrap">
                <!-- Ganti src di bawah ini dengan file video milik Anda,
                     misalnya: <?= BASE_URL ?>video/area-parkir.mp4 -->
                <video controls preload="metadata" poster="<?= BASE_URL ?>img/gacoan.mp4">
                    <source src="<?= BASE_URL ?>img/gacoan.mp4" type="video/mp4">
                    Maaf, browser Anda tidak mendukung pemutaran video. Anda dapat
                    <a href="<?= BASE_URL ?>video/area-parkir.mp4">mengunduh video di sini</a>.
                </video>
            </div>
        </div>
        <div class="col-lg-6">
            <span class="eyebrow">Tentang Parkir</span>
            <h2 class="section-head-title" style="font-weight:800; font-size:1.9rem; color:var(--gc-navy); margin-top:6px;">Lihat Area Parkir Kami</h2>
            <p class="text-muted">Tonton video singkat suasana dan tata letak area parkir Gacoan sebelum Anda datang.</p>
            <div class="video-side-list">
                <div class="vs-item">
                    <i class="bi bi-signpost-split"></i>
                    <div><strong>Jalur Masuk &amp; Keluar Jelas</strong><div class="text-muted small">Alur kendaraan diatur agar tidak menumpuk.</div></div>
                </div>
                <div class="vs-item">
                    <i class="bi bi-camera-video"></i>
                    <div><strong>Terpantau Petugas</strong><div class="text-muted small">Area diawasi selama jam operasional.</div></div>
                </div>
                <div class="vs-item">
                    <i class="bi bi-lightbulb"></i>
                    <div><strong>Pencahayaan Memadai</strong><div class="text-muted small">Aman digunakan hingga malam hari.</div></div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ===== INFORMASI: stat bar + grafik sejajar dengan info box ===== -->
<section class="info-section py-5" id="informasi">
    <div class="container">
        <div class="section-head">
            <div>
                <span class="eyebrow">Informasi</span>
                <h2>Sekilas Layanan Parkir Gacoan</h2>
            </div>
        </div>

        <div class="info-stat-row mb-4">
            <div class="row g-3">
                <div class="col-6 col-md-3">
                    <div class="info-stat"><h3>24/7</h3><p>Pemantauan Real-Time</p></div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="info-stat"><h3>3</h3><p>Peran Pengguna</p></div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="info-stat"><h3>100%</h3><p>Struk Otomatis</p></div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="info-stat"><h3>0</h3><p>Pencatatan Manual</p></div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-7">
                <div class="grafik-card">
                    <h6 class="fw-bold mb-1"><i class="bi bi-bar-chart-line"></i> Kepadatan Parkir per Jam</h6>
                    <p class="text-muted small mb-3">Grafik jumlah kendaraan masuk berdasarkan jam (hari ini).</p>
                    <canvas id="chartKepadatan"></canvas>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="d-flex flex-column gap-3 h-100">
                    <div class="info-box">
                        <h6 class="fw-bold mb-1"><i class="bi bi-clock-history"></i>Jam Operasional</h6>
                        <p class="text-muted mb-0">Sistem parkir aktif mengikuti jam operasional Gacoan setiap hari.</p>
                    </div>
                    <div class="info-box">
                        <h6 class="fw-bold mb-1"><i class="bi bi-person-plus"></i>Akun Pengguna</h6>
                        <p class="text-muted mb-0">Akun baru untuk Admin, Petugas, atau Owner hanya dapat dibuat oleh Administrator.</p>
                    </div>
                    <div class="info-box info-box-clickable" data-bs-toggle="modal" data-bs-target="#modalBantuan">
                        <h6 class="fw-bold mb-1"><i class="bi bi-headset"></i>Bantuan</h6>
                        <p class="text-muted mb-0">Kendala login atau transaksi dapat dilaporkan langsung ke Admin sistem.</p>
                        <p class="feature-more mb-0">Lihat FAQ &amp; kontak <i class="bi bi-arrow-right"></i></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ===== TESTIMONI: carousel horizontal ===== -->
<section class="testi-section py-5" id="testimoni">
    <div class="container">
        <div class="section-head">
            <div>
                <span class="eyebrow">Testimoni</span>
                <h2>Apa Kata Pengguna Kami</h2>
                <p>Geser untuk melihat lebih banyak ulasan dari member sistem parkir Gacoan.</p>
            </div>
            <button type="button" class="btn btn-gacoan px-4 py-2" data-bs-toggle="modal" data-bs-target="#modalTestimoni">
                <i class="bi bi-chat-left-text"></i> Tulis Komentar &amp; Rating
            </button>
        </div>

        <?php if (isset($_GET['testimoni']) && $_GET['testimoni'] === 'sukses'): ?>
            <div class="alert alert-success text-center" role="alert">
                Terima kasih! Komentar Anda sudah terkirim dan akan tampil setelah disetujui Admin.
            </div>
        <?php elseif (isset($_GET['testimoni']) && $_GET['testimoni'] === 'gagal'): ?>
            <div class="alert alert-danger text-center" role="alert">
                Gagal mengirim komentar. Pastikan nama, rating, dan komentar terisi dengan benar.
            </div>
        <?php endif; ?>

        <div class="testi-scroll">
            <?php if (!empty($daftarTestimoni)): ?>
                <?php foreach ($daftarTestimoni as $t): ?>
                    <div class="testi-card">
                        <div class="stars">
                            <?php
                            $r = (int)$t['rating'];
                            for ($i = 1; $i <= 5; $i++) {
                                echo $i <= $r
                                    ? '<i class="bi bi-star-fill"></i>'
                                    : '<i class="bi bi-star"></i>';
                            }
                            ?>
                        </div>
                        <p class="testi-text">"<?= htmlspecialchars($t['komentar']) ?>"</p>
                        <div class="testi-user">
                            <div class="testi-avatar"><?= strtoupper(substr($t['nama'], 0, 1)) ?></div>
                            <div>
                                <h6><?= htmlspecialchars($t['nama']) ?></h6>
                                <small><?= htmlspecialchars($t['role']) ?></small>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- Testimoni contoh (tampil jika belum ada data approved di database) -->
                <div class="testi-card">
                    <div class="stars">
                        <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
                    </div>
                    <p class="testi-text">"Sejak pakai sistem ini, transaksi parkir jadi lebih cepat dan struk langsung tercetak otomatis."</p>
                    <div class="testi-user">
                        <div class="testi-avatar">R</div>
                        <div><h6>Rian</h6><small>Petugas Parkir</small></div>
                    </div>
                </div>
                <div class="testi-card">
                    <div class="stars">
                        <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
                    </div>
                    <p class="testi-text">"Laporan pendapatan parkir bisa saya pantau kapan saja tanpa harus datang langsung ke lokasi."</p>
                    <div class="testi-user">
                        <div class="testi-avatar">D</div>
                        <div><h6>Coach Dedi</h6><small>Owner Gacoan</small></div>
                    </div>
                </div>
                <div class="testi-card">
                    <div class="stars">
                        <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-half"></i>
                    </div>
                    <p class="testi-text">"Sebagai Admin, mengelola akun dan data pengguna jadi jauh lebih rapi dan terstruktur."</p>
                    <div class="testi-user">
                        <div class="testi-avatar">A</div>
                        <div><h6>Admin D</h6><small>Administrator</small></div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Modal Tulis Testimoni (Komentar & Rating) -->
<div class="modal fade" id="modalTestimoni" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="bi bi-chat-left-text"></i> Tulis Komentar &amp; Rating</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= BASE_URL ?>testimoni_submit.php" method="POST">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="inputNama" class="form-label fw-semibold">Nama</label>
                        <input type="text" class="form-control" id="inputNama" name="nama" maxlength="100" required>
                    </div>
                    <div class="mb-3">
                        <label for="inputRole" class="form-label fw-semibold">Peran / Status <span class="text-muted fw-normal">(opsional)</span></label>
                        <input type="text" class="form-control" id="inputRole" name="role" maxlength="50" placeholder="Contoh: Pelanggan, Petugas, Owner">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold d-block">Rating</label>
                        <div class="rating-input">
                            <input type="radio" id="star5" name="rating" value="5" required><label for="star5" title="5 bintang"><i class="bi bi-star-fill"></i></label>
                            <input type="radio" id="star4" name="rating" value="4"><label for="star4" title="4 bintang"><i class="bi bi-star-fill"></i></label>
                            <input type="radio" id="star3" name="rating" value="3"><label for="star3" title="3 bintang"><i class="bi bi-star-fill"></i></label>
                            <input type="radio" id="star2" name="rating" value="2"><label for="star2" title="2 bintang"><i class="bi bi-star-fill"></i></label>
                            <input type="radio" id="star1" name="rating" value="1"><label for="star1" title="1 bintang"><i class="bi bi-star-fill"></i></label>
                        </div>
                    </div>
                    <div class="mb-1">
                        <label for="inputKomentar" class="form-label fw-semibold">Komentar</label>
                        <textarea class="form-control" id="inputKomentar" name="komentar" rows="4" maxlength="1000" required placeholder="Ceritakan pengalaman Anda menggunakan sistem parkir Gacoan..."></textarea>
                    </div>
                    <p class="text-muted small mb-0">Komentar Anda akan ditinjau oleh Admin sebelum tampil di halaman ini.</p>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-gacoan px-4">Kirim <i class="bi bi-send"></i></button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ===== CTA ===== -->
<section class="container">
    <div class="cta-section">
        <div>
            <h3 class="fw-bold mb-2">Siap mengelola parkir Gacoan?</h3>
            <p class="opacity-75 mb-0">Masuk ke sistem untuk mulai memantau dan mengelola transaksi parkir.</p>
            <small class="opacity-50 d-block mt-2">Akun Petugas/Owner hanya dapat dibuat oleh Administrator.</small>
        </div>
        <a href="<?= BASE_URL ?>auth/login.php" class="btn btn-gacoan px-5 py-3 flex-shrink-0">
            Masuk Sekarang <i class="bi bi-box-arrow-in-right"></i>
        </a>
    </div>
</section>

<footer>
    <div class="container">
        <div class="row g-4">
            <div class="col-md-4">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="navbar-logo-wrap">
                        <?php if (is_file(__DIR__ . '/assets/img/logo-gacoan.png')): ?>
                            <img src="<?= htmlspecialchars($logoGacoan) ?>" alt="Logo Gacoan" style="height:30px;">
                        <?php else: ?>
                            <?= gacoanLogo(30) ?>
                        <?php endif; ?>
                    </span>
                    <span class="fw-bold text-white">GACOAN</span>
                </div>
                <p class="mb-0">Sistem manajemen parkir untuk member &amp; tamu Gacoan — cepat, rapi, dan real-time.</p>
            </div>
            <div class="col-md-4">
                <h6>Navigasi</h6>
                <div class="d-flex flex-column gap-1">
                    <a href="#beranda">Beranda</a>
                    <a href="#fitur">Fitur Unggulan</a>
                    <a href="#informasi">Informasi</a>
                    <a href="#testimoni">Testimoni</a>
                </div>
            </div>
            <div class="col-md-4">
                <h6>Akun</h6>
                <div class="d-flex flex-column gap-1">
                    <a href="<?= BASE_URL ?>auth/login.php">Login</a>
                    <a href="<?= BASE_URL ?>auth/register.php">Daftar</a>
                </div>
            </div>
        </div>
        <hr>
        <div class="text-center">&copy; <?= date('Y') ?> Gacoan Parking System. All rights reserved.By awan dwi saputro</div>
    </div>
</footer>

<!-- Modal Bantuan -->
<div class="modal fade" id="modalBantuan" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="bi bi-headset"></i> Pusat Bantuan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <h6 class="fw-bold mb-3">Pertanyaan yang Sering Diajukan</h6>
                <div class="accordion mb-4" id="accordionBantuan">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                Saya lupa password akun, bagaimana cara reset?
                            </button>
                        </h2>
                        <div id="faq1" class="accordion-collapse collapse" data-bs-parent="#accordionBantuan">
                            <div class="accordion-body text-muted">
                                Reset password hanya dapat dilakukan oleh Administrator. Silakan hubungi Admin melalui kontak di bawah dengan menyertakan username akun Anda.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                Bagaimana cara membuat akun baru?
                            </button>
                        </h2>
                        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#accordionBantuan">
                            <div class="accordion-body text-muted">
                                Akun untuk Admin, Petugas, maupun Owner hanya dapat dibuat oleh Administrator melalui menu kelola pengguna. Pengguna baru tidak dapat mendaftar sendiri.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                Struk transaksi tidak tercetak, apa yang harus dilakukan?
                            </button>
                        </h2>
                        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#accordionBantuan">
                            <div class="accordion-body text-muted">
                                Periksa koneksi printer terlebih dahulu. Jika masih bermasalah, transaksi tetap tersimpan di sistem dan struk dapat dicetak ulang oleh Petugas melalui riwayat transaksi.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                Data slot parkir tidak update secara real-time?
                            </button>
                        </h2>
                        <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#accordionBantuan">
                            <div class="accordion-body text-muted">
                                Pastikan koneksi internet perangkat stabil, lalu muat ulang (refresh) halaman. Jika masalah berlanjut, laporkan ke Admin sistem.
                            </div>
                        </div>
                    </div>
                </div>

                <h6 class="fw-bold mb-3">Hubungi Kami</h6>
                <a href="https://wa.me/6288216158488?text=Halo%2C%20min%20saya%20perlu%20bantuan%20Anda" target="_blank" rel="noopener" class="bantuan-contact-item">
                    <i class="bi bi-whatsapp"></i>
                    <div>
                        <strong>WhatsApp Admin</strong>
                        <div class="small text-muted">Respon cepat untuk kendala teknis</div>
                    </div>
                </a>
                <a href="mailto:admin@gacoanparkir.com" class="bantuan-contact-item">
                    <i class="bi bi-envelope"></i>
                    <div>
                        <strong>Email</strong>
                        <div class="small text-muted">dedir0901@gmail.com</div>
                    </div>
                </a>
                <div class="bantuan-contact-item" style="cursor:default;">
                    <i class="bi bi-geo-alt"></i>
                    <div>
                        <strong>Lokasi</strong>
                        <div class="small text-muted">Gacoan, area kasir/loket parkir</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tombol Bantuan Mengambang -->
<button type="button" class="help-float-btn" data-bs-toggle="modal" data-bs-target="#modalBantuan" title="Bantuan">
    <i class="bi bi-headset"></i>
</button>

<?php if (isset($_GET['testimoni'])): ?>
<script>
    // Scroll otomatis ke bagian testimoni setelah kirim komentar
    document.addEventListener('DOMContentLoaded', function () {
        const el = document.getElementById('testimoni');
        if (el) el.scrollIntoView({ behavior: 'instant', block: 'start' });
    });
</script>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
    const ctxKepadatan = document.getElementById('chartKepadatan');
    if (ctxKepadatan) {
        new Chart(ctxKepadatan, {
            type: 'bar',
            data: {
                labels: <?= json_encode($labelJam) ?>,
                datasets: [{
                    label: 'Jumlah Kendaraan',
                    data: <?= json_encode($dataKepadatan) ?>,
                    backgroundColor: '#f4b400',
                    borderRadius: 8,
                    maxBarThickness: 46
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { precision: 0, color: 'rgba(238,241,250,.65)' },
                        grid: { color: 'rgba(255,255,255,.08)' }
                    },
                    x: {
                        ticks: { color: 'rgba(238,241,250,.65)' },
                        grid: { display: false }
                    }
                }
            }
        });
    }
</script>
</body>
</html>