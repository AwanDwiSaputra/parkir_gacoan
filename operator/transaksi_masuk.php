<?php
require_once __DIR__ . '/../config/koneksi.php';
cekLogin(['petugas']);
$page_title = 'Kendaraan Masuk';

$error = '';

// ================== CHECK-IN OTOMATIS DARI BOOKING ==================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['aksi'] ?? '') === 'checkin_booking') {
    $id_booking = (int)$_POST['id_booking'];

    try {
        $koneksi->beginTransaction();

        $stmt = $koneksi->prepare(
            "SELECT b.*, k.id_kendaraan, k.plat_nomor, k.jenis_kendaraan
             FROM tb_booking b
             JOIN tb_kendaraan k ON k.id_kendaraan = b.id_kendaraan
             WHERE b.id_booking = :id AND b.status IN ('menunggu','dikonfirmasi') FOR UPDATE"
        );
        $stmt->execute([':id' => $id_booking]);
        $booking = $stmt->fetch();

        if (!$booking) {
            throw new Exception('Booking tidak ditemukan atau sudah diproses.');
        }

        $cek = $koneksi->prepare("SELECT id_parkir FROM tb_transaksi WHERE id_kendaraan=:k AND status='masuk'");
        $cek->execute([':k' => $booking['id_kendaraan']]);
        if ($cek->fetch()) {
            throw new Exception('Kendaraan ini masih tercatat sedang parkir.');
        }

        // Pakai area pilihan booking kalau masih ada slot, kalau tidak cari area lain yang tersedia
        $id_area = null;
        if (!empty($booking['id_area'])) {
            $stmt = $koneksi->prepare("SELECT * FROM tb_area_parkir WHERE id_area=:id FOR UPDATE");
            $stmt->execute([':id' => $booking['id_area']]);
            $areaPilihan = $stmt->fetch();
            if ($areaPilihan && $areaPilihan['terisi'] < $areaPilihan['kapasitas']) {
                $id_area = $areaPilihan['id_area'];
            }
        }
        if (!$id_area) {
            $stmt = $koneksi->query(
                "SELECT id_area FROM tb_area_parkir WHERE terisi < kapasitas ORDER BY (kapasitas - terisi) DESC LIMIT 1"
            );
            $areaTersedia = $stmt->fetch();
            if (!$areaTersedia) {
                throw new Exception('Semua slot area parkir penuh.');
            }
            $id_area = $areaTersedia['id_area'];
        }

        $stmt = $koneksi->prepare("SELECT * FROM tb_tarif WHERE jenis_kendaraan=:j LIMIT 1");
        $stmt->execute([':j' => $booking['jenis_kendaraan']]);
        $tarif = $stmt->fetch();
        if (!$tarif) {
            throw new Exception('Tarif untuk jenis kendaraan ini belum diatur. Hubungi admin.');
        }

        $stmt = $koneksi->prepare(
            "INSERT INTO tb_transaksi (id_kendaraan, waktu_masuk, id_tarif, status, id_user, id_area)
             VALUES (:k, NOW(), :tf, 'masuk', :u, :a)"
        );
        $stmt->execute([
            ':k'  => $booking['id_kendaraan'],
            ':tf' => $tarif['id_tarif'],
            ':u'  => $_SESSION['id_user'],
            ':a'  => $id_area,
        ]);
        $id_transaksi = $koneksi->lastInsertId();

        $koneksi->prepare("UPDATE tb_area_parkir SET terisi = terisi + 1 WHERE id_area=:id")->execute([':id' => $id_area]);
        $koneksi->prepare("UPDATE tb_booking SET status='selesai' WHERE id_booking=:id")->execute([':id' => $id_booking]);

        $koneksi->commit();
        catatAktivitas("Mencatat kendaraan masuk: {$booking['plat_nomor']} (dari booking #{$id_booking})");

        header('Location: cetak_struk.php?id=' . $id_transaksi . '&tipe=masuk');
        exit;

    } catch (Exception $e) {
        $koneksi->rollBack();
        $error = $e->getMessage();
    }
}

