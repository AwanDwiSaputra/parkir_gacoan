<?php
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/cek_denda_parkir.php';
cekLogin(['petugas']);
$page_title = 'Kendaraan Keluar';

// Cek otomatis: kendaraan yang sudah parkir lebih dari batas waktu langsung dicatat dendanya
cekDendaParkir($koneksi);

$error = '';
$search = trim($_GET['q'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_transaksi'])) {
    $id_transaksi = (int)$_POST['id_transaksi'];
    $metode_bayar = $_POST['metode_bayar'] ?? 'tunai';

    try {
        $koneksi->beginTransaction();

        $stmt = $koneksi->prepare(
            "SELECT t.*, tf.tarif_per_jam
             FROM tb_transaksi t
             JOIN tb_tarif tf ON tf.id_tarif = t.id_tarif
             WHERE t.id_parkir = :id AND t.status='masuk' FOR UPDATE"
        );
        $stmt->execute([':id' => $id_transaksi]);
        $trx = $stmt->fetch();

        if (!$trx) {
            throw new Exception('Transaksi tidak ditemukan atau sudah selesai.');
        }

        $waktuMasuk  = new DateTime($trx['waktu_masuk']);
        $waktuKeluar = new DateTime();
        $selisihMenit = max(ceil(($waktuKeluar->getTimestamp() - $waktuMasuk->getTimestamp()) / 60), 1);
        $durasiJam = max((int)ceil($selisihMenit / 60), 1);
        $biayaParkir = $durasiJam * $trx['tarif_per_jam'];

        // Ambil total denda yang belum dibayar untuk transaksi ini
        $stmtDenda = $koneksi->prepare(
            "SELECT COALESCE(SUM(jumlah), 0) AS total_denda
             FROM tb_denda WHERE id_parkir = :id AND status = 'belum_bayar'"
        );
        $stmtDenda->execute([':id' => $id_transaksi]);
        $totalDenda = (float) $stmtDenda->fetchColumn();

        // Biaya total = biaya parkir normal + denda (kalau ada)
        $biaya = $biayaParkir + $totalDenda;

        $koneksi->prepare(
            "UPDATE tb_transaksi SET waktu_keluar=NOW(), durasi_jam=:d, biaya_total=:b, metode_bayar=:m, status='keluar'
             WHERE id_parkir=:id"
        )->execute([':d'=>$durasiJam, ':b'=>$biaya, ':m'=>$metode_bayar, ':id'=>$id_transaksi]);

        $koneksi->prepare("UPDATE tb_area_parkir SET terisi = GREATEST(terisi-1,0) WHERE id_area=:id")
            ->execute([':id' => $trx['id_area']]);

        // Tandai denda (kalau ada) sebagai lunas, dianggap dibayar bersamaan saat checkout
        $koneksi->prepare(
            "UPDATE tb_denda SET status='sudah_bayar', dibayar_at=NOW()
             WHERE id_parkir=:id AND status='belum_bayar'"
        )->execute([':id' => $id_transaksi]);

        $koneksi->commit();
        catatAktivitas("Memproses kendaraan keluar transaksi #$id_transaksi");

        header('Location: cetak_struk.php?id=' . $id_transaksi . '&tipe=keluar');
        exit;

    } catch (Exception $e) {
        $koneksi->rollBack();
        $error = $e->getMessage();
    }
}

$sql = "SELECT t.*, k.plat_nomor, k.jenis_kendaraan, a.nama_area,
               d.total_denda
        FROM tb_transaksi t
        JOIN tb_kendaraan k ON k.id_kendaraan = t.id_kendaraan
        JOIN tb_area_parkir a ON a.id_area = t.id_area
        LEFT JOIN (
            SELECT id_parkir, SUM(jumlah) AS total_denda
            FROM tb_denda
            WHERE status = 'belum_bayar' AND id_parkir IS NOT NULL
            GROUP BY id_parkir
        ) d ON d.id_parkir = t.id_parkir
        WHERE t.status='masuk'";
$params = [];
if ($search !== '') {
    $sql .= " AND k.plat_nomor LIKE :q";
    $params[':q'] = "%$search%";
}
$sql .= " ORDER BY t.waktu_masuk ASC";
$stmt = $koneksi->prepare($sql);
$stmt->execute($params);
$daftar = $stmt->fetchAll();

include __DIR__ . '/components/header.php';
?>

<?php if ($error): ?>
    <div class="alert alert-danger alert-auto-dismiss"><i class="bi bi-exclamation-triangle-fill me-1"></i> <?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="card card-gacoan">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span><i class="bi bi-box-arrow-right"></i> Kendaraan Sedang Parkir</span>
        <form method="GET" class="d-flex">
            <input type="text" name="q" class="form-control form-control-sm" placeholder="Cari plat nomor..." value="<?= htmlspecialchars($search) ?>">
            <button class="btn btn-sm btn-outline-gacoan ms-1"><i class="bi bi-search"></i></button>
        </form>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead><tr><th>#</th><th>Plat Nomor</th><th>Jenis</th><th>Area</th><th>Waktu Masuk</th><th>Durasi</th><th class="text-end">Aksi</th></tr></thead>
                <tbody>
                <?php foreach ($daftar as $d): ?>
                    <?php $masukTs = strtotime($d['waktu_masuk']); ?>
                    <tr>
                        <td class="small">#<?= $d['id_parkir'] ?></td>
                        <td class="fw-semibold"><?= htmlspecialchars($d['plat_nomor']) ?></td>
                        <td class="text-capitalize"><?= htmlspecialchars($d['jenis_kendaraan']) ?></td>
                        <td><?= htmlspecialchars($d['nama_area']) ?></td>
                        <td class="small"><?= formatTanggal($d['waktu_masuk']) ?></td>
                        <td>
                            <div class="small durasi-parkir" data-masuk="<?= $masukTs ?>">menghitung...</div>
                            <?php if (!empty($d['total_denda'])): ?>
                                <span class="badge bg-danger mt-1">
                                    <i class="bi bi-cash-coin"></i> Denda Rp <?= number_format($d['total_denda'], 0, ',', '.') ?>
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <form method="POST" class="d-flex gap-1 justify-content-end" onsubmit="return confirm('Proses kendaraan keluar untuk plat <?= htmlspecialchars($d['plat_nomor']) ?>?<?= !empty($d['total_denda']) ? ' Termasuk denda Rp ' . number_format($d['total_denda'], 0, ',', '.') . ' yang akan otomatis dilunasi.' : '' ?>')">
                                <input type="hidden" name="id_transaksi" value="<?= $d['id_parkir'] ?>">
                                <select name="metode_bayar" class="form-select form-select-sm" style="max-width:110px;">
                                    <option value="tunai">Tunai</option>
                                    <option value="qris">QRIS</option>
                                </select>
                                <button type="submit" class="btn btn-sm btn-gacoan">
                                    <i class="bi bi-check2-circle"></i> Keluar
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($daftar)): ?>
                    <tr><td colspan="7" class="text-center text-muted py-4">Tidak ada kendaraan sedang parkir</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
// ===== Durasi parkir live untuk setiap kendaraan yang sedang parkir =====
// Kendaraan yang sudah lebih dari 15 menit ditandai merah sebagai peringatan visual.
(function () {
    const BATAS_DETIK = 15 * 60; // samakan dengan BATAS_PARKIR_MENIT di cek_denda_parkir.php
    const rows = document.querySelectorAll('.durasi-parkir');
    if (!rows.length) return;

    function format(detik) {
        const j = Math.floor(detik / 3600);
        const m = Math.floor((detik % 3600) / 60);
        const s = detik % 60;
        if (j > 0) return j + 'j ' + String(m).padStart(2, '0') + 'm';
        return m + ':' + String(s).padStart(2, '0');
    }

    function tick() {
        const now = Math.floor(Date.now() / 1000);
        rows.forEach(function (el) {
            const masuk = parseInt(el.dataset.masuk, 10);
            const durasi = now - masuk;
            el.textContent = format(durasi);
            el.classList.toggle('text-danger', durasi > BATAS_DETIK);
            el.classList.toggle('fw-bold', durasi > BATAS_DETIK);
        });
    }

    tick();
    setInterval(tick, 1000);
})();
</script>

<?php include __DIR__ . '/components/footer.php'; ?>