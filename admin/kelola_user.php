<?php
require_once __DIR__ . '/../config/koneksi.php';
cekLogin(['admin']);
$page_title = 'Kelola User';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['aksi'] ?? '') === 'tambah') {
    $username     = trim($_POST['username']);
    $nama_lengkap = trim($_POST['nama_lengkap']);
    $password     = $_POST['password'];
    $role         = $_POST['role'];
    $status       = (int)$_POST['status_aktif'];

    $cek = $koneksi->prepare("SELECT id_user FROM tb_user WHERE username=:u");
    $cek->execute([':u' => $username]);

    if ($cek->fetch()) {
        header('Location: kelola_user.php?gagal=Username sudah digunakan');
        exit;
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $koneksi->prepare(
        "INSERT INTO tb_user (nama_lengkap, username, password, role, status_aktif) VALUES (:n,:u,:p,:r,:s)"
    );
    $stmt->execute([':n'=>$nama_lengkap, ':u'=>$username, ':p'=>$hash, ':r'=>$role, ':s'=>$status]);
    catatAktivitas("Menambahkan user baru: $username");
    header('Location: kelola_user.php?sukses=User berhasil ditambahkan');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['aksi'] ?? '') === 'edit') {
    $id = $_POST['id_user'];
    if (!empty($_POST['password'])) {
        $hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $stmt = $koneksi->prepare("UPDATE tb_user SET nama_lengkap=:n, role=:r, status_aktif=:s, password=:p WHERE id_user=:id");
        $stmt->execute([':n'=>$_POST['nama_lengkap'], ':r'=>$_POST['role'], ':s'=>(int)$_POST['status_aktif'], ':p'=>$hash, ':id'=>$id]);
    } else {
        $stmt = $koneksi->prepare("UPDATE tb_user SET nama_lengkap=:n, role=:r, status_aktif=:s WHERE id_user=:id");
        $stmt->execute([':n'=>$_POST['nama_lengkap'], ':r'=>$_POST['role'], ':s'=>(int)$_POST['status_aktif'], ':id'=>$id]);
    }
    catatAktivitas("Mengubah data user ID $id");
    header('Location: kelola_user.php?sukses=User berhasil diperbarui');
    exit;
}

if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    if ($id === (int)$_SESSION['id_user']) {
        header('Location: kelola_user.php?gagal=Anda tidak bisa menghapus akun sendiri');
        exit;
    }

    // Cek apakah user masih memiliki data booking terkait
    $cekBooking = $koneksi->prepare("SELECT COUNT(*) FROM tb_booking WHERE id_user=:id");
    $cekBooking->execute([':id'=>$id]);
    $jumlahBooking = (int)$cekBooking->fetchColumn();

    if ($jumlahBooking > 0) {
        header('Location: kelola_user.php?gagal=User tidak bisa dihapus karena masih memiliki ' . $jumlahBooking . ' data booking terkait');
        exit;
    }

    $koneksi->prepare("DELETE FROM tb_user WHERE id_user=:id")->execute([':id'=>$id]);
    catatAktivitas("Menghapus user ID $id");
    header('Location: kelola_user.php?sukses=User berhasil dihapus');
    exit;
}

$users = $koneksi->query("SELECT * FROM tb_user ORDER BY id_user DESC")->fetchAll();

include __DIR__ . '/template/header.php';
?>

<div class="card card-gacoan">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Daftar User</span>
        <button class="btn btn-gacoan btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambah">
            <i class="bi bi-plus-lg"></i> Tambah User
        </button>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr><th>#</th><th>Username</th><th>Nama Lengkap</th><th>Role</th><th>Status</th><th>Dibuat</th><th class="text-end">Aksi</th></tr>
                </thead>
                <tbody>
                <?php foreach ($users as $i => $u): ?>
                    <tr>
                        <td><?= $i+1 ?></td>
                        <td class="fw-semibold"><?= htmlspecialchars($u['username']) ?></td>
                        <td><?= htmlspecialchars($u['nama_lengkap']) ?></td>
                        <td><span class="badge" style="background:var(--gc-black);"><?= strtoupper($u['role']) ?></span></td>
                        <td>
                            <?php if ($u['status_aktif']): ?>
                                <span class="badge bg-success">Aktif</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Nonaktif</span>
                            <?php endif; ?>
                        </td>
                        <td class="small text-muted"><?= formatTanggal($u['created_at']) ?></td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-outline-gacoan" data-bs-toggle="modal" data-bs-target="#modalEdit<?= $u['id_user'] ?>">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <a href="?hapus=<?= $u['id_user'] ?>" class="btn btn-sm btn-outline-gacoan" onclick="return confirm('Hapus user <?= htmlspecialchars($u['username']) ?>?')">
                                <i class="bi bi-trash"></i>
                            </a>
                        </td>
                    </tr>

                    <div class="modal fade" id="modalEdit<?= $u['id_user'] ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form method="POST">
                                    <input type="hidden" name="aksi" value="edit">
                                    <input type="hidden" name="id_user" value="<?= $u['id_user'] ?>">
                                    <div class="modal-header">
                                        <h6 class="modal-title">Edit User: <?= htmlspecialchars($u['username']) ?></h6>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-2">
                                            <label class="form-label">Nama Lengkap</label>
                                            <input type="text" name="nama_lengkap" class="form-control" value="<?= htmlspecialchars($u['nama_lengkap']) ?>" required>
                                        </div>
                                        <div class="mb-2">
                                            <label class="form-label">Role</label>
                                            <select name="role" class="form-select">
                                                <?php foreach (['admin','petugas','owner','member'] as $r): ?>
                                                    <option value="<?= $r ?>" <?= $u['role']===$r?'selected':'' ?>><?= ucfirst($r) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="mb-2">
                                            <label class="form-label">Status</label>
                                            <select name="status_aktif" class="form-select">
                                                <option value="1" <?= $u['status_aktif']?'selected':'' ?>>Aktif</option>
                                                <option value="0" <?= !$u['status_aktif']?'selected':'' ?>>Nonaktif</option>
                                            </select>
                                        </div>
                                        <div class="mb-2">
                                            <label class="form-label">Password Baru (kosongkan jika tidak diubah)</label>
                                            <input type="password" name="password" class="form-control">
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
                    <h6 class="modal-title">Tambah User Baru</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" name="nama_lengkap" class="form-control" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Role</label>
                        <select name="role" class="form-select">
                            <option value="admin">Admin</option>
                            <option value="petugas">Petugas</option>
                            <option value="owner">Owner</option>
                            <option value="member">Member</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Status</label>
                        <select name="status_aktif" class="form-select">
                            <option value="1">Aktif</option>
                            <option value="0">Nonaktif</option>
                        </select>
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