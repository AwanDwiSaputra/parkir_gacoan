# 🅿️ Gacoan Parking System

Aplikasi manajemen parkir **"Parkir Gacoan"**, dibangun dengan PHP native + MySQL (PDO)
dan tampilan Bootstrap 5. Sistem mendukung 4 peran pengguna (Admin, Petugas, Owner, Member)
dengan dashboard dan hak akses masing-masing, lengkap dengan landing page publik, fitur
booking online, dan testimoni pengguna.

## 1. Fitur Utama

- **Landing page publik** (`index.php`) — profil layanan, daftar fitur, grafik kepadatan
  parkir per jam, video area parkir, serta form testimoni pengguna.
- **Autentikasi & Role-Based Access** — login/register dengan redirect otomatis ke
  dashboard sesuai peran (`admin`, `petugas`, `owner`, `member`).
- **Booking online (Member)** — member dapat booking slot parkir, memilih kendaraan &
  area, mengecek ketersediaan slot, serta melihat status booking secara real-time.
- **Transaksi parkir (Petugas/Operator)** — input kendaraan masuk (manual atau dari
  booking member), proses kendaraan keluar dengan perhitungan tarif otomatis, dan
  cetak struk.
- **Master data & administrasi (Admin)** — kelola user, tarif, area parkir, kendaraan,
  moderasi testimoni, serta log aktivitas seluruh pengguna.
- **Laporan (Owner)** — dashboard ringkasan pendapatan dan rekap transaksi berdasarkan
  rentang tanggal.

## 2. Struktur Folder

```
parkir_cloud/
├── admin/                    -> Halaman & fitur untuk role ADMIN
│   ├── template/               (header, footer, sidebar_admin)
│   ├── index.php               (dashboard admin)
│   ├── kelola_user.php         (CRUD user: admin/petugas/owner/member)
│   ├── kelola_tarif.php        (CRUD tarif parkir per jenis kendaraan)
│   ├── kelola_area.php         (CRUD area parkir & kapasitas)
│   ├── kelola_kendaraan.php    (CRUD data kendaraan)
│   ├── testimoni.php           (daftar testimoni)
│   ├── moderasi_testimoni.php  (approve/reject testimoni)
│   ├── testimoni_action.php    (proses aksi moderasi)
│   ├── log_aktivitas.php       (akses log aktivitas seluruh user)
│   └── profil.php              (profil admin)
├── auth/                      -> login.php, logout.php, register.php
├── assets/                    -> css, js, logo.php
├── config/                    -> koneksi.php (koneksi DB + helper), cek_denda_parkir.php
├── database/                  -> gaca.sql (struktur & data awal database)
├── img/                       -> aset gambar & video landing page (logo, banner, QRIS)
├── member/                    -> Halaman & fitur untuk role MEMBER (pengguna terdaftar)
│   ├── components/             (header, footer, sidebar_user)
│   ├── index.php               (dashboard member: daftar booking)
│   ├── booking.php             (form booking slot parkir)
│   ├── cek_slot_area.php       (cek ketersediaan slot area, dipanggil via AJAX)
│   ├── cek_status_booking.php  (cek status booking, dipanggil via AJAX)
│   ├── kendaraan.php           (kelola kendaraan milik member)
│   ├── riwayat_parkir.php      (riwayat parkir member)
│   └── edit_profil.php         (edit profil member)
├── operator/                  -> Halaman & fitur untuk role PETUGAS
│   ├── components/             (navbar, header, footer, sidebar_operator)
│   ├── index.php               (dashboard petugas)
│   ├── cek_booking_baru.php    (cek notifikasi booking baru, dipanggil via AJAX)
│   ├── kelola_booking.php      (konfirmasi/tolak booking dari member)
│   ├── transaksi_masuk.php     (input kendaraan masuk, manual/dari booking)
│   ├── transaksi_keluar.php    (proses kendaraan keluar & hitung biaya)
│   ├── cetak_struk.php         (cetak struk masuk/keluar)
│   ├── riwayat_transaksi.php   (riwayat transaksi milik petugas)
│   └── edit_profil.php         (edit profil petugas)
├── owner/                     -> Halaman & fitur untuk role OWNER
│   ├── components/             (sidebar_owner, header, footer)
│   ├── index.php               (dashboard owner: grafik pendapatan)
│   ├── rekap_transaksi.php     (rekap transaksi sesuai rentang tanggal)
│   └── edit_profil.php         (edit profil owner)
├── uploads/                   -> profil, profile_admin, profile_operator, profile_user
├── testimoni_submit.php       -> proses submit testimoni dari landing page
└── index.php                  -> landing page publik + redirect otomatis ke dashboard
```

> **Catatan:** folder `user/` yang mungkin masih ada di dalam repo merupakan sisa versi
> lama (legacy) dan **sudah digantikan sepenuhnya oleh folder `member/`**. Folder
> tersebut tidak lagi direferensikan di manapun pada sistem (login, sidebar, maupun
> tabel `role` pada database) dan aman untuk dihapus.

## 3. Instalasi (XAMPP / Laragon)

1. Copy folder `parkir_cloud` ke dalam `htdocs` (XAMPP) atau `www` (Laragon).
2. Buka **phpMyAdmin**, buat database baru bernama **`gaca`**, lalu **Import** file
   `database/gaca.sql` (seluruh tabel beserta data contoh akan otomatis dibuat).
