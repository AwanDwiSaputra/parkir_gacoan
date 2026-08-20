<?php
require_once __DIR__ . '/../config/koneksi.php';
cekLogin(['admin']);
$page_title = 'Area Parkir';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['aksi'] ?? '') === 'tambah') {
    $stmt = $koneksi->prepare("INSERT INTO tb_area_parkir (nama_area, kapasitas, terisi) VALUES (:n,:k,0)");
    $stmt->execute([':n' => $_POST['nama_area'], ':k' => $_POST['kapasitas']]);
    catatAktivitas("Menambahkan area parkir: " . $_POST['nama_area']);
    header('Location: kelola_area.php?sukses=Area berhasil ditambahkan');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['aksi'] ?? '') === 'edit') {
    $stmt = $koneksi->prepare("UPDATE tb_area_parkir SET nama_area=:n, kapasitas=:k WHERE id_area=:id");
    $stmt->execute([':n' => $_POST['nama_area'], ':k' => $_POST['kapasitas'], ':id' => $_POST['id_area']]);
    catatAktivitas("Mengubah area parkir ID " . $_POST['id_area']);
    header('Location: kelola_area.php?sukses=Area berhasil diperbarui');
    exit;
}

if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    try {
        $koneksi->prepare("DELETE FROM tb_area_parkir WHERE id_area=:id")->execute([':id'=>$id]);
        catatAktivitas("Menghapus area parkir ID $id");
        header('Location: kelola_area.php?sukses=Area berhasil dihapus');
    } catch (PDOException $e) {
        header('Location: kelola_area.php?gagal=Area tidak dapat dihapus, masih dipakai transaksi');
    }
    exit;
}

$areas = $koneksi->query("SELECT * FROM tb_area_parkir ORDER BY id_area DESC")->fetchAll();

include __DIR__ . '/template/header.php';
?>

<div class="card card-gacoan">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Daftar Area Parkir</span>
        <button class="btn btn-gacoan btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambah">
            <i class="bi bi-plus-lg"></i> Tambah Area
        </button>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead><tr><th>#</th><th>Nama Area</th><th>Kapasitas</th><th>Terisi</th><th class="text-end">Aksi</th></tr></thead>
                <tbody>
                <?php foreach ($areas as $i => $a): ?>
                    <tr>
                        <td><?= $i+1 ?></td>
                        <td class="fw-semibold"><?= htmlspecialchars($a['nama_area']) ?></td>
                        <td><?= $a['kapasitas'] ?></td>
                        <td><?= $a['terisi'] ?> / <?= $a['kapasitas'] ?></td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-outline-gacoan" data-bs-toggle="modal" data-bs-target="#modalEdit<?= $a['id_area'] ?>">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <a href="?hapus=<?= $a['id_area'] ?>" class="btn btn-sm btn-outline-gacoan" onclick="return confirm('Hapus area ini?')">
                                <i class="bi bi-trash"></i>
                            </a>
                        </td>
                    </tr>

                    <div class="modal fade" id="modalEdit<?= $a['id_area'] ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form method="POST">
                                    <input type="hidden" name="aksi" value="edit">
                                    <input type="hidden" name="id_area" value="<?= $a['id_area'] ?>">
                                    <div class="modal-header">
                                        <h6 class="modal-title">Edit Area Parkir</h6>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-2">
                                            <label class="form-label">Nama Area</label>
                                            <input type="text" name="nama_area" class="form-control" value="<?= htmlspecialchars($a['nama_area']) ?>" required>
                                        </div>
                                        <div class="mb-2">
                                            <label class="form-label">Kapasitas</label>
                                            <input type="number" name="kapasitas" class="form-control" value="<?= $a['kapasitas'] ?>" required>
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
                    <h6 class="modal-title">Tambah Area Parkir</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label">Nama Area</label>
                        <input type="text" name="nama_area" class="form-control" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Kapasitas</label>
                        <input type="number" name="kapasitas" class="form-control" required>
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
