<?php
require_once __DIR__ . '/../config/koneksi.php';
cekLogin(['member']);
$page_title = 'Booking Parkir';
$id_user = $_SESSION['id_user'];

// ===== Batas waktu menunggu konfirmasi petugas (dalam menit) =====
// Booking berstatus 'menunggu' yang melewati batas ini otomatis
// diubah menjadi 'kedaluwarsa' dan tidak bisa lagi diproses/dibatalkan manual.
const BATAS_WAKTU_MENIT = 15;

// Auto-expire booking yang sudah lewat batas waktu (dijalankan tiap halaman dibuka).
// Sesuaikan nama kolom created_at jika berbeda di tabel tb_booking Anda.
$koneksi->prepare(
    "UPDATE tb_booking
     SET status = 'kedaluwarsa'
     WHERE status = 'menunggu'
       AND created_at < (NOW() - INTERVAL :batas MINUTE)"
)->execute(['batas' => BATAS_WAKTU_MENIT]);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['aksi'] ?? '') === 'tambah') {
    $id_kendaraan    = (int)$_POST['id_kendaraan'];
    $id_area         = (int)$_POST['id_area'];
    $tanggal_booking = $_POST['tanggal_booking'];
    $jam_booking     = $_POST['jam_booking'];
    $catatan         = trim($_POST['catatan']);

    // pastikan kendaraan ini benar milik member yang login
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
        header('Location: booking.php?gagal=Booking tidak dapat dibatalkan (mungkin sudah kedaluwarsa)');
    }
    exit;
}

if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    // Hanya boleh menghapus booking yang statusnya sudah tidak aktif lagi
    // (menunggu/dikonfirmasi harus dibatalkan dulu, bukan langsung dihapus)
    $cek = $koneksi->prepare(
        "SELECT id_booking FROM tb_booking
         WHERE id_booking=? AND id_user=? AND status IN ('selesai','dibatalkan','kedaluwarsa')"
    );
    $cek->execute([$id, $id_user]);
    if ($cek->fetch()) {
        $koneksi->prepare("DELETE FROM tb_booking WHERE id_booking=?")->execute([$id]);
        header('Location: booking.php?sukses=Riwayat booking berhasil dihapus');
    } else {
        header('Location: booking.php?gagal=Booking tidak dapat dihapus (batalkan dulu jika masih aktif)');
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
        <div class="alert alert-info small mb-0 rounded-0 border-0 border-bottom">
            <i class="bi bi-info-circle"></i>
            Booking yang berstatus <strong>Menunggu</strong> akan otomatis kedaluwarsa jika tidak dikonfirmasi petugas dalam <?= BATAS_WAKTU_MENIT ?> menit.
        </div>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead><tr><th>#</th><th>Plat</th><th>Area</th><th>Tanggal</th><th>Jam</th><th>Catatan</th><th>Status</th><th class="text-end">Aksi</th></tr></thead>
                <tbody>
                <?php foreach ($bookings as $i => $b): ?>
                    <?php
                        $badge = [
                            'menunggu'     => 'bg-warning text-dark',
                            'dikonfirmasi' => 'bg-primary',
                            'selesai'      => 'bg-success',
                            'dibatalkan'   => 'bg-secondary',
                            'kedaluwarsa'  => 'bg-danger',
                        ][$b['status']] ?? 'bg-secondary';

                        // Hitung kapan booking ini akan kedaluwarsa (untuk countdown live di JS)
                        $expireAt = null;
                        if ($b['status'] === 'menunggu' && !empty($b['created_at'])) {
                            $expireAt = strtotime($b['created_at']) + (BATAS_WAKTU_MENIT * 60);
                        }

                        $bisaDihapus = in_array($b['status'], ['selesai', 'dibatalkan', 'kedaluwarsa'], true);
                    ?>
                    <tr id="row-<?= $b['id_booking'] ?>">
                        <td><?= $i+1 ?></td>
                        <td class="fw-semibold"><?= htmlspecialchars($b['plat_nomor']) ?></td>
                        <td><?= htmlspecialchars($b['nama_area'] ?? '-') ?></td>
                        <td class="small"><?= date('d M Y', strtotime($b['tanggal_booking'])) ?></td>
                        <td class="small"><?= substr($b['jam_booking'], 0, 5) ?></td>
                        <td class="small"><?= htmlspecialchars($b['catatan'] ?? '-') ?></td>
                        <td>
                            <span class="badge <?= $badge ?>"><?= ucfirst($b['status']) ?></span>
                            <?php if ($expireAt): ?>
                                <div class="small text-muted mt-1 countdown-batas" data-expire="<?= $expireAt ?>">
                                    <i class="bi bi-hourglass-split"></i> menghitung...
                                </div>
                            <?php endif; ?>
                        </td>
                        <td class="text-end text-nowrap">
                            <?php if ($b['status'] === 'menunggu'): ?>
                                <a href="?batal=<?= $b['id_booking'] ?>" class="btn btn-sm btn-outline-gacoan" onclick="return confirm('Batalkan booking ini?')">
                                    <i class="bi bi-x-lg"></i> Batalkan
                                </a>
                            <?php endif; ?>
                            <?php if ($bisaDihapus): ?>
                                <a href="?hapus=<?= $b['id_booking'] ?>"
                                   class="btn btn-sm btn-outline-danger btn-hapus-booking"
                                   data-plat="<?= htmlspecialchars($b['plat_nomor']) ?>"
                                   title="Hapus dari riwayat">
                                    <i class="bi bi-trash"></i>
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
                    <div class="alert alert-warning small mb-0">
                        <i class="bi bi-exclamation-triangle"></i>
                        Booking akan otomatis kedaluwarsa jika tidak dikonfirmasi petugas dalam <?= BATAS_WAKTU_MENIT ?> menit.
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

<script>
// ===== Countdown live untuk booking berstatus "menunggu" =====
// Setiap baris dengan class .countdown-batas dihitung mundur dari atribut data-expire (unix timestamp).
// Saat mencapai 0, halaman dimuat ulang otomatis agar status "kedaluwarsa" tersinkron dari server.
(function () {
    const rows = document.querySelectorAll('.countdown-batas');
    if (!rows.length) return;

    let sudahExpired = false;

    function format(detik) {
        const m = Math.floor(detik / 60);
        const s = detik % 60;
        return m + ':' + String(s).padStart(2, '0');
    }

    function tick() {
        const now = Math.floor(Date.now() / 1000);
        rows.forEach(function (el) {
            const expireAt = parseInt(el.dataset.expire, 10);
            const sisa = expireAt - now;
            if (sisa <= 0) {
                el.innerHTML = '<i class="bi bi-exclamation-circle text-danger"></i> Kedaluwarsa, memuat ulang...';
                if (!sudahExpired) {
                    sudahExpired = true;
                    setTimeout(() => window.location.reload(), 1500);
                }
            } else {
                el.innerHTML = '<i class="bi bi-hourglass-split"></i> Sisa ' + format(sisa);
            }
        });
    }

    tick();
    setInterval(tick, 1000);
})();

// ===== Konfirmasi hapus riwayat booking =====
document.querySelectorAll('.btn-hapus-booking').forEach(function (btn) {
    btn.addEventListener('click', function (e) {
        var plat = btn.getAttribute('data-plat');
        if (!confirm('Hapus riwayat booking untuk plat "' + plat + '"?\nTindakan ini tidak bisa dibatalkan.')) {
            e.preventDefault();
        }
    });
});
</script>

<?php include __DIR__ . '/components/footer.php'; ?>