// ================== INPUT MANUAL ==================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['aksi'] ?? '') === 'manual') {
    $plat_nomor = strtoupper(trim($_POST['plat_nomor']));
    $jenis      = $_POST['jenis_kendaraan'];
    $warna      = trim($_POST['warna']);
    $pemilik    = trim($_POST['pemilik']);
    $id_area    = (int)$_POST['id_area'];

    try {
        $koneksi->beginTransaction();

        $stmt = $koneksi->prepare("SELECT * FROM tb_kendaraan WHERE plat_nomor=:p");
        $stmt->execute([':p' => $plat_nomor]);
        $kendaraan = $stmt->fetch();

        if ($kendaraan) {
            $id_kendaraan = $kendaraan['id_kendaraan'];
        } else {
            $stmt = $koneksi->prepare(
                "INSERT INTO tb_kendaraan (plat_nomor, jenis_kendaraan, warna, pemilik) VALUES (:p,:j,:w,:pm)"
            );
            $stmt->execute([':p'=>$plat_nomor, ':j'=>$jenis, ':w'=>$warna, ':pm'=>$pemilik]);
            $id_kendaraan = $koneksi->lastInsertId();
        }

        $cek = $koneksi->prepare("SELECT id_parkir FROM tb_transaksi WHERE id_kendaraan=:k AND status='masuk'");
        $cek->execute([':k' => $id_kendaraan]);
        if ($cek->fetch()) {
            throw new Exception('Kendaraan ini masih tercatat sedang parkir.');
        }

        $stmt = $koneksi->prepare("SELECT * FROM tb_area_parkir WHERE id_area=:id FOR UPDATE");
        $stmt->execute([':id' => $id_area]);
        $areaData = $stmt->fetch();
        if (!$areaData || $areaData['terisi'] >= $areaData['kapasitas']) {
            throw new Exception('Slot area parkir penuh.');
        }

        $stmt = $koneksi->prepare("SELECT * FROM tb_tarif WHERE jenis_kendaraan=:j LIMIT 1");
        $stmt->execute([':j' => $jenis]);
        $tarif = $stmt->fetch();
        if (!$tarif) {
            throw new Exception('Tarif untuk jenis kendaraan ini belum diatur. Hubungi admin.');
        }

        $stmt = $koneksi->prepare(
            "INSERT INTO tb_transaksi (id_kendaraan, waktu_masuk, id_tarif, status, id_user, id_area)
             VALUES (:k, NOW(), :tf, 'masuk', :u, :a)"
        );
        $stmt->execute([':k'=>$id_kendaraan, ':tf'=>$tarif['id_tarif'], ':u'=>$_SESSION['id_user'], ':a'=>$id_area]);
        $id_transaksi = $koneksi->lastInsertId();

        $koneksi->prepare("UPDATE tb_area_parkir SET terisi = terisi + 1 WHERE id_area=:id")->execute([':id'=>$id_area]);

        $koneksi->commit();
        catatAktivitas("Mencatat kendaraan masuk: $plat_nomor");

        header('Location: cetak_struk.php?id=' . $id_transaksi . '&tipe=masuk');
        exit;

    } catch (Exception $e) {
        $koneksi->rollBack();
        $error = $e->getMessage();
    }
}

$areas  = $koneksi->query("SELECT * FROM tb_area_parkir ORDER BY nama_area")->fetchAll();
$tarifs = $koneksi->query("SELECT DISTINCT jenis_kendaraan FROM tb_tarif ORDER BY jenis_kendaraan")->fetchAll();

// Booking member yang siap di-check-in: hari ini DAN semua tanggal mendatang
// (menunggu / dikonfirmasi), diurutkan dari yang paling dekat tanggalnya.
$bookingHariIni = $koneksi->query(
    "SELECT b.*, k.plat_nomor, k.jenis_kendaraan, k.pemilik, u.nama_lengkap AS nama_member, a.nama_area
     FROM tb_booking b
     JOIN tb_kendaraan k ON k.id_kendaraan = b.id_kendaraan
     JOIN tb_user u ON u.id_user = b.id_user
     LEFT JOIN tb_area_parkir a ON a.id_area = b.id_area
     WHERE b.status IN ('menunggu','dikonfirmasi') AND b.tanggal_booking >= CURDATE()
     ORDER BY b.tanggal_booking ASC, b.jam_booking ASC"
)->fetchAll();

