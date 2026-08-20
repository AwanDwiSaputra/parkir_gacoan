<?php
/**
 * cek_denda_parkir.php
 *
 * Panggil fungsi cekDendaParkir($koneksi) ini di halaman yang sering dibuka
 * petugas/admin (misalnya dashboard atau daftar kendaraan yang sedang parkir),
 * paling atas file, SEBELUM ada output HTML apa pun. Contoh pemakaian:
 *
 *   require_once __DIR__ . '/../config/koneksi.php';
 *   require_once __DIR__ . '/../config/cek_denda_parkir.php';
 *   cekDendaParkir($koneksi);
 *
 * Setiap kali halaman dibuka, semua kendaraan yang sedang parkir (status='masuk')
 * dan sudah lebih dari BATAS_PARKIR_MENIT akan otomatis dikenakan denda sekali
 * per transaksi (id_parkir), tidak akan didobel walau halaman dibuka berkali-kali.
 */

const BATAS_PARKIR_MENIT   = 40;   // batas waktu parkir sebelum kena denda
const DENDA_PARKIR_LAMA    = 5000; // nominal denda (Rp) untuk parkir kelamaan

function cekDendaParkir(PDO $koneksi): void
{
    // Cari transaksi yang masih "masuk" (belum keluar), sudah lewat batas waktu,
    // dan BELUM pernah dicatat dendanya (dicek via NOT EXISTS ke tb_denda).
    $stmtCari = $koneksi->prepare(
        "SELECT t.id_parkir, t.id_user
         FROM tb_transaksi t
         WHERE t.status = 'masuk'
           AND t.waktu_keluar IS NULL
           AND t.waktu_masuk < (NOW() - INTERVAL :batas MINUTE)
           AND NOT EXISTS (
               SELECT 1 FROM tb_denda d
               WHERE d.id_parkir = t.id_parkir
           )"
    );
    $stmtCari->execute(['batas' => BATAS_PARKIR_MENIT]);
    $transaksiLama = $stmtCari->fetchAll();

    if (empty($transaksiLama)) {
        return;
    }

    $insertDenda = $koneksi->prepare(
        "INSERT INTO tb_denda (id_user, id_parkir, jumlah, alasan, status)
         VALUES (?, ?, ?, ?, 'belum_bayar')"
    );

    foreach ($transaksiLama as $t) {
        $insertDenda->execute([
            $t['id_user'],
            $t['id_parkir'],
            DENDA_PARKIR_LAMA,
            'Parkir melebihi ' . BATAS_PARKIR_MENIT . ' menit',
        ]);
    }
}