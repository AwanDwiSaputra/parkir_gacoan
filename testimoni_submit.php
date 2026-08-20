<?php
require_once __DIR__ . '/config/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . 'index.php');
    exit;
}

$nama     = trim($_POST['nama'] ?? '');
$role     = trim($_POST['role'] ?? '') ?: 'Pengguna';
$rating   = (int)($_POST['rating'] ?? 0);
$komentar = trim($_POST['komentar'] ?? '');

if ($nama === '' || $komentar === '' || $rating < 1 || $rating > 5) {
    header('Location: ' . BASE_URL . 'index.php?testimoni=gagal#testimoni');
    exit;
}

$stmt = $koneksi->prepare(
    "INSERT INTO testimoni (nama, role, rating, komentar, status, created_at)
     VALUES (:nama, :role, :rating, :komentar, 'pending', NOW())"
);
$stmt->execute([
    ':nama'     => $nama,
    ':role'     => $role,
    ':rating'   => $rating,
    ':komentar' => $komentar,
]);

header('Location: ' . BASE_URL . 'index.php?testimoni=sukses#testimoni');
exit;
