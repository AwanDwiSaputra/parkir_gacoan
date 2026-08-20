<?php
require_once __DIR__ . '/../config/koneksi.php';
cekLogin(['admin']);
$page_title = 'Kelola Tarif';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['aksi'] ?? '') === 'tambah') {
    $stmt = $koneksi->prepare("INSERT INTO tb_tarif (jenis_kendaraan, tarif_per_jam) VALUES (:j,:t)");
    $stmt->execute([':j' => $_POST['jenis_kendaraan'], ':t' => $_POST['tarif_per_jam']]);
    catatAktivitas("Menambahkan tarif jenis " . $_POST['jenis_kendaraan']);
    header('Location: kelola_tarif.php?sukses=Tarif berhasil ditambahkan');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['aksi'] ?? '') === 'edit') {
    $stmt = $koneksi->prepare("UPDATE tb_tarif SET jenis_kendaraan=:j, tarif_per_jam=:t WHERE id_tarif=:id");
    $stmt->execute([':j' => $_POST['jenis_kendaraan'], ':t' => $_POST['tarif_per_jam'], ':id' => $_POST['id_tarif']]);
    catatAktivitas("Mengubah tarif ID " . $_POST['id_tarif']);
    header('Location: kelola_tarif.php?sukses=Tarif berhasil diperbarui');
    exit;
}

if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    try {
        $koneksi->prepare("DELETE FROM tb_tarif WHERE id_tarif=:id")->execute([':id'=>$id]);
        catatAktivitas("Menghapus tarif ID $id");
        header('Location: kelola_tarif.php?sukses=Tarif berhasil dihapus');
    } catch (PDOException $e) {
        header('Location: kelola_tarif.php?gagal=Tarif tidak dapat dihapus, masih dipakai transaksi');
    }
    exit;
}

$tarifs = $koneksi->query("SELECT * FROM tb_tarif ORDER BY id_tarif DESC")->fetchAll();

include __DIR__ . '/template/header.php';
?>

<div class="card card-gacoan">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Daftar Tarif Parkir</span>
        <button class="btn btn-gacoan btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambah">
            <i class="bi bi-plus-lg"></i> Tambah Tarif
        </button>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead><tr><th>#</th><th>Jenis Kendaraan</th><th>Tarif per Jam</th><th class="text-end">Aksi</th></tr></thead>
                <tbody>
                <?php foreach ($tarifs as $i => $t): ?>
                    <tr>
                        <td><?= $i+1 ?></td>
                        <td class="text-capitalize fw-semibold"><?= htmlspecialchars($t['jenis_kendaraan']) ?></td>
                        <td><?= rupiah($t['tarif_per_jam']) ?></td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-outline-gacoan" data-bs-toggle="modal" data-bs-target="#modalEdit<?= $t['id_tarif'] ?>">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <a href="?hapus=<?= $t['id_tarif'] ?>" class="btn btn-sm btn-outline-gacoan" onclick="return confirm('Hapus tarif ini?')">
                                <i class="bi bi-trash"></i>
                            </a>
                        </td>
                    </tr>

                    <div class="modal fade" id="modalEdit<?= $t['id_tarif'] ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form method="POST">
                                    <input type="hidden" name="aksi" value="edit">
                                    <input type="hidden" name="id_tarif" value="<?= $t['id_tarif'] ?>">
                                    <div class="modal-header">
                                        <h6 class="modal-title">Edit Tarif</h6>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-2">
                                            <label class="form-label">Jenis Kendaraan</label>
                                            <select name="jenis_kendaraan" class="form-select">
                                                <?php foreach (['motor','mobil','truk/bus'] as $j): ?>
                                                    <option value="<?= $j ?>" <?= $t['jenis_kendaraan']===$j?'selected':'' ?>><?= ucfirst($j) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="mb-2">
                                            <label class="form-label">Tarif per Jam</label>
                                            <input type="number" step="1" name="tarif_per_jam" class="form-control" value="<?= $t['tarif_per_jam'] ?>" required>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-outline-gacoan" data-bs-dismiss="modal">Batal</button>
                                        <button type="submit" class="btn btn-gacoan">Simpan</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                <?php if (empty($tarifs)): ?>
                    <tr><td colspan="4" class="text-center text-muted py-4">Belum ada data tarif</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalTambah" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="aksi" value="tambah">
                <div class="modal-header">
                    <h6 class="modal-title">Tambah Tarif</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label">Jenis Kendaraan</label>
                        <select name="jenis_kendaraan" class="form-select">
                            <option value="motor">Motor</option>
                            <option value="mobil">Mobil</option>
                            <option value="truk/bus">Truk/Bus</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Tarif per Jam</label>
                        <input type="number" step="1" name="tarif_per_jam" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-gacoan" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-gacoan">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/template/footer.php'; ?>
