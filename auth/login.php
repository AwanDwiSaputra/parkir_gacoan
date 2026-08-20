<?php
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../assets/logo.php';

if (!empty($_SESSION['id_user'])) {
    redirectDashboard();
}

$error = '';
$loginSukses = false;
$redirectUrl = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Username dan password wajib diisi.';
    } else {
        $stmt = $koneksi->prepare("SELECT * FROM tb_user WHERE username = :username LIMIT 1");
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password'])) {
            $error = 'Username atau password salah.';
        } elseif ((int)$user['status_aktif'] !== 1) {
            $error = 'Akun anda nonaktif. Hubungi admin.';
        } elseif (!in_array($user['role'], ['admin', 'petugas', 'owner', 'member'])) {
            $error = 'Akun ini tidak memiliki akses ke dashboard.';
        } else {
            $_SESSION['id_user']      = $user['id_user'];
            $_SESSION['username']     = $user['username'];
            $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
            $_SESSION['role']         = $user['role'];
            $_SESSION['foto']         = $user['foto'];

            catatAktivitas('Login ke sistem');
            $loginSukses = true;
            $redirectUrl = getDashboardUrl();
        }
    }
}

if (isset($_GET['akses']) && $_GET['akses'] === 'ditolak') {
    $error = 'Anda tidak memiliki akses ke halaman tersebut.';
}

// ===== Logo Gacoan (file yang sama dipakai di halaman beranda) =====
// Letakkan file logo di img/logo-gacoan.png (folder ini satu level di atas auth/)
$logoGacoan = BASE_URL . '/img/logo-gacoan.png';
$logoGacoanAda = is_file(__DIR__ . '/../img/logo-gacoan.png');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Parkir Gacoan</title>
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

        .login-wrap {
            display: flex;
            width: 100%;
            max-width: 900px;
            background: #fff;
            border-radius: 22px;
            overflow: hidden;
            box-shadow: 0 30px 70px rgba(0,0,0,.4);
        }

        .login-side {
            flex: 1;
            background: linear-gradient(160deg, var(--gc-navy), var(--gc-navy-deep));
            color: #fff;
            padding: 3rem 2.2rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .login-side .badge-hot {
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

        .login-side .logo-wrap { margin-bottom: 1.3rem; }
        .login-side .logo-wrap img { display: block; height: 64px; width: auto; }

        .login-side h2 {
            font-weight: 800;
            font-size: 1.9rem;
            margin: 0 0 .6rem;
            line-height: 1.15;
        }

        .login-side h2 span { color: var(--gc-gold); }

        .login-side p {
            color: rgba(255,255,255,.72);
            font-size: .92rem;
            max-width: 320px;
        }

        .login-form-side {
            flex: 1;
            padding: 3rem 2.6rem;
            background: #fff;
        }

        .login-form-side h4 {
            font-weight: 800;
            font-size: 1.5rem;
            margin-bottom: .2rem;
            color: var(--gc-navy);
        }

        .login-form-side p.sub {
            color: #7a8296;
            margin-bottom: 1.8rem;
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
            padding: .65rem .9rem;
        }

        .form-control:focus {
            border-color: var(--gc-navy);
            box-shadow: 0 0 0 .18rem rgba(11,31,77,.14);
        }

        .btn-login {
            background: var(--gc-navy);
            color: #fff;
            border: none;
            font-weight: 700;
            border-radius: 12px;
            padding: .68rem;
            box-shadow: 0 6px 18px rgba(11,31,77,.3);
            transition: transform .12s ease, box-shadow .12s ease, background .12s ease;
        }

        .btn-login:hover {
            color: #fff;
            transform: translateY(-1px);
            box-shadow: 0 10px 24px rgba(11,31,77,.4);
            background: var(--gc-navy-deep);
        }

        .alert-login {
            border-radius: 12px;
            border: none;
            font-weight: 600;
            font-size: .87rem;
        }

        .success-screen {
            text-align: center;
            padding: 2.5rem 1rem;
        }

        .success-screen .check-circle {
            width: 74px;
            height: 74px;
            border-radius: 50%;
            background: #e8f7ee;
            color: #1f9d55;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.3rem;
            margin: 0 auto 1.2rem;
        }

        @media (max-width: 767.98px) {
            .login-wrap { flex-direction: column; }
        }
    </style>
</head>
<body>

<div class="login-wrap">
    <div class="login-side">
        <span class="badge-hot"><i class="bi bi-p-circle-fill"></i> Sistem Parkir</span>
        <div class="logo-wrap">
            <?php if ($logoGacoanAda): ?>
                <img src="<?= htmlspecialchars($logoGacoan) ?>" alt="Logo Gacoan">
            <?php else: ?>
                <?= gacoanLogo(64) ?>
            <?php endif; ?>
        </div>
        <h2>PARKIR <span>GACOAN</span></h2>
        <p>Kelola kendaraan masuk, keluar, dan rekap pendapatan parkir dengan cepat dan rapi.</p>
    </div>

    <div class="login-form-side">
        <?php if ($loginSukses): ?>
            <div class="success-screen">
                <div class="check-circle"><i class="bi bi-check-lg"></i></div>
                <h4>Login Berhasil!</h4>
                <p class="sub">Selamat datang, <?= htmlspecialchars($_SESSION['nama_lengkap']) ?>. Mengalihkan ke dashboard...</p>
                <div class="spinner-border" style="width:1.6rem;height:1.6rem;color:var(--gc-navy);" role="status"></div>
            </div>
            <script>
                gacoanPlaySuccess();
                setTimeout(function () {
                    window.location.href = <?= json_encode($redirectUrl) ?>;
                }, 1200);
            </script>
        <?php else: ?>
            <h4>Masuk Akun</h4>
            <p class="sub">Login menggunakan akun Admin, Petugas, Owner, atau Member.</p>

            <?php if ($error): ?>
                <div class="alert alert-danger alert-login py-2"><i class="bi bi-exclamation-triangle-fill"></i> <?= htmlspecialchars($error) ?></div>
                <script>gacoanPlayError();</script>
            <?php endif; ?>

            <form method="POST" autocomplete="off">
                <div class="mb-3">
                    <label class="form-label">Username</label>
                    <input type="text" name="username" class="form-control" required autofocus>
                </div>
                <div class="mb-4">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-login w-100">
                    Masuk Sekarang <i class="bi bi-arrow-right-circle"></i>
                </button>
            </form>

            <p class="text-center small mt-4 mb-0" style="color:#a3aabb;">
                <a href="<?= BASE_URL ?>" style="color:var(--gc-navy);font-weight:600;text-decoration:none;">
                    <i class="bi bi-arrow-left"></i> Kembali ke Beranda
                </a>
            </p>
            <p class="text-center small mt-2 mb-0" style="color:#a3aabb;">
                Pelanggan baru? <a href="register.php" style="color:var(--gc-navy);font-weight:600;text-decoration:none;">Daftar sebagai Member</a>
            </p>
        <?php endif; ?>
    </div>
</div>

</body>
</html>