<?php
require_once __DIR__ . '/../config/koneksi.php';
cekLogin(['admin']);
$page_title = 'Log Aktivitas';

// ================== HAPUS SATU LOG ==================
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    $koneksi->prepare("DELETE FROM tb_log_aktivitas WHERE id_log=:id")->execute([':id' => $id]);
    header('Location: log_aktivitas.php?sukses=Log berhasil dihapus');
    exit;
}

// ================== HAPUS SEMUA LOG ==================
if (isset($_GET['hapus_semua'])) {
    $koneksi->exec("TRUNCATE TABLE tb_log_aktivitas");
    header('Location: log_aktivitas.php?sukses=Semua log berhasil dihapus');
    exit;
}

$search = trim($_GET['q'] ?? '');
$sql = "SELECT l.*, u.username, u.nama_lengkap, u.role
        FROM tb_log_aktivitas l
        LEFT JOIN tb_user u ON u.id_user = l.id_user";
$params = [];
if ($search !== '') {
    $sql .= " WHERE l.aktivitas LIKE :q OR u.nama_lengkap LIKE :q";
    $params[':q'] = "%$search%";
}
$sql .= " ORDER BY l.waktu_aktivitas DESC LIMIT 200";
$stmt = $koneksi->prepare($sql);
$stmt->execute($params);
$logs = $stmt->fetchAll();

include __DIR__ . '/template/header.php';
?>

<div class="card card-gacoan">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span>Log Aktivitas Seluruh User</span>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <form method="GET" class="d-flex">
                <input type="text" name="q" class="form-control form-control-sm" placeholder="Cari aktivitas / user..." value="<?= htmlspecialchars($search) ?>">
                <button class="btn btn-sm btn-outline-gacoan ms-1"><i class="bi bi-search"></i></button>
            </form>
            <?php if (!empty($logs)): ?>
            <a href="?hapus_semua=1" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus SEMUA log aktivitas? Tindakan ini tidak bisa dibatalkan.')">
                <i class="bi bi-trash3"></i> Hapus Semua
            </a>
            <?php endif; ?>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead><tr><th>#</th><th>Waktu</th><th>User</th><th>Role</th><th>Aktivitas</th><th class="text-end">Aksi</th></tr></thead>
                <tbody>
                <?php foreach ($logs as $i => $l): ?>
                    <tr>
                        <td><?= $i+1 ?></td>
                        <td class="small"><?= formatTanggal($l['waktu_aktivitas']) ?></td>
                        <td class="fw-semibold"><?= htmlspecialchars($l['nama_lengkap'] ?? 'Sistem') ?></td>
                        <td><span class="badge" style="background:var(--gc-black);"><?= strtoupper($l['role'] ?? '-') ?></span></td>
                        <td><?= htmlspecialchars($l['aktivitas']) ?></td>
                        <td class="text-end">
                            <a href="?hapus=<?= $l['id_log'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus log ini?')">
                                <i class="bi bi-trash"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($logs)): ?>
                    <tr><td colspan="6" class="text-center text-muted py-4">Belum ada log aktivitas</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        <p class="small text-muted p-3 mb-0">Menampilkan maksimal 200 log terbaru.</p>
    </div>
</div>

<?php include __DIR__ . '/template/footer.php'; ?>