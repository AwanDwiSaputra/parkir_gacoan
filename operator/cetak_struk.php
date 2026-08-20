<?php
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../assets/logo.php';
cekLogin(['petugas', 'admin', 'owner']);

$id   = (int)($_GET['id'] ?? 0);
$tipe = $_GET['tipe'] ?? 'masuk';

$stmt = $koneksi->prepare(
    "SELECT t.*, k.plat_nomor, k.jenis_kendaraan, k.pemilik,
            a.nama_area, tf.tarif_per_jam, u.nama_lengkap AS petugas,
            (SELECT COALESCE(SUM(jumlah), 0) FROM tb_denda WHERE id_parkir = t.id_parkir) AS total_denda
     FROM tb_transaksi t
     JOIN tb_kendaraan k ON k.id_kendaraan = t.id_kendaraan
     JOIN tb_area_parkir a ON a.id_area = t.id_area
     JOIN tb_tarif tf ON tf.id_tarif = t.id_tarif
     JOIN tb_user u ON u.id_user = t.id_user
     WHERE t.id_parkir = :id"
);
$stmt->execute([':id' => $id]);
$trx = $stmt->fetch();

if (!$trx) {
    die('Transaksi tidak ditemukan.');
}

$tampilkanQris = ($tipe === 'keluar' && $trx['status'] === 'keluar' && $trx['metode_bayar'] === 'qris');

// Pisahkan biaya parkir normal dari denda untuk ditampilkan rinci di struk
$totalDenda  = (float) ($trx['total_denda'] ?? 0);
$biayaParkir = $trx['biaya_total'] - $totalDenda;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Struk Parkir #<?= $trx['id_parkir'] ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@700;800&family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body { background:#071336; font-family:'Poppins',sans-serif; min-height:100vh; display:flex; align-items:center; justify-content:center; padding:24px; }
        .struk {
            width: 340px;
            background: #fff;
            border-radius: 16px;
            padding: 24px;
            position: relative;
            box-shadow: 0 25px 60px rgba(0,0,0,.4);
        }
        .struk h5 { font-family:'Plus Jakarta Sans',sans-serif; font-weight:800; color:#0b1f4d; }
        .struk hr { border-top: 1.5px dashed #d7dce8; }
        .struk table td { padding: 3px 0; font-size: .85rem; }
        .total-box {
            background:#0b1f4d; color:#fff; border-radius:10px; padding:10px 14px;
            display:flex; justify-content:space-between; font-weight:800; font-size:1.1rem;
        }
        .qris-box {
            background: #f3f5fa;
            border-radius: 14px;
            padding: 16px;
            text-align: center;
            margin-top: 10px;
        }
        .qris-box img { max-width: 100%; height: auto; border-radius: 8px; }
        .qris-badge {
            display: inline-block;
            background: #0b1f4d;
            color: #f4b400;
            font-weight: 800;
            font-size: .72rem;
            letter-spacing: 1.5px;
            padding: 4px 12px;
            border-radius: 50px;
            margin-bottom: 10px;
        }
        @media print { body{background:#fff;} .no-print{display:none!important;} }
    </style>
</head>
<body>
<div>
    <div class="struk">
        <div class="text-center mb-2">
            <div class="d-flex justify-content-center mb-2"><?= gacoanLogo(48) ?></div>
            <h5 class="mb-0">PARKIR GACOAN</h5>
            <div class="small fw-semibold text-uppercase text-muted" style="letter-spacing:1px;">
                <?= $tipe === 'masuk' ? 'Tiket Masuk' : 'Struk Pembayaran' ?>
            </div>
        </div>
        <hr>
        <table class="w-100">
            <tr><td>No. Transaksi</td><td class="text-end fw-semibold">#<?= $trx['id_parkir'] ?></td></tr>
            <tr><td>Plat Nomor</td><td class="text-end fw-bold"><?= htmlspecialchars($trx['plat_nomor']) ?></td></tr>
            <tr><td>Jenis</td><td class="text-end text-capitalize"><?= htmlspecialchars($trx['jenis_kendaraan']) ?></td></tr>
            <tr><td>Pemilik</td><td class="text-end"><?= htmlspecialchars($trx['pemilik']) ?></td></tr>
            <tr><td>Area</td><td class="text-end"><?= htmlspecialchars($trx['nama_area']) ?></td></tr>
            <tr><td>Waktu Masuk</td><td class="text-end"><?= formatTanggal($trx['waktu_masuk']) ?></td></tr>
            <tr><td>Petugas</td><td class="text-end"><?= htmlspecialchars($trx['petugas']) ?></td></tr>
            <?php if ($tipe === 'keluar' && $trx['status'] === 'keluar'): ?>
            <tr><td>Waktu Keluar</td><td class="text-end"><?= formatTanggal($trx['waktu_keluar']) ?></td></tr>
            <tr><td>Durasi</td><td class="text-end"><?= $trx['durasi_jam'] ?> jam</td></tr>
            <tr><td>Metode Bayar</td><td class="text-end text-uppercase"><?= htmlspecialchars($trx['metode_bayar']) ?></td></tr>
            <?php endif; ?>
        </table>
        <hr>
        <?php if ($tipe === 'keluar' && $trx['status'] === 'keluar'): ?>
            <table class="w-100 mb-2">
                <tr><td>Biaya Parkir</td><td class="text-end"><?= rupiah($biayaParkir) ?></td></tr>
                <?php if ($totalDenda > 0): ?>
                <tr>
                    <td class="text-danger"><i class="bi bi-exclamation-circle"></i> Denda Keterlambatan</td>
                    <td class="text-end text-danger"><?= rupiah($totalDenda) ?></td>
                </tr>
                <?php endif; ?>
            </table>

            <div class="total-box"><span>TOTAL BAYAR</span><span><?= rupiah($trx['biaya_total']) ?></span></div>

            <?php if ($tampilkanQris): ?>
            <div class="qris-box">
                <span class="qris-badge"><i class="bi bi-qr-code"></i> QRIS</span>
                <div><img src="../img/qris.jpeg" alt="QRIS Cloud Store"></div>
                <p class="small text-muted mb-0 mt-2">Pindai kode ini untuk konfirmasi pembayaran QRIS</p>
            </div>
            <?php endif; ?>

        <?php else: ?>
            <div class="text-center small fw-semibold">Simpan tiket ini untuk proses keluar.</div>
        <?php endif; ?>
        <hr>
        <div class="text-center small text-muted">Terima kasih & drive safely!</div>
    </div>

    <div class="text-center mt-3 no-print">
        <button class="btn btn-warning fw-bold" onclick="window.print()"><i class="bi bi-printer"></i> Cetak</button>
        <a href="<?= $tipe === 'masuk' ? 'transaksi_masuk.php' : 'transaksi_keluar.php' ?>" class="btn btn-outline-light">Kembali</a>
    </div>
</div>
</body>
</html>