include __DIR__ . '/components/header.php';
?>

<?php if ($error): ?>
    <div class="alert alert-danger alert-auto-dismiss"><i class="bi bi-exclamation-triangle-fill me-1"></i> <?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<?php if (!empty($bookingHariIni)): ?>
<div class="card card-gacoan mb-3">
    <div class="card-header d-flex align-items-center gap-2">
        <i class="bi bi-calendar-check"></i> Booking Member (Hari Ini &amp; Mendatang)
        <span class="badge bg-warning text-dark ms-1"><?= count($bookingHariIni) ?> menunggu check-in</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead><tr><th>Tanggal</th><th>Jam</th><th>Plat</th><th>Jenis</th><th>Member</th><th>Area Diminta</th><th class="text-end">Aksi</th></tr></thead>
                <tbody>
                <?php foreach ($bookingHariIni as $b): ?>
                    <?php $isHariIni = $b['tanggal_booking'] === date('Y-m-d'); ?>
                    <tr>
                        <td class="small fw-semibold">
                            <?= date('d M Y', strtotime($b['tanggal_booking'])) ?>
                            <?php if ($isHariIni): ?>
                                <span class="badge bg-success ms-1">Hari Ini</span>
                            <?php endif; ?>
                        </td>
                        <td class="small fw-semibold"><?= substr($b['jam_booking'], 0, 5) ?></td>
                        <td class="fw-bold"><?= htmlspecialchars($b['plat_nomor']) ?></td>
                        <td class="text-capitalize"><?= htmlspecialchars($b['jenis_kendaraan']) ?></td>
                        <td><?= htmlspecialchars($b['nama_member']) ?></td>
                        <td><?= htmlspecialchars($b['nama_area'] ?? 'Bebas') ?></td>
                        <td class="text-end">
                            <form method="POST" onsubmit="return confirm('Check-in otomatis kendaraan <?= htmlspecialchars($b['plat_nomor']) ?> dari booking ini?')">
                                <input type="hidden" name="aksi" value="checkin_booking">
                                <input type="hidden" name="id_booking" value="<?= $b['id_booking'] ?>">
                                <button type="submit" class="btn btn-sm btn-gacoan" <?= $isHariIni ? '' : 'title="Booking untuk tanggal mendatang"' ?>>
                                    <i class="bi bi-lightning-charge-fill"></i> Check-in Otomatis
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card card-gacoan">
            <div class="card-header"><i class="bi bi-box-arrow-in-right"></i> Input Kendaraan Masuk Manual</div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="aksi" value="manual">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Plat Nomor</label>
                            <input type="text" name="plat_nomor" class="form-control text-uppercase" required placeholder="B 1234 CD">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Jenis Kendaraan</label>
                            <select name="jenis_kendaraan" class="form-select" required>
                                <?php foreach ($tarifs as $t): ?>
                                    <option value="<?= htmlspecialchars($t['jenis_kendaraan']) ?>" class="text-capitalize"><?= ucfirst($t['jenis_kendaraan']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Warna</label>
                            <input type="text" name="warna" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Pemilik</label>
                            <input type="text" name="pemilik" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Area Parkir</label>
                            <select name="id_area" class="form-select" required>
                                <?php foreach ($areas as $a): $sisa = $a['kapasitas']-$a['terisi']; ?>
                                    <option value="<?= $a['id_area'] ?>" <?= $sisa<=0?'disabled':'' ?>>
                                        <?= htmlspecialchars($a['nama_area']) ?> — sisa <?= $sisa ?> slot
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-gacoan w-100 mt-4">
                        <i class="bi bi-check2-circle"></i> Catat Kendaraan Masuk & Cetak Tiket
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/components/footer.php'; ?>