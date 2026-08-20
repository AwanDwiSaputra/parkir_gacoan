<?php
require_once __DIR__ . '/../config/koneksi.php';
cekLogin(['user']);
$page_title = 'Riwayat Parkir';
$id_user = $_SESSION['id_user'];

$stmt = $koneksi->prepare(
    "SELECT t.*, k.plat_nomor, k.jenis_kendaraan, a.nama_area
     FROM tb_transaksi t
     JOIN tb_kendaraan k ON k.id_kendaraan = t.id_kendaraan
     JOIN tb_area_parkir a ON a.id_area = t.id_area
     WHERE k.id_user = ?
     ORDER BY t.id_parkir DESC"
);
$stmt->execute([$id_user]);
$riwayat = $stmt->fetchAll();

include __DIR__ . '/components/header.php';
?>

<div class="card card-gacoan">
    <div class="card-header">Riwayat Parkir Kendaraan Saya</div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead><tr><th>#</th><th>Plat</th><th>Jenis</th><th>Area</th><th>Masuk</th><th>Keluar</th><th>Biaya</th><th>Status</th></tr></thead>
                <tbody>
                <?php foreach ($riwayat as $r): ?>
                    <tr>
                        <td class="small">#<?= $r['id_parkir'] ?></td>
                        <td class="fw-semibold"><?= htmlspecialchars($r['plat_nomor']) ?></td>
                        <td class="text-capitalize"><?= htmlspecialchars($r['jenis_kendaraan']) ?></td>
                        <td><?= htmlspecialchars($r['nama_area']) ?></td>
                        <td class="small"><?= formatTanggal($r['waktu_masuk']) ?></td>
                        <td class="small"><?= formatTanggal($r['waktu_keluar']) ?></td>
                        <td><?= $r['biaya_total'] ? rupiah($r['biaya_total']) : '-' ?></td>
                        <td>
                            <?php if ($r['status']==='masuk'): ?>
                                <span class="badge bg-warning text-dark">Parkir</span>
                            <?php else: ?>
                                <span class="badge bg-success">Selesai</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($riwayat)): ?>
                    <tr><td colspan="8" class="text-center text-muted py-4">Belum ada riwayat parkir</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/components/footer.php'; ?>
