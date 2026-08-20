<?php
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../assets/logo.php';

if (!empty($_SESSION['id_user'])) {
    redirectDashboard();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_lengkap = trim($_POST['nama_lengkap'] ?? '');
    $username     = trim($_POST['username'] ?? '');
    $no_telepon   = trim($_POST['no_telepon'] ?? '');
    $password     = $_POST['password'] ?? '';
    $konfirmasi   = $_POST['konfirmasi'] ?? '';

    if ($nama_lengkap === '' || $username === '' || $no_telepon === '' || $password === '') {
        $error = 'Semua field wajib diisi.';
    } elseif (!preg_match('/^[0-9+\s-]{9,15}$/', $no_telepon)) {
        $error = 'Format nomor telepon tidak valid.';
    } elseif ($password !== $konfirmasi) {
        $error = 'Konfirmasi password tidak sama.';
    } elseif (strlen($password) < 5) {
        $error = 'Password minimal 5 karakter.';
    } else {
        $cek = $koneksi->prepare("SELECT id_user FROM tb_user WHERE username=:u");
        $cek->execute([':u' => $username]);

        if ($cek->fetch()) {
            $error = 'Username sudah digunakan, silakan pilih yang lain.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $koneksi->prepare(
                "INSERT INTO tb_user (nama_lengkap, username, no_telepon, password, role, status_aktif)
                 VALUES (:n, :u, :t, :p, 'member', 1)"
            );
            $stmt->execute([':n' => $nama_lengkap, ':u' => $username, ':t' => $no_telepon, ':p' => $hash]);
            $success = 'Pendaftaran berhasil! Silakan login menggunakan akun barumu.';
        }
    }
}

