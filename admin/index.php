<?php
require_once __DIR__ . '/../config/koneksi.php';
cekLogin(['admin']);
$page_title = 'Dashboard Admin';

$totalUser      = $koneksi->query("SELECT COUNT(*) c FROM tb_user")->fetch()['c'];
$totalKendaraan = $koneksi->query("SELECT COUNT(*) c FROM tb_kendaraan")->fetch()['c'];
$sedangParkir   = $koneksi->query("SELECT COUNT(*) c FROM tb_transaksi WHERE status='masuk'")->fetch()['c'];
$pendapatanHariIni = $koneksi->query("SELECT COALESCE(SUM(biaya_total),0) c FROM tb_transaksi WHERE status='keluar' AND DATE(waktu_keluar)=CURDATE()")->fetch()['c'];
$testimoniPending = $koneksi->query("SELECT COUNT(*) c FROM testimoni WHERE status='pending'")->fetch()['c'];

$area = $koneksi->query("SELECT * FROM tb_area_parkir ORDER BY nama_area")->fetchAll();

$logTerbaru = $koneksi->query(
    "SELECT l.*, u.nama_lengkap FROM tb_log_aktivitas l
     LEFT JOIN tb_user u ON u.id_user = l.id_user
     ORDER BY l.waktu_aktivitas DESC LIMIT 8"
)->fetchAll();

// ===== Data grafik: pendapatan & jumlah kendaraan keluar, 7 hari terakhir =====
$stmtGrafik = $koneksi->prepare(
    "SELECT DATE(waktu_keluar) AS tgl, COALESCE(SUM(biaya_total),0) AS total_pendapatan, COUNT(*) AS jumlah_transaksi
     FROM tb_transaksi
     WHERE status = 'keluar' AND waktu_keluar >= (CURDATE() - INTERVAL 6 DAY)
     GROUP BY DATE(waktu_keluar)
     ORDER BY tgl ASC"
);
$stmtGrafik->execute();
$hasilGrafik = $stmtGrafik->fetchAll(PDO::FETCH_ASSOC);

// Susun ulang jadi 7 hari berurutan (isi 0 untuk hari yang tidak ada transaksi)
$petaGrafik = [];
foreach ($hasilGrafik as $row) {
    $petaGrafik[$row['tgl']] = $row;
}

$labelGrafik = [];
$dataPendapatan = [];
$dataTransaksi = [];
for ($i = 6; $i >= 0; $i--) {
    $tgl = date('Y-m-d', strtotime("-$i day"));
    $labelGrafik[]    = date('d M', strtotime($tgl));
    $dataPendapatan[] = isset($petaGrafik[$tgl]) ? (float)$petaGrafik[$tgl]['total_pendapatan'] : 0;
    $dataTransaksi[]  = isset($petaGrafik[$tgl]) ? (int)$petaGrafik[$tgl]['jumlah_transaksi'] : 0;
}

include __DIR__ . '/template/header.php';
?>

