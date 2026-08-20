<?php
/**
 * config/koneksi.php
 * Koneksi database (PDO) + helper session/hak akses + log aktivitas
 * Parkir Gacoan
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ================== KONFIGURASI DATABASE ==================
define('DB_HOST', 'localhost');
define('DB_NAME', 'gaca');
define('DB_USER', 'root');
define('DB_PASS', '');

// Sesuaikan jika nama folder project anda berbeda
define('BASE_URL', 'http://localhost/parkir_cloud/');

try {
    $koneksi = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    die('Koneksi database gagal: ' . $e->getMessage());
}

// ================== HELPER: PROTEKSI LOGIN & ROLE ==================
/**
 * Memastikan user sudah login. Jika $roles diisi, hanya role tsb yang boleh akses.
 * @param array $roles contoh: ['admin'], ['petugas','owner']
 */
function cekLogin($roles = [])
{
    if (empty($_SESSION['id_user'])) {
        header('Location: ' . BASE_URL . 'auth/login.php');
        exit;
    }
    if (!empty($roles) && !in_array($_SESSION['role'], $roles)) {
        header('Location: ' . BASE_URL . 'auth/login.php?akses=ditolak');
        exit;
    }
}

/**
 * Mengambil URL dashboard sesuai role tanpa langsung redirect.
 */
function getDashboardUrl()
{
    if (empty($_SESSION['id_user'])) return BASE_URL . 'auth/login.php';
    switch ($_SESSION['role']) {
        case 'admin':   return BASE_URL . 'admin/index.php';
        case 'petugas': return BASE_URL . 'operator/index.php';
        case 'owner':   return BASE_URL . 'owner/index.php';
        case 'member':  return BASE_URL . 'member/index.php';
        default:        return BASE_URL . 'auth/login.php';
    }
}

/**
 * Redirect user yang sudah login ke dashboard sesuai role-nya.
 */
function redirectDashboard()
{
    if (empty($_SESSION['id_user'])) return;
    header('Location: ' . getDashboardUrl());
    exit;
}

// ================== HELPER: LOG AKTIVITAS ==================
function catatAktivitas($aktivitas)
{
    global $koneksi;
    if (empty($_SESSION['id_user'])) return;
    $stmt = $koneksi->prepare(
        "INSERT INTO tb_log_aktivitas (id_user, aktivitas, waktu_aktivitas) VALUES (:id_user, :aktivitas, NOW())"
    );
    $stmt->execute([':id_user' => $_SESSION['id_user'], ':aktivitas' => $aktivitas]);
}

// ================== HELPER: FORMAT ==================
function rupiah($angka)
{
    return 'Rp ' . number_format((float)$angka, 0, ',', '.');
}

function formatTanggal($datetime)
{
    if (!$datetime) return '-';
    $bulan = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
    $ts = strtotime($datetime);
    return date('d', $ts) . ' ' . $bulan[(int)date('n', $ts)] . ' ' . date('Y H:i', $ts);
}
