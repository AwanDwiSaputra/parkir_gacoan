<?php
require_once __DIR__ . '/../config/koneksi.php';
cekLogin(['admin']);
$page_title = 'Moderasi Testimoni';

if (isset($_GET['aksi']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $aksi = $_GET['aksi'];
    if (in_array($aksi, ['approve', 'reject'])) {
        $status = $aksi === 'approve' ? 'approved' : 'rejected';
        $koneksi->prepare("UPDATE testimoni SET status=:s WHERE id=:id")->execute([':s'=>$status, ':id'=>$id]);
        catatAktivitas("Moderasi testimoni #$id: $aksi");
        header('Location: moderasi_testimoni.php?sukses=Testimoni berhasil ' . ($aksi === 'approve' ? 'disetujui' : 'ditolak'));
        exit;
    }
}

$testimonis = $koneksi->query("SELECT * FROM testimoni ORDER BY FIELD(status,'pending','approved','rejected'), created_at DESC")->fetchAll();

include __DIR__ . '/template/header.php';
?>

<div class="card card-gacoan">
    <div class="card-header">Semua Testimoni</div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead><tr><th>#</th><th>Nama</th><th>Role</th><th>Rating</th><th>Komentar</th><th>Status</th><th class="text-end">Aksi</th></tr></thead>
                <tbody>
                <?php foreach ($testimonis as $i => $t): ?>
                    <tr>
                        <td><?= $i+1 ?></td>
                        <td class="fw-semibold"><?= htmlspecialchars($t['nama']) ?></td>
                        <td><?= htmlspecialchars($t['role']) ?></td>
                        <td><?= str_repeat('⭐', (int)$t['rating']) ?></td>
                        <td style="max-width:280px;"><?= htmlspecialchars($t['komentar']) ?></td>
                        <td>
                            <?php if ($t['status']==='pending'): ?>
                                <span class="badge bg-warning text-dark">Pending</span>
                            <?php elseif ($t['status']==='approved'): ?>
                                <span class="badge bg-success">Approved</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Rejected</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <a href="?aksi=approve&id=<?= $t['id'] ?>" class="btn btn-sm btn-outline-gacoan" onclick="return confirm('Setujui testimoni ini?')">
                                <i class="bi bi-check-lg"></i>
                            </a>
                            <a href="?aksi=reject&id=<?= $t['id'] ?>" class="btn btn-sm btn-outline-gacoan" onclick="return confirm('Tolak testimoni ini?')">
                                <i class="bi bi-x-lg"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($testimonis)): ?>
                    <tr><td colspan="7" class="text-center text-muted py-4">Belum ada testimoni</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/template/footer.php'; ?>
