<?php
require_once __DIR__ . '/../config/koneksi.php';
cekLogin(['user']);
$page_title = 'Kendaraan Saya';
$id_user = $_SESSION['id_user'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['aksi'] ?? '') === 'tambah') {
    $plat_nomor = strtoupper(trim($_POST['plat_nomor']));
    $jenis      = $_POST['jenis_kendaraan'];
    $warna      = trim($_POST['warna']);
    $pemilik    = trim($_POST['pemilik']) ?: $_SESSION['nama_lengkap'];

    $cek = $koneksi->prepare("SELECT id_kendaraan FROM tb_kendaraan WHERE plat_nomor = ?");
    $cek->execute([$plat_nomor]);

    if ($cek->fetch()) {
        header('Location: kendaraan.php?gagal=Plat nomor sudah terdaftar');
        exit;
    }

    $stmt = $koneksi->prepare(
        "INSERT INTO tb_kendaraan (plat_nomor, jenis_kendaraan, warna, pemilik, id_user) VALUES (?,?,?,?,?)"
    );
    $stmt->execute([$plat_nomor, $jenis, $warna, $pemilik, $id_user]);
    header('Location: kendaraan.php?sukses=Kendaraan berhasil ditambahkan');
    exit;
}

if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    $cek = $koneksi->prepare("SELECT id_kendaraan FROM tb_kendaraan WHERE id_kendaraan=? AND id_user=?");
    $cek->execute([$id, $id_user]);
    if ($cek->fetch()) {
        try {
            $koneksi->prepare("DELETE FROM tb_kendaraan WHERE id_kendaraan=?")->execute([$id]);
            header('Location: kendaraan.php?sukses=Kendaraan berhasil dihapus');
        } catch (PDOException $e) {
            header('Location: kendaraan.php?gagal=Kendaraan tidak dapat dihapus, masih ada riwayat transaksi/booking');
        }
    } else {
        header('Location: kendaraan.php?gagal=Data tidak ditemukan');
    }
    exit;
}

$stmt = $koneksi->prepare("SELECT * FROM tb_kendaraan WHERE id_user = ? ORDER BY id_kendaraan DESC");
$stmt->execute([$id_user]);
$kendaraans = $stmt->fetchAll();

include __DIR__ . '/components/header.php';
?>

<div class="card card-gacoan">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Kendaraan Terdaftar Atas Nama Saya</span>
        <button class="btn btn-gacoan btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambah">
            <i class="bi bi-plus-lg"></i> Tambah Kendaraan
        </button>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead><tr><th>#</th><th>Plat Nomor</th><th>Jenis</th><th>Warna</th><th>Pemilik</th><th class="text-end">Aksi</th></tr></thead>
                <tbody>
                <?php foreach ($kendaraans as $i => $k): ?>
                    <tr>
                        <td><?= $i+1 ?></td>
                        <td class="fw-bold"><?= htmlspecialchars($k['plat_nomor']) ?></td>
                        <td class="text-capitalize"><?= htmlspecialchars($k['jenis_kendaraan']) ?></td>
                        <td><?= htmlspecialchars($k['warna'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($k['pemilik']) ?></td>
                        <td class="text-end">
                            <a href="?hapus=<?= $k['id_kendaraan'] ?>" class="btn btn-sm btn-outline-gacoan" onclick="return confirm('Hapus kendaraan <?= htmlspecialchars($k['plat_nomor']) ?>?')">
                                <i class="bi bi-trash"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($kendaraans)): ?>
                    <tr><td colspan="6" class="text-center text-muted py-4">Belum ada kendaraan terdaftar. Tambahkan dulu sebelum booking.</td></tr>
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
                    <h6 class="modal-title">Tambah Kendaraan</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label">Plat Nomor</label>
                        <input type="text" name="plat_nomor" class="form-control text-uppercase" required placeholder="B 1234 CD">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Jenis Kendaraan</label>
                        <select name="jenis_kendaraan" class="form-select">
                            <option value="motor">Motor</option>
                            <option value="mobil">Mobil</option>
                            <option value="truk/bus">Truk/Bus</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Warna</label>
                        <input type="text" name="warna" class="form-control">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Nama Pemilik</label>
                        <input type="text" name="pemilik" class="form-control" value="<?= htmlspecialchars($_SESSION['nama_lengkap']) ?>">
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

<?php include __DIR__ . '/components/footer.php'; ?>