<div class="row g-3 mb-3">
    <div class="col-md-3 col-6">
        <div class="stat-card bg-grad-red">
            <div class="stat-icon"><i class="bi bi-people"></i></div>
            <h3><?= $totalUser ?></h3>
            <span class="label">Total User</span>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="stat-card bg-grad-orange">
            <div class="stat-icon"><i class="bi bi-car-front"></i></div>
            <h3><?= $totalKendaraan ?></h3>
            <span class="label">Kendaraan Terdaftar</span>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="stat-card bg-grad-dark">
            <div class="stat-icon"><i class="bi bi-p-circle"></i></div>
            <h3><?= $sedangParkir ?></h3>
            <span class="label">Sedang Parkir</span>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="stat-card bg-grad-green">
            <div class="stat-icon"><i class="bi bi-cash-stack"></i></div>
            <h3 style="font-size:1.25rem;"><?= rupiah($pendapatanHariIni) ?></h3>
            <span class="label">Pendapatan Hari Ini</span>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-lg-7">
        <div class="card card-gacoan h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-graph-up-arrow"></i> Pendapatan 7 Hari Terakhir</span>
            </div>
            <div class="card-body">
                <div style="position:relative; height:300px;">
                    <canvas id="chartPendapatan"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card card-gacoan h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-bar-chart-line"></i> Jumlah Transaksi 7 Hari Terakhir</span>
            </div>
            <div class="card-body">
                <div style="position:relative; height:300px;">
                    <canvas id="chartTransaksi"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-5">
        <div class="card card-gacoan mb-3">
            <div class="card-header">Okupansi Area Parkir</div>
            <div class="card-body">
                <?php foreach ($area as $a): $sisa = $a['kapasitas'] - $a['terisi']; $p = $a['kapasitas'] > 0 ? round(($a['terisi']/$a['kapasitas'])*100) : 0; ?>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between small fw-semibold mb-1">
                            <span><?= htmlspecialchars($a['nama_area']) ?></span>
                            <span><?= $a['terisi'] ?>/<?= $a['kapasitas'] ?></span>
                        </div>
                        <div class="progress" style="height:10px;border-radius:50px;">
                            <div class="progress-bar <?= $p>=90?'bg-danger':($p>=60?'bg-warning':'bg-success') ?>" style="width:<?= $p ?>%"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php if ($testimoniPending > 0): ?>
        <div class="card card-gacoan">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <strong><?= $testimoniPending ?> Testimoni Menunggu Moderasi</strong>
                    <div class="small text-muted">Perlu ditinjau sebelum tampil di beranda</div>
                </div>
                <a href="moderasi_testimoni.php" class="btn btn-gacoan btn-sm">Tinjau</a>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <div class="col-lg-7">
        <div class="card card-gacoan">
            <div class="card-header">Log Aktivitas Terbaru</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead><tr><th>Waktu</th><th>User</th><th>Aktivitas</th></tr></thead>
                        <tbody>
                        <?php foreach ($logTerbaru as $log): ?>
                            <tr>
                                <td class="small"><?= formatTanggal($log['waktu_aktivitas']) ?></td>
                                <td class="small fw-semibold"><?= htmlspecialchars($log['nama_lengkap'] ?? 'Sistem') ?></td>
                                <td class="small"><?= htmlspecialchars($log['aktivitas']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($logTerbaru)): ?>
                            <tr><td colspan="3" class="text-center text-muted py-4">Belum ada aktivitas</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="text-end p-2">
                    <a href="log_aktivitas.php" class="small fw-semibold" style="color:var(--gc-red);">Lihat semua log &rarr;</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
    const ctxPendapatan = document.getElementById('chartPendapatan');
    if (ctxPendapatan) {
        new Chart(ctxPendapatan, {
            type: 'bar',
            data: {
                labels: <?= json_encode($labelGrafik) ?>,
                datasets: [{
                    label: 'Pendapatan (Rp)',
                    data: <?= json_encode($dataPendapatan) ?>,
                    backgroundColor: '#f4b400',
                    borderRadius: 8,
                    maxBarThickness: 46
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function (ctx) { return 'Rp ' + ctx.parsed.y.toLocaleString('id-ID'); }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function (value) { return 'Rp ' + value.toLocaleString('id-ID'); }
                        },
                        grid: { color: 'rgba(0,0,0,.06)' }
                    },
                    x: { grid: { display: false } }
                }
            }
        });
    }

    const ctxTransaksi = document.getElementById('chartTransaksi');
    if (ctxTransaksi) {
        new Chart(ctxTransaksi, {
            type: 'line',
            data: {
                labels: <?= json_encode($labelGrafik) ?>,
                datasets: [{
                    label: 'Jumlah Transaksi',
                    data: <?= json_encode($dataTransaksi) ?>,
                    borderColor: '#dc3545',
                    backgroundColor: 'rgba(220,53,69,.12)',
                    tension: 0.35,
                    fill: true,
                    pointRadius: 4,
                    pointBackgroundColor: '#dc3545'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function (ctx) { return ctx.parsed.y + ' transaksi'; }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { precision: 0 },
                        grid: { color: 'rgba(0,0,0,.06)' }
                    },
                    x: { grid: { display: false } }
                }
            }
        });
    }
</script>

<?php include __DIR__ . '/template/footer.php'; ?>