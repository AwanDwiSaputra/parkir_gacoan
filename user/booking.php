<?php
require_once __DIR__ . '/../config/koneksi.php';
cekLogin(['user']);
$page_title = 'Booking Parkir';
$id_user = $_SESSION['id_user'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['aksi'] ?? '') === 'tambah') {
    $id_kendaraan    = (int)$_POST['id_kendaraan'];
    $id_area         = (int)$_POST['id_area'];
    $tanggal_booking = $_POST['tanggal_booking'];
    $jam_booking     = $_POST['jam_booking'];
    $catatan         = trim($_POST['catatan']);

    // pastikan kendaraan ini benar milik user yang login
    $cek = $koneksi->prepare("SELECT id_kendaraan FROM tb_kendaraan WHERE id_kendaraan=? AND id_user=?");
    $cek->execute([$id_kendaraan, $id_user]);

    if (!$cek->fetch()) {
        header('Location: booking.php?gagal=Kendaraan tidak valid');
        exit;
    }

    $stmt = $koneksi->prepare(
        "INSERT INTO tb_booking (id_user, id_kendaraan, id_area, tanggal_booking, jam_booking, catatan, status)
         VALUES (?,?,?,?,?,?,'menunggu')"
    );
    $stmt->execute([$id_user, $id_kendaraan, $id_area ?: null, $tanggal_booking, $jam_booking, $catatan ?: null]);
    header('Location: booking.php?sukses=Booking berhasil dibuat, menunggu konfirmasi petugas');
    exit;
}

if (isset($_GET['batal'])) {
    $id = (int)$_GET['batal'];
    $cek = $koneksi->prepare("SELECT id_booking FROM tb_booking WHERE id_booking=? AND id_user=? AND status='menunggu'");
    $cek->execute([$id, $id_user]);
    if ($cek->fetch()) {
        $koneksi->prepare("UPDATE tb_booking SET status='dibatalkan' WHERE id_booking=?")->execute([$id]);
        header('Location: booking.php?sukses=Booking berhasil dibatalkan');
    } else {
        header('Location: booking.php?gagal=Booking tidak dapat dibatalkan');
    }
    exit;
}

$kendaraans = $koneksi->prepare("SELECT * FROM tb_kendaraan WHERE id_user = ? ORDER BY plat_nomor");
$kendaraans->execute([$id_user]);
$kendaraans = $kendaraans->fetchAll();

$areas = $koneksi->query("SELECT * FROM tb_area_parkir ORDER BY nama_area")->fetchAll();

$bookings = $koneksi->prepare(
    "SELECT b.*, k.plat_nomor, a.nama_area
     FROM tb_booking b
     JOIN tb_kendaraan k ON k.id_kendaraan = b.id_kendaraan
     LEFT JOIN tb_area_parkir a ON a.id_area = b.id_area
     WHERE b.id_user = ?
     ORDER BY b.tanggal_booking DESC, b.jam_booking DESC"
);
$bookings->execute([$id_user]);
$bookings = $bookings->fetchAll();

include __DIR__ . '/components/header.php';
?>

<div class="card card-gacoan mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Booking Slot Parkir</span>
        <?php if (!empty($kendaraans)): ?>
        <button class="btn btn-gacoan btn-sm" data-bs-toggle="modal" data-bs-target="#modalBooking">
            <i class="bi bi-plus-lg"></i> Booking Baru
        </button>
        <?php else: ?>
        <a href="kendaraan.php" class="btn btn-outline-gacoan btn-sm">Tambah Kendaraan Dulu</a>
        <?php endif; ?>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead><tr><th>#</th><th>Plat</th><th>Area</th><th>Tanggal</th><th>Jam</th><th>Catatan</th><th>Status</th><th class="text-end">Aksi</th></tr></thead>
                <tbody>
                <?php foreach ($bookings as $i => $b): ?>
                    <tr>
                        <td><?= $i+1 ?></td>
                        <td class="fw-semibold"><?= htmlspecialchars($b['plat_nomor']) ?></td>
                        <td><?= htmlspecialchars($b['nama_area'] ?? '-') ?></td>
                        <td class="small"><?= date('d M Y', strtotime($b['tanggal_booking'])) ?></td>
                        <td class="small"><?= substr($b['jam_booking'], 0, 5) ?></td>
                        <td class="small"><?= htmlspecialchars($b['catatan'] ?? '-') ?></td>
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
                        <td class="text-end">
                            <?php if ($b['status'] === 'menunggu'): ?>
                                <a href="?batal=<?= $b['id_booking'] ?>" class="btn btn-sm btn-outline-gacoan" onclick="return confirm('Batalkan booking ini?')">
                                    <i class="bi bi-x-lg"></i> Batalkan
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($bookings)): ?>
                    <tr><td colspan="8" class="text-center text-muted py-4">Belum ada booking</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalBooking" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="aksi" value="tambah">
                <div class="modal-header">
                    <h6 class="modal-title">Booking Slot Parkir</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label">Kendaraan</label>
                        <select name="id_kendaraan" class="form-select" required>
                            <?php foreach ($kendaraans as $k): ?>
                                <option value="<?= $k['id_kendaraan'] ?>"><?= htmlspecialchars($k['plat_nomor']) ?> (<?= htmlspecialchars($k['jenis_kendaraan']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Area Parkir <span class="text-muted fw-normal">(opsional)</span></label>
                        <select name="id_area" class="form-select">
                            <option value="">Tanpa preferensi area</option>
                            <?php foreach ($areas as $a): ?>
                                <option value="<?= $a['id_area'] ?>"><?= htmlspecialchars($a['nama_area']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label">Tanggal</label>
                            <input type="date" name="tanggal_booking" class="form-control" min="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Jam</label>
                            <input type="time" name="jam_booking" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-2 mt-2">
                        <label class="form-label">Catatan <span class="text-muted fw-normal">(opsional)</span></label>
                        <textarea name="catatan" class="form-control" rows="2" placeholder="Contoh: butuh slot dekat pintu masuk"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-gacoan" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-gacoan">Kirim Booking</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/components/footer.php'; ?>
