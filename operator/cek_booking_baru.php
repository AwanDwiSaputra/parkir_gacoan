<?php
require_once __DIR__ . '/../config/koneksi.php';

if (!isset($_SESSION['id_user']) || !in_array($_SESSION['role'], ['petugas', 'admin'])) {
    http_response_code(403);
    exit;
}

header('Content-Type: application/json');

// ===== Batas waktu menunggu (dalam menit) =====
// Booking dengan status 'menunggu' yang sudah melewati batas ini
// akan otomatis dianggap kedaluwarsa dan tidak lagi dihitung.
const BATAS_WAKTU_MENIT = 15;

// 1) Auto-expire booking yang sudah melewati batas waktu.
//    Sesuaikan nama kolom waktu (created_at) jika berbeda di tabel Anda.
$stmtExpire = $koneksi->prepare(
    "UPDATE tb_booking
     SET status = 'kedaluwarsa'
     WHERE status = 'menunggu'
       AND created_at < (NOW() - INTERVAL :batas MINUTE)"
);
$stmtExpire->execute(['batas' => BATAS_WAKTU_MENIT]);

// 2) Ambil sisa booking yang masih menunggu (dalam batas waktu)
$stmt = $koneksi->query(
    "SELECT COUNT(*) AS jumlah, MAX(id_booking) AS id_terbaru
     FROM tb_booking
     WHERE status = 'menunggu'"
);
$data = $stmt->fetch();

// 3) Hitung sisa waktu (detik) untuk booking terbaru, untuk kebutuhan countdown di frontend
$sisaDetik = null;
if ($data['id_terbaru']) {
    $stmtWaktu = $koneksi->prepare(
        "SELECT created_at FROM tb_booking WHERE id_booking = :id LIMIT 1"
    );
    $stmtWaktu->execute(['id' => $data['id_terbaru']]);
    $bookingTerbaru = $stmtWaktu->fetch();

    if ($bookingTerbaru && $bookingTerbaru['created_at']) {
        $waktuDibuat = strtotime($bookingTerbaru['created_at']);
        $batasDetik  = BATAS_WAKTU_MENIT * 60;
        $terpakai    = time() - $waktuDibuat;
        $sisaDetik   = max(0, $batasDetik - $terpakai);
    }
}

echo json_encode([
    'jumlah_menunggu'   => (int)$data['jumlah'],
    'id_terbaru'        => $data['id_terbaru'] ? (int)$data['id_terbaru'] : 0,
    'batas_waktu_menit' => BATAS_WAKTU_MENIT,
    'sisa_detik'        => $sisaDetik,
]);