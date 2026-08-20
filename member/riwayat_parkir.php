<?php
require_once __DIR__ . '/../config/koneksi.php';
cekLogin(['member']);
$page_title = 'Riwayat Parkir';
$id_user = $_SESSION['id_user'];

// ==== Hapus dari riwayat (soft delete: hanya disembunyikan dari tampilan ====
// ==== member, TIDAK mempengaruhi data/riwayat di sisi petugas maupun ====
// ==== Total Pendapatan) ====
if (isset($_GET['hapus'])) {
    $idHapus = (int) $_GET['hapus'];

    $cek = $koneksi->prepare(
        "SELECT t.id_parkir
         FROM tb_transaksi t
         JOIN tb_kendaraan k ON k.id_kendaraan = t.id_kendaraan
         WHERE t.id_parkir = :id AND k.id_user = :uid AND t.status = 'keluar'"
    );
    $cek->execute([':id' => $idHapus, ':uid' => $id_user]);

    if ($cek->fetch()) {
        $koneksi->prepare("UPDATE tb_transaksi SET dihapus_riwayat_member = 1 WHERE id_parkir = :id")
                ->execute([':id' => $idHapus]);
        header('Location: riwayat_parkir.php?sukses=Riwayat%20berhasil%20dihapus');
    } else {
        header('Location: riwayat_parkir.php?gagal=Riwayat%20tidak%20ditemukan%20atau%20tidak%20bisa%20dihapus');
    }
    exit;
}

$stmt = $koneksi->prepare(
    "SELECT t.*, k.plat_nomor, k.jenis_kendaraan, a.nama_area
     FROM tb_transaksi t
     JOIN tb_kendaraan k ON k.id_kendaraan = t.id_kendaraan
     JOIN tb_area_parkir a ON a.id_area = t.id_area
     WHERE k.id_user = ? AND t.dihapus_riwayat_member = 0
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
                        <td class="text-end">
                            <?php if ($r['status']==='keluar'): ?>
                                <a href="riwayat_parkir.php?hapus=<?= $r['id_parkir'] ?>"
                                   class="btn btn-sm btn-outline-danger btn-hapus-riwayat"
                                   data-plat="<?= htmlspecialchars($r['plat_nomor']) ?>"
                                   title="Hapus dari riwayat">
                                    <i class="bi bi-trash"></i>
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($riwayat)): ?>
                    <tr><td colspan="9" class="text-center text-muted py-4">Belum ada riwayat parkir</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.btn-hapus-riwayat').forEach(function (btn) {
    btn.addEventListener('click', function (e) {
        var plat = btn.getAttribute('data-plat');
        if (!confirm('Hapus riwayat parkir untuk plat "' + plat + '" dari daftar ini?\nTindakan ini tidak bisa dibatalkan.')) {
            e.preventDefault();
        }
    });
});
</script>

<?php include __DIR__ . '/components/footer.php'; ?>