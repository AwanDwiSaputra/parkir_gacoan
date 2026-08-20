<?php
require_once __DIR__ . '/../config/koneksi.php';
cekLogin(['user']);
$page_title = 'Dashboard User';
$id_user = $_SESSION['id_user'];

$totalKendaraan = $koneksi->prepare("SELECT COUNT(*) c FROM tb_kendaraan WHERE id_user = ?");
$totalKendaraan->execute([$id_user]);
$totalKendaraan = $totalKendaraan->fetch()['c'];

$bookingAktif = $koneksi->prepare("SELECT COUNT(*) c FROM tb_booking WHERE id_user = ? AND status IN ('menunggu','dikonfirmasi')");
$bookingAktif->execute([$id_user]);
$bookingAktif = $bookingAktif->fetch()['c'];

$sedangParkir = $koneksi->prepare(
    "SELECT COUNT(*) c FROM tb_transaksi t
     JOIN tb_kendaraan k ON k.id_kendaraan = t.id_kendaraan
     WHERE k.id_user = ? AND t.status = 'masuk'"
);
$sedangParkir->execute([$id_user]);
$sedangParkir = $sedangParkir->fetch()['c'];

$totalKunjungan = $koneksi->prepare(
    "SELECT COUNT(*) c FROM tb_transaksi t
     JOIN tb_kendaraan k ON k.id_kendaraan = t.id_kendaraan
     WHERE k.id_user = ? AND t.status = 'keluar'"
);
$totalKunjungan->execute([$id_user]);
$totalKunjungan = $totalKunjungan->fetch()['c'];

$bookingTerbaru = $koneksi->prepare(
    "SELECT b.*, k.plat_nomor, a.nama_area
     FROM tb_booking b
     JOIN tb_kendaraan k ON k.id_kendaraan = b.id_kendaraan
     LEFT JOIN tb_area_parkir a ON a.id_area = b.id_area
     WHERE b.id_user = ?
     ORDER BY b.created_at DESC LIMIT 5"
);
$bookingTerbaru->execute([$id_user]);
$bookingTerbaru = $bookingTerbaru->fetchAll();

include __DIR__ . '/components/header.php';
?>

<div class="row g-3 mb-3">
    <div class="col-md-3 col-6">
        <div class="stat-card bg-grad-red">
            <div class="stat-icon"><i class="bi bi-car-front"></i></div>
            <h3><?= $totalKendaraan ?></h3>
            <span class="label">Kendaraan Terdaftar</span>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="stat-card bg-grad-orange">
            <div class="stat-icon"><i class="bi bi-calendar-check"></i></div>
            <h3><?= $bookingAktif ?></h3>
            <span class="label">Booking Aktif</span>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="stat-card bg-grad-dark">
            <div class="stat-icon"><i class="bi bi-p-circle"></i></div>
            <h3><?= $sedangParkir ?></h3>
            <span class="label">Sedang Parkir</span>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="stat-card bg-grad-green">
            <div class="stat-icon"><i class="bi bi-clock-history"></i></div>
            <h3><?= $totalKunjungan ?></h3>
            <span class="label">Total Kunjungan</span>
        </div>
    </div>
</div>

<div class="card card-gacoan">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Booking Terbaru</span>
        <a href="booking.php" class="btn btn-gacoan btn-sm"><i class="bi bi-plus-lg"></i> Booking Baru</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead><tr><th>Plat</th><th>Area</th><th>Tanggal</th><th>Jam</th><th>Status</th></tr></thead>
                <tbody>
                <?php foreach ($bookingTerbaru as $b): ?>
                    <tr>
                        <td class="fw-semibold"><?= htmlspecialchars($b['plat_nomor']) ?></td>
                        <td><?= htmlspecialchars($b['nama_area'] ?? '-') ?></td>
                        <td class="small"><?= date('d M Y', strtotime($b['tanggal_booking'])) ?></td>
                        <td class="small"><?= substr($b['jam_booking'], 0, 5) ?></td>
                        <td>
                            <?php
                            $badge = [
                                'menunggu' => 'bg-warning text-dark',
                                'dikonfirmasi' => 'bg-primary',
                                'selesai' => 'bg-success',
                                'dibatalkan' => 'bg-secondary',
                            ][$b['status']];
                            ?>
                            <span class="badge <?= $badge ?>"><?= ucfirst($b['status']) ?></span>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($bookingTerbaru)): ?>
                    <tr><td colspan="5" class="text-center text-muted py-4">Belum ada booking. Yuk booking slot parkir pertamamu!</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/components/footer.php'; ?>