// ===== Logo Gacoan (file yang sama dipakai di halaman beranda & login) =====
// register.php ada di folder auth/, jadi naik satu folder ke img/logo-gacoan.png
$logoGacoan = BASE_URL . '/img/logo-gacoan.png';
$logoGacoanAda = is_file(__DIR__ . '/../img/logo-gacoan.png');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun — Parkir Gacoan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@600;700;800&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="<?= BASE_URL ?>assets/js/sound.js"></script>
    <style>
        :root {
            --gc-navy: #0b1f4d;
            --gc-navy-deep: #071336;
            --gc-gold: #f4b400;
            --gc-bg: #f3f5fa;
            --gc-line: #e1e5f0;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            font-family: 'Poppins', sans-serif;
            background:
                linear-gradient(160deg, rgba(7,19,54,.3), rgba(11,31,77,.22)),
                url('<?= BASE_URL ?>img/bg-login.png') center/cover no-repeat fixed;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }
        h1, h2, h3 { font-family: 'Plus Jakarta Sans', sans-serif; }

        .register-wrap {
            display: flex;
            width: 100%;
            max-width: 900px;
            background: #fff;
            border-radius: 22px;
            overflow: hidden;
            box-shadow: 0 30px 70px rgba(0,0,0,.4);
        }

        .register-side {
            flex: 1;
            background: linear-gradient(160deg, var(--gc-navy), var(--gc-navy-deep));
            color: #fff;
            padding: 3rem 2.2rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .register-side .badge-hot {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(244,180,0,.15);
            color: var(--gc-gold);
            font-weight: 700;
            font-size: .72rem;
            letter-spacing: 1.5px;
            padding: 6px 14px;
            border-radius: 50px;
            border: 1px solid rgba(244,180,0,.4);
            width: fit-content;
            margin-bottom: 1.4rem;
            text-transform: uppercase;
        }

        .register-side .logo-wrap { margin-bottom: 1.3rem; }
        .register-side .logo-wrap img { display: block; height: 64px; width: auto; }

        .register-side h2 {
            font-weight: 800;
            font-size: 1.8rem;
            margin: 0 0 .6rem;
            line-height: 1.15;
        }

        .register-side h2 span { color: var(--gc-gold); }

        .register-side p {
            color: rgba(255,255,255,.72);
            font-size: .92rem;
            max-width: 320px;
        }

        .register-side ul {
            padding-left: 1.1rem;
            margin-top: 1rem;
            color: rgba(255,255,255,.72);
            font-size: .85rem;
        }
        .register-side ul li { margin-bottom: 6px; }

        .register-form-side {
            flex: 1;
            padding: 3rem 2.6rem;
            background: #fff;
        }

        .register-form-side h4 {
            font-weight: 800;
            font-size: 1.5rem;
            margin-bottom: .2rem;
            color: var(--gc-navy);
        }

        .register-form-side p.sub {
            color: #7a8296;
            margin-bottom: 1.6rem;
            font-size: .9rem;
        }

        .form-label {
            font-weight: 700;
            font-size: .82rem;
            color: var(--gc-navy);
        }

        .form-control {
            border: 1.5px solid var(--gc-line);
            border-radius: 12px;
            padding: .6rem .9rem;
        }

        .form-control:focus {
            border-color: var(--gc-navy);
            box-shadow: 0 0 0 .18rem rgba(11,31,77,.14);
        }

        .btn-register {
            background: var(--gc-navy);
            color: #fff;
            border: none;
            font-weight: 700;
            border-radius: 12px;
            padding: .6rem;
            box-shadow: 0 6px 18px rgba(11,31,77,.3);
            transition: transform .12s ease, box-shadow .12s ease, background .12s ease;
        }

        .btn-register:hover {
            color: #fff;
            transform: translateY(-1px);
            box-shadow: 0 10px 24px rgba(11,31,77,.4);
            background: var(--gc-navy-deep);
        }

        .alert-box {
            border-radius: 12px;
            border: none;
            font-weight: 600;
            font-size: .87rem;
        }

        @media (max-width: 767.98px) {
            .register-wrap { flex-direction: column; }
        }
    </style>
</head>
<body>

<div class="register-wrap">
    <div class="register-side">
        <span class="badge-hot"><i class="bi bi-person-plus-fill"></i> Akun Member</span>
        <div class="logo-wrap">
            <?php if ($logoGacoanAda): ?>
                <img src="<?= htmlspecialchars($logoGacoan) ?>" alt="Logo Gacoan">
            <?php else: ?>
                <?= gacoanLogo(64) ?>
            <?php endif; ?>
        </div>
        <h2>GABUNG JADI <span>MEMBER</span></h2>
        <p>Daftar untuk booking slot parkir lebih awal dan pantau riwayat kunjunganmu.</p>
        <ul>
            <li>Booking slot parkir sebelum datang</li>
            <li>Riwayat kendaraan &amp; kunjungan tersimpan</li>
            <li>Bisa kirim testimoni &amp; rating</li>
        </ul>
    </div>

    <div class="register-form-side">
        <h4>Daftar Akun Member</h4>
        <p class="sub">Gratis, cukup isi data di bawah ini.</p>

        <?php if ($error): ?>
            <div class="alert alert-danger alert-box py-2"><i class="bi bi-exclamation-triangle-fill"></i> <?= htmlspecialchars($error) ?></div>
            <script src="<?= BASE_URL ?>assets/js/sound.js"></script>
            <script>gacoanPlayError();</script>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success alert-box py-2"><i class="bi bi-check-circle-fill"></i> <?= htmlspecialchars($success) ?></div>
            <p class="small mb-3" style="color:#a3aabb;">
                Catatan: dashboard khusus member (booking online) masih dalam pengembangan. Untuk sementara, silakan booking langsung ke petugas di lokasi.
            </p>
            <script>gacoanPlaySuccess();</script>
        <?php endif; ?>

        <?php if (!$success): ?>
        <form method="POST" autocomplete="off">
            <div class="mb-3">
                <label class="form-label">Nama Lengkap</label>
                <input type="text" name="nama_lengkap" class="form-control" required autofocus value="<?= htmlspecialchars($_POST['nama_lengkap'] ?? '') ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" required value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">No. Telepon</label>
                <input type="tel" name="no_telepon" class="form-control" required placeholder="08xxxxxxxxxx" value="<?= htmlspecialchars($_POST['no_telepon'] ?? '') ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <div class="mb-4">
                <label class="form-label">Konfirmasi Password</label>
                <input type="password" name="konfirmasi" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-register w-100">
                Daftar Sekarang <i class="bi bi-arrow-right-circle"></i>
            </button>
        </form>
        <?php else: ?>
            <a href="<?= BASE_URL ?>" class="btn btn-register w-100">
                Kembali ke Beranda <i class="bi bi-house"></i>
            </a>
        <?php endif; ?>

        <p class="text-center small mt-4 mb-0" style="color:#a3aabb;">
            Sudah punya akun?
            <a href="login.php" style="color:var(--gc-navy);font-weight:600;text-decoration:none;">Login di sini</a>
            &middot;
            <a href="<?= BASE_URL ?>" style="color:var(--gc-navy);font-weight:600;text-decoration:none;">Beranda</a>
        </p>
    </div>
</div>

</body>
</html>