<?php
require_once __DIR__ . '/../config/koneksi.php';
cekLogin(['petugas']);
$page_title = 'Riwayat Transaksi';

$dari   = $_GET['dari'] ?? date('Y-m-01');
$sampai = $_GET['sampai'] ?? date('Y-m-d');

// ==== Hapus transaksi (soft delete: hanya disembunyikan dari riwayat, ====
// ==== TIDAK mengurangi Total Pendapatan yang sudah tercatat) ====
if (isset($_GET['hapus'])) {
    $idHapus = (int) $_GET['hapus'];

    $cek = $koneksi->prepare(
        "SELECT id_parkir FROM tb_transaksi WHERE id_parkir = :id AND id_user = :uid AND status = 'keluar'"
    );
    $cek->execute([':id' => $idHapus, ':uid' => $_SESSION['id_user']]);

    $redirectQuery = 'dari=' . urlencode($dari) . '&sampai=' . urlencode($sampai);

    if ($cek->fetch()) {
        $del = $koneksi->prepare("UPDATE tb_transaksi SET dihapus_riwayat = 1 WHERE id_parkir = :id");
        $del->execute([':id' => $idHapus]);
        header("Location: riwayat_transaksi.php?{$redirectQuery}&sukses=Transaksi%20berhasil%20dihapus%20dari%20riwayat");
        exit;
    } else {
        header("Location: riwayat_transaksi.php?{$redirectQuery}&gagal=Transaksi%20tidak%20ditemukan%20atau%20tidak%20bisa%20dihapus");
        exit;
    }
}

$stmt = $koneksi->prepare(
    "SELECT t.*, k.plat_nomor, k.jenis_kendaraan, a.nama_area
     FROM tb_transaksi t
     JOIN tb_kendaraan k ON k.id_kendaraan = t.id_kendaraan
     JOIN tb_area_parkir a ON a.id_area = t.id_area
     WHERE t.id_user = :id AND t.dihapus_riwayat = 0 AND DATE(t.waktu_masuk) BETWEEN :dari AND :sampai
     ORDER BY t.id_parkir DESC"
);
$stmt->execute([':id' => $_SESSION['id_user'], ':dari' => $dari, ':sampai' => $sampai]);
$riwayat = $stmt->fetchAll();

$totalPendapatan = array_sum(array_column(array_filter($riwayat, fn($r) => $r['status']==='keluar'), 'biaya_total'));

include __DIR__ . '/components/header.php';
?>

<div class="card card-gacoan">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span><i class="bi bi-clock-history"></i> Riwayat Transaksi Saya</span>
        <form method="GET" class="d-flex gap-2 align-items-center">
            <input type="date" name="dari" class="form-control form-control-sm" value="<?= htmlspecialchars($dari) ?>">
            <span class="small">s/d</span>
            <input type="date" name="sampai" class="form-control form-control-sm" value="<?= htmlspecialchars($sampai) ?>">
            <button class="btn btn-sm btn-gacoan">Filter</button>
        </form>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead><tr><th>#</th><th>Plat</th><th>Jenis</th><th>Area</th><th>Masuk</th><th>Keluar</th><th>Biaya</th><th>Status</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($riwayat as $r): ?>
                    <tr id="row-<?= $r['id_parkir'] ?>">
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
                        <td class="text-nowrap">
                            <a href="cetak_struk.php?id=<?= $r['id_parkir'] ?>&tipe=<?= $r['status']==='keluar'?'keluar':'masuk' ?>" class="btn btn-sm btn-outline-gacoan" target="_blank" title="Cetak Struk">
                                <i class="bi bi-printer"></i>
                            </a>
                            <?php if ($r['status']==='keluar'): ?>
                                <a href="riwayat_transaksi.php?hapus=<?= $r['id_parkir'] ?>&dari=<?= urlencode($dari) ?>&sampai=<?= urlencode($sampai) ?>"
                                   class="btn btn-sm btn-outline-danger btn-hapus-transaksi"
                                   data-plat="<?= htmlspecialchars($r['plat_nomor']) ?>"
                                   title="Hapus Transaksi">
                                    <i class="bi bi-trash"></i>
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($riwayat)): ?>
                    <tr><td colspan="9" class="text-center text-muted py-4">Tidak ada transaksi pada rentang tanggal ini</td></tr>
                <?php endif; ?>
                </tbody>
                <?php if (!empty($riwayat)): ?>
                <tfoot>
                    <tr class="fw-bold">
                        <td colspan="6" class="text-end">Total Pendapatan Terkumpul</td>
                        <td colspan="3"><?= rupiah($totalPendapatan) ?></td>
                    </tr>
                </tfoot>
                <?php endif; ?>
            </table>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.btn-hapus-transaksi').forEach(function (btn) {
    btn.addEventListener('click', function (e) {
        var plat = btn.getAttribute('data-plat');
        if (!confirm('Hapus transaksi kendaraan dengan plat "' + plat + '"?\nTindakan ini tidak bisa dibatalkan.')) {
            e.preventDefault();
        }
    });
});
</script>

<?php include __DIR__ . '/components/footer.php'; ?>