3. Buka `config/koneksi.php`, sesuaikan bila perlu:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'gaca');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   define('BASE_URL', 'http://localhost/parkir_cloud/');
   ```
   Ubah `BASE_URL` jika nama folder project Anda berbeda.
4. Akses aplikasi melalui `http://localhost/parkir_cloud/`.

## 4. Akun Default (Seeder)

| Role     | Username  | Password  |
|----------|-----------|-----------|
| Admin    | admin     | (sesuai hash pada seeder, silakan reset via `password_hash` bila lupa) |
| Petugas  | petugas   | (sesuai hash pada seeder) |
| Owner    | owner     | (sesuai hash pada seeder) |
| Member   | awan      | (sesuai hash pada seeder) |

Pendaftaran akun baru melalui halaman **Daftar** (`auth/register.php`) otomatis
membuat akun dengan role **member**. Segera ubah password melalui menu **Edit Profil**
atau **Kelola User** (admin) setelah login pertama kali.

## 5. Hak Akses Fitur

| Fitur                                  | Admin | Petugas | Owner | Member |
|------------------------------------------|:-----:|:-------:|:-----:|:------:|
| Login / Register / Logout                 | ✔     | ✔       | ✔     | ✔      |
| CRUD User                                 | ✔     |         |       |        |
| CRUD Tarif Parkir                         | ✔     |         |       |        |
| CRUD Area Parkir                          | ✔     |         |       |        |
| CRUD Data Kendaraan (master)              | ✔     |         |       |        |
| Moderasi Testimoni                        | ✔     |         |       |        |
| Akses Log Aktivitas                       | ✔     |         |       |        |
| Kelola / Konfirmasi Booking Member        |       | ✔       |       |        |
| Kendaraan Masuk (Transaksi)               |       | ✔       |       |        |
| Kendaraan Keluar + Cetak Struk            |       | ✔       |       |        |
| Riwayat Transaksi Petugas                 |       | ✔       |       |        |
| Rekap Transaksi sesuai periode            |       |         | ✔     |        |
| Dashboard Pendapatan                      |       |         | ✔     |        |
| Booking Slot Parkir                       |       |         |       | ✔      |
| Kelola Kendaraan Pribadi                  |       |         |       | ✔      |
| Riwayat Parkir Pribadi                    |       |         |       | ✔      |
| Kirim Testimoni (landing page)            | ✔     | ✔       | ✔     | ✔      |

## 6. Skema Database

Database **`gaca`** terdiri dari tabel-tabel berikut (lihat `database/gaca.sql`):

- `tb_user` — data pengguna & role (`admin`, `petugas`, `owner`, `member`).
- `tb_kendaraan` — data kendaraan, terhubung ke pemilik (`id_user`).
- `tb_area_parkir` — data area parkir beserta kapasitas & slot terisi.
- `tb_tarif` — tarif parkir per jenis kendaraan (motor, mobil, truk/bus).
- `tb_booking` — booking slot parkir oleh member (`menunggu`, `dikonfirmasi`,
  `dibatalkan`, `selesai`).
- `tb_transaksi` — transaksi parkir masuk/keluar beserta biaya & metode bayar
  (tunai/QRIS).
- `tb_log_aktivitas` — log aktivitas seluruh pengguna (login, logout, perubahan data).
- `testimoni` — testimoni dari landing page (`pending`, `approved`, `rejected`).

Relasi antar tabel dijaga dengan **FOREIGN KEY** (mis. `tb_booking.id_user` →
`tb_user.id_user`, `tb_transaksi.id_kendaraan` → `tb_kendaraan.id_kendaraan`, dsb).

## 7. Alur Kerja Aplikasi

1. **Member** login, mendaftarkan kendaraan (`kendaraan.php`), lalu membuat booking
   slot parkir (`booking.php`) dengan memilih kendaraan, area, tanggal & jam.
2. **Petugas** menerima notifikasi booking baru (`cek_booking_baru.php`), meninjau &
   mengonfirmasi booking (`kelola_booking.php`).
3. Saat kendaraan tiba, petugas mencatat kendaraan masuk (`transaksi_masuk.php`) —
   baik dari booking yang sudah dikonfirmasi maupun input manual — sistem otomatis
   mengurangi slot area parkir yang tersedia.
4. Saat kendaraan keluar, petugas memproses pada `transaksi_keluar.php` — sistem
   menghitung durasi parkir & biaya otomatis berdasarkan tarif per jam, lalu struk
   dicetak (`cetak_struk.php`).
5. **Admin** mengelola master data (user, tarif, area, kendaraan), memoderasi
   testimoni yang masuk dari landing page, dan memantau log aktivitas seluruh
   pengguna.
6. **Owner** memantau dashboard pendapatan & dapat melihat rekap transaksi pada
   rentang tanggal tertentu.
7. Pengunjung landing page (termasuk yang belum login) dapat melihat testimoni
   yang telah disetujui admin dan mengirim testimoni baru melalui
   `testimoni_submit.php`.

## 8. Teknologi

- **Backend:** PHP native (PDO untuk akses database, tanpa framework)
- **Database:** MySQL / MariaDB
- **Frontend:** Bootstrap 5, Bootstrap Icons, Chart.js (grafik kepadatan & pendapatan)
- **Lainnya:** Google Fonts (Plus Jakarta Sans, Poppins)

---
**Gacoan Parking System** — dibuat oleh **Awan Dwi Saputro**.
