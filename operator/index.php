<?php
require_once __DIR__ . '/../config/koneksi.php';
cekLogin(['petugas']);
$page_title = 'Dashboard Petugas';

$sedangParkir = $koneksi->query("SELECT COUNT(*) c FROM tb_transaksi WHERE status='masuk'")->fetch()['c'];

$bookingMenunggu = $koneksi->query(
    "SELECT COUNT(*) c FROM tb_booking WHERE status IN ('menunggu','dikonfirmasi') AND tanggal_booking = CURDATE()"
)->fetch()['c'];

$masukHariIni = $koneksi->prepare("SELECT COUNT(*) c FROM tb_transaksi WHERE id_user=:id AND DATE(waktu_masuk)=CURDATE()");
$masukHariIni->execute([':id' => $_SESSION['id_user']]);
$masukHariIni = $masukHariIni->fetch()['c'];

$keluarHariIni = $koneksi->prepare("SELECT COUNT(*) c, COALESCE(SUM(biaya_total),0) total FROM tb_transaksi WHERE id_user=:id AND status='keluar' AND DATE(waktu_keluar)=CURDATE()");
$keluarHariIni->execute([':id' => $_SESSION['id_user']]);
$keluarHariIni = $keluarHariIni->fetch();

// Total pendapatan keseluruhan (semua petugas, semua waktu)
$totalPendapatan = $koneksi->query(
    "SELECT COALESCE(SUM(biaya_total),0) total FROM tb_transaksi WHERE status='keluar'"
)->fetch()['total'];

$area = $koneksi->query("SELECT * FROM tb_area_parkir ORDER BY nama_area")->fetchAll();

$transaksiTerbaru = $koneksi->query(
    "SELECT t.*, k.plat_nomor, k.jenis_kendaraan FROM tb_transaksi t
     JOIN tb_kendaraan k ON k.id_kendaraan = t.id_kendaraan
     WHERE t.status = 'masuk'
     ORDER BY t.id_parkir DESC LIMIT 8"
)->fetchAll();

include __DIR__ . '/components/header.php';
?>

<div class="row g-3 mb-3">
    <div class="col-lg-3 col-md-6 col-6">
        <div class="stat-card bg-grad-dark">
            <div class="stat-icon"><i class="bi bi-p-circle"></i></div>
            <h3><?= $sedangParkir ?></h3>
            <span class="label">Sedang Parkir</span>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 col-6">
        <div class="stat-card bg-grad-red">
            <div class="stat-icon"><i class="bi bi-box-arrow-in-right"></i></div>
            <h3><?= $masukHariIni ?></h3>
            <span class="label">Kendaraan Masuk (Saya, Hari Ini)</span>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 col-6">
        <div class="stat-card bg-grad-green">
            <div class="stat-icon"><i class="bi bi-cash-coin"></i></div>
            <h3 style="font-size:1.15rem;"><?= $keluarHariIni['c'] ?> transaksi / <?= rupiah($keluarHariIni['total']) ?></h3>
            <span class="label">Keluar (Saya, Hari Ini)</span>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 col-6">
        <div class="stat-card" style="background: linear-gradient(135deg, #f5a623 0%, #c77c11 100%); color:#fff;">
            <div class="stat-icon"><i class="bi bi-wallet2"></i></div>
            <h3 style="font-size:1.15rem;"><?= rupiah($totalPendapatan) ?></h3>
            <span class="label">Total Pendapatan</span>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-4">
        <div class="card card-gacoan">
            <div class="card-header">Ketersediaan Slot Area</div>
            <div class="card-body">
                <?php foreach ($area as $a): $sisa = $a['kapasitas']-$a['terisi']; $p = $a['kapasitas']>0 ? round(($a['terisi']/$a['kapasitas'])*100) : 0; ?>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between small fw-semibold mb-1">
                            <span><?= htmlspecialchars($a['nama_area']) ?></span>
                            <span><?= $a['terisi'] ?>/<?= $a['kapasitas'] ?></span>
                        </div>
                        <div class="progress" style="height:10px;border-radius:50px;">
                            <div class="progress-bar <?= $p>=90?'bg-danger':($p>=60?'bg-warning':'bg-success') ?>" style="width:<?= $p ?>%"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php if ($bookingMenunggu > 0): ?>
        <div class="card card-gacoan mt-3">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <strong><?= $bookingMenunggu ?> Booking Hari Ini</strong>
                    <div class="small text-muted">Siap di-check-in otomatis</div>
                </div>
                <a href="transaksi_masuk.php" class="btn btn-gacoan btn-sm">
                    <i class="bi bi-lightning-charge-fill"></i> Check-in
                </a>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <div class="col-lg-8">
        <div class="card card-gacoan">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Transaksi Terbaru</span>
                <div class="d-flex gap-2">
                    <a href="transaksi_masuk.php" class="btn btn-gacoan btn-sm">+ Kendaraan Masuk</a>
                    <a href="transaksi_keluar.php" class="btn btn-outline-gacoan btn-sm">Kendaraan Keluar</a>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead><tr><th>#</th><th>Plat</th><th>Jenis</th><th>Masuk</th><th>Status</th></tr></thead>
                        <tbody>
                        <?php foreach ($transaksiTerbaru as $t): ?>
                            <tr id="row-<?= $t['id_parkir'] ?>">
                                <td class="small">#<?= $t['id_parkir'] ?></td>
                                <td class="fw-semibold"><?= htmlspecialchars($t['plat_nomor']) ?></td>
                                <td class="text-capitalize"><?= htmlspecialchars($t['jenis_kendaraan']) ?></td>
                                <td class="small"><?= formatTanggal($t['waktu_masuk']) ?></td>
                                <td><span class="badge bg-warning text-dark">Parkir</span></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($transaksiTerbaru)): ?>
                            <tr><td colspan="5" class="text-center text-muted py-4">Tidak ada kendaraan sedang parkir</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/components/footer.php'; ?>