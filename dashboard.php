<?php
$pageTitle = 'Dashboard';
$pageSubtitle = 'Ringkasan aktivitas parkir, status pengguna, kartu RFID, dan akses terbaru.';
require_once __DIR__ . '/includes/header.php';
$totalUsers = count_table($pdo, 'users');
$totalCards = count_table($pdo, 'rfid_cards');
$totalVehicles = count_table($pdo, 'vehicles');
$totalLogs = count_table($pdo, 'access_logs');
$acceptedToday = count_table($pdo, 'access_logs', "DATE(waktu_akses)=CURDATE() AND status_akses='Diterima'");
$rejectedToday = count_table($pdo, 'access_logs', "DATE(waktu_akses)=CURDATE() AND status_akses='Ditolak'");
$currentCount = current_parking_count($pdo);
$capacity = (int)$pdo->query('SELECT COALESCE(SUM(kapasitas),0) total FROM parking_areas WHERE status=1')->fetch()['total'];
$percent = $capacity ? min(100, round(($currentCount/$capacity)*100)) : 0;
$freeCapacity = max(0, $capacity - $currentCount);
$recent = $pdo->query("SELECT l.*, u.nama_user, u.kode_user, u.jenis_user, g.nama_gate, a.nama_area
                       FROM access_logs l
                       LEFT JOIN users u ON u.id_user=l.id_user
                       LEFT JOIN gates g ON g.id_gate=l.id_gate
                       LEFT JOIN parking_areas a ON a.id_area=l.id_area
                       ORDER BY l.waktu_akses DESC, l.id_akses DESC LIMIT 8")->fetchAll();
$daily = $pdo->query("SELECT DATE(waktu_akses) tgl, COUNT(*) total FROM access_logs WHERE waktu_akses >= DATE_SUB(CURDATE(), INTERVAL 6 DAY) GROUP BY DATE(waktu_akses) ORDER BY tgl ASC")->fetchAll();
$maxDaily = max(array_column($daily ?: [['total'=>1]], 'total')) ?: 1;
?>
<div class="dashboard-hero">
    <section class="card hero-panel">
        <div class="hero-content">
            <div>
                <span class="hero-eyebrow">RFID IoT Parking Control</span>
                <h2>Kontrol akses parkir lebih rapi, cepat, dan mudah dipantau.</h2>
                <p>Dashboard ini menampilkan data pengguna, kartu RFID, kendaraan, kapasitas area, dan riwayat akses terbaru dalam satu halaman.</p>
                <div class="hero-actions">
                    <a class="btn btn-primary" href="scan.php">Mulai Scan RFID</a>
                    <a class="btn btn-light" href="reports.php">Lihat Laporan</a>
                </div>
            </div>
            <div class="hero-mini-grid">
                <div class="hero-mini"><b><?= $acceptedToday ?></b><span>Diterima hari ini</span></div>
                <div class="hero-mini"><b><?= $rejectedToday ?></b><span>Ditolak hari ini</span></div>
                <div class="hero-mini"><b><?= $totalLogs ?></b><span>Total log akses</span></div>
            </div>
        </div>
    </section>
    <section class="card status-card">
        <div class="card-title"><h3>Kapasitas Parkir</h3><span class="badge badge-info"><?= $percent ?>%</span></div>
        <div class="status-ring" style="--value:<?= $percent ?>"><strong><?= $percent ?>%</strong></div>
        <div class="status-list">
            <div class="status-item"><span>Terisi</span><b><?= $currentCount ?> kendaraan</b></div>
            <div class="status-item"><span>Tersedia</span><b><?= $freeCapacity ?> slot</b></div>
            <div class="status-item"><span>Kapasitas</span><b><?= $capacity ?: 0 ?> slot</b></div>
        </div>
    </section>
</div>

<div class="grid grid-4">
    <div class="card stat"><div class="label">Pengguna</div><div class="number"><?= $totalUsers ?></div><div class="note">Mahasiswa, dosen, staf, dan tamu.</div><div class="icon">☷</div></div>
    <div class="card stat"><div class="label">Kartu RFID</div><div class="number"><?= $totalCards ?></div><div class="note">Kartu aktif dan terdaftar.</div><div class="icon">◈</div></div>
    <div class="card stat"><div class="label">Kendaraan</div><div class="number"><?= $totalVehicles ?></div><div class="note">Nomor polisi telah tersimpan.</div><div class="icon">▣</div></div>
    <div class="card stat"><div class="label">Log Akses</div><div class="number"><?= $totalLogs ?></div><div class="note">Seluruh catatan aktivitas sistem.</div><div class="icon">☰</div></div>
</div>

<div class="grid grid-3" style="margin-top:18px">
    <div class="card">
        <div class="card-title"><h3>Kendaraan di Area</h3><span class="badge badge-info"><?= $percent ?>%</span></div>
        <div class="number" style="font-size:38px;font-weight:950"><?= $currentCount ?> <span style="font-size:16px;color:#64748b">/ <?= $capacity ?: 0 ?> kapasitas</span></div>
        <div class="progress" style="margin-top:14px"><span style="width:<?= $percent ?>%"></span></div>
    </div>
    <div class="card">
        <div class="card-title"><h3>Akses Diterima Hari Ini</h3><span class="badge badge-success">Valid</span></div>
        <div class="number" style="font-size:38px;font-weight:950"><?= $acceptedToday ?></div>
        <p style="color:#64748b;margin:6px 0 0">Kartu valid, aktif, dan memiliki hak akses.</p>
    </div>
    <div class="card">
        <div class="card-title"><h3>Akses Ditolak Hari Ini</h3><span class="badge badge-danger">Ditolak</span></div>
        <div class="number" style="font-size:38px;font-weight:950"><?= $rejectedToday ?></div>
        <p style="color:#64748b;margin:6px 0 0">Kartu tidak terdaftar, tidak aktif, atau data belum lengkap.</p>
    </div>
</div>

<div class="kpi-row" style="margin-top:18px">
    <div class="card">
        <div class="card-title">
            <h3>Riwayat Akses Terbaru</h3>
            <a class="btn btn-light btn-sm" href="access_logs.php">Lihat Semua</a>
        </div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Waktu</th><th>Pengguna</th><th>UID</th><th>Akses</th><th>Status</th><th>Gerbang</th></tr></thead>
                <tbody>
                <?php foreach($recent as $r): ?>
                    <tr>
                        <td><?= e(date('d/m/Y H:i', strtotime($r['waktu_akses']))) ?></td>
                        <td><?= e($r['nama_user'] ?: '-') ?><br><small><?= e($r['kode_user'] ?: '-') ?></small></td>
                        <td><b><?= e($r['uid_kartu']) ?></b></td>
                        <td><?= e($r['jenis_akses']) ?></td>
                        <td><span class="badge <?= $r['status_akses']=='Diterima'?'badge-success':'badge-danger' ?>"><?= e($r['status_akses']) ?></span></td>
                        <td><?= e($r['nama_gate'] ?: '-') ?></td>
                    </tr>
                <?php endforeach; if(!$recent): ?><tr><td colspan="6" class="empty">Belum ada riwayat akses.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="card">
        <div class="card-title"><h3>Aktivitas 7 Hari</h3></div>
        <div class="chart-bars">
            <?php foreach($daily as $d): $w=round(($d['total']/$maxDaily)*100); ?>
            <div class="bar-row"><span><?= e(date('d M', strtotime($d['tgl']))) ?></span><div class="bar"><span style="width:<?= $w ?>%"></span></div><b><?= (int)$d['total'] ?></b></div>
            <?php endforeach; if(!$daily): ?><div class="empty">Belum ada data aktivitas.</div><?php endif; ?>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
