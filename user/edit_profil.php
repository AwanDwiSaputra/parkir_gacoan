<?php
require_once __DIR__ . '/../config/koneksi.php';
cekLogin(['user']);

$page_title = 'Profil Saya';
$id_user = $_SESSION['id_user'];
$error = '';

$stmtUser = $koneksi->prepare("SELECT * FROM tb_user WHERE id_user = ?");
$stmtUser->execute([$id_user]);
$user = $stmtUser->fetch();

// ==== UPLOAD / GANTI FOTO PROFIL ====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi']) && $_POST['aksi'] === 'upload_foto') {
    if (!isset($_FILES['foto']) || $_FILES['foto']['error'] === UPLOAD_ERR_NO_FILE) {
        $error = 'Silakan pilih foto terlebih dahulu.';
    } elseif ($_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
        $error = 'Terjadi kesalahan saat upload foto.';
    } else {
        $file = $_FILES['foto'];
        $ekstensiValid = ['jpg', 'jpeg', 'png', 'webp'];
        $ekstensi = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $ukuranMaks = 2 * 1024 * 1024; // 2 MB

        if (!in_array($ekstensi, $ekstensiValid)) {
            $error = 'Format foto harus JPG, PNG, atau WEBP.';
        } elseif ($file['size'] > $ukuranMaks) {
            $error = 'Ukuran foto maksimal 2 MB.';
        } else {
            $folderUpload = __DIR__ . '/../uploads/profil/';
            if (!is_dir($folderUpload)) {
                mkdir($folderUpload, 0755, true);
            }

            $namaFileBaru = 'user_' . $id_user . '_' . time() . '.' . $ekstensi;
            $tujuan = $folderUpload . $namaFileBaru;

            if (move_uploaded_file($file['tmp_name'], $tujuan)) {
                if (!empty($user['foto'])) {
                    $fotoLama = $folderUpload . $user['foto'];
                    if (file_exists($fotoLama)) {
                        @unlink($fotoLama);
                    }
                }

                $stmtUpdate = $koneksi->prepare("UPDATE tb_user SET foto = ? WHERE id_user = ?");
                $stmtUpdate->execute([$namaFileBaru, $id_user]);
                $_SESSION['foto'] = $namaFileBaru;

                header("Location: edit_profil.php?sukses=Foto profil berhasil diperbarui");
                exit;
            } else {
                $error = 'Gagal menyimpan foto. Coba lagi.';
            }
        }
    }

    $stmtUser->execute([$id_user]);
    $user = $stmtUser->fetch();
}

// ==== HAPUS FOTO PROFIL ====
if (isset($_GET['hapus_foto'])) {
    if (!empty($user['foto'])) {
        $fotoLama = __DIR__ . '/../uploads/profil/' . $user['foto'];
        if (file_exists($fotoLama)) {
            @unlink($fotoLama);
        }
        $stmtUpdate = $koneksi->prepare("UPDATE tb_user SET foto = NULL WHERE id_user = ?");
        $stmtUpdate->execute([$id_user]);
        $_SESSION['foto'] = null;
    }
    header("Location: edit_profil.php?sukses=Foto profil dihapus");
    exit;
}

// ==== GANTI PASSWORD ====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['aksi'] ?? '') === 'ganti_password') {
    $lama       = $_POST['password_lama'] ?? '';
    $baru       = $_POST['password_baru'] ?? '';
    $konfirmasi = $_POST['konfirmasi'] ?? '';

    if (!password_verify($lama, $user['password'])) {
        header("Location: edit_profil.php?gagal=Password lama salah");
        exit;
    } elseif (strlen($baru) < 5) {
        header("Location: edit_profil.php?gagal=Password baru minimal 5 karakter");
        exit;
    } elseif ($baru !== $konfirmasi) {
        header("Location: edit_profil.php?gagal=Konfirmasi password tidak sama");
        exit;
    }

    $hash = password_hash($baru, PASSWORD_DEFAULT);
    $koneksi->prepare("UPDATE tb_user SET password = ? WHERE id_user = ?")->execute([$hash, $id_user]);
    header("Location: edit_profil.php?sukses=Password berhasil diperbarui");
    exit;
}

if (isset($_GET['gagal'])) $error = $_GET['gagal'];

include __DIR__ . '/components/header.php';
?>

<div class="row g-3">
    <div class="col-lg-5">
        <div class="card card-gacoan mb-3">
            <div class="card-header">Foto Profil</div>
            <div class="card-body text-center">

                <?php if ($error): ?>
                    <div class="alert alert-danger py-2"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <div class="mb-3">
                    <?php if (!empty($user['foto'])): ?>
                        <img src="<?= BASE_URL ?>uploads/profil/<?= htmlspecialchars($user['foto']) ?>?v=<?= time() ?>"
                             alt="Foto Profil"
                             style="width:140px;height:140px;border-radius:50%;object-fit:cover;border:3px solid #eee;">
                    <?php else: ?>
                        <div style="width:140px;height:140px;border-radius:50%;background:linear-gradient(135deg,#e0201a,#ffc633);
                                    display:flex;align-items:center;justify-content:center;color:#fff;font-size:3rem;
                                    font-weight:700;margin:0 auto;">
                            <?= strtoupper(substr($user['nama_lengkap'], 0, 1)) ?>
                        </div>
                    <?php endif; ?>
                </div>

                <form method="POST" enctype="multipart/form-data" class="d-flex flex-column align-items-center gap-2">
                    <input type="hidden" name="aksi" value="upload_foto">
                    <input type="file" name="foto" accept=".jpg,.jpeg,.png,.webp" class="form-control" required>
                    <small class="text-muted">Format JPG/PNG/WEBP, maksimal 2 MB.</small>
                    <button type="submit" class="btn btn-gacoan w-100 mt-2">
                        <i class="bi bi-upload"></i> Unggah Foto Baru
                    </button>
                </form>

                <?php if (!empty($user['foto'])): ?>
                    <a href="?hapus_foto=1"
                       onclick="return confirm('Hapus foto profil?');"
                       class="btn btn-outline-danger btn-sm w-100 mt-2">
                        <i class="bi bi-trash"></i> Hapus Foto
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <div class="card card-gacoan">
            <div class="card-header">Ganti Password</div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="aksi" value="ganti_password">
                    <div class="mb-2">
                        <label class="form-label">Password Lama</label>
                        <input type="password" name="password_lama" class="form-control" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Password Baru</label>
                        <input type="password" name="password_baru" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Konfirmasi Password Baru</label>
                        <input type="password" name="konfirmasi" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-gacoan w-100">Ubah Password</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card card-gacoan">
            <div class="card-header">Informasi Akun</div>
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tr>
                        <td class="text-muted" style="width:160px;">Nama Lengkap</td>
                        <td class="fw-semibold"><?= htmlspecialchars($user['nama_lengkap']) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Username</td>
                        <td class="fw-semibold"><?= htmlspecialchars($user['username']) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Role</td>
                        <td class="fw-semibold">User</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Bergabung Sejak</td>
                        <td class="fw-semibold"><?= date('d M Y', strtotime($user['created_at'])) ?></td>
                    </tr>
                </table>
                <p class="text-muted small mt-3 mb-0">
                    Untuk mengubah nama atau username, silakan hubungi Administrator.
                </p>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/components/footer.php'; ?>
