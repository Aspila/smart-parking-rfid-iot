<?php
$pageTitle = 'Live IoT Monitor';
$pageSubtitle = 'Pantau request dari Wokwi atau ESP32 secara langsung melalui API scan.';
require_once __DIR__ . '/includes/header.php';

$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
$baseUrl = $scheme . '://' . $host . ($basePath && $basePath !== '/' ? $basePath : '');
$apiUrl = $baseUrl . '/api/scan.php';
$pingUrl = $baseUrl . '/api/ping.php';
$validUid = '04A1B2C3D4';
$invalidUid = 'FFFFFFFF';
?>
<div class="grid grid-3 live-summary">
    <div class="card stat compact-stat">
        <div class="label">Total Log</div>
        <div class="number" id="statLogs">-</div>
        <div class="note">Seluruh request scan yang masuk.</div>
    </div>
    <div class="card stat compact-stat">
        <div class="label">Diterima Hari Ini</div>
        <div class="number" id="statAccepted">-</div>
        <div class="note">Kartu valid dan akses terbuka.</div>
    </div>
    <div class="card stat compact-stat">
        <div class="label">Ditolak Hari Ini</div>
        <div class="number" id="statRejected">-</div>
        <div class="note">Kartu tidak valid atau data belum lengkap.</div>
    </div>
</div>

<div class="grid grid-2" style="margin-top:18px">
    <div class="card">
        <div class="card-title">
            <h3>Status Koneksi API</h3>
            <span class="badge badge-info" id="apiStatus">Menunggu</span>
        </div>
        <div class="endpoint-box">
            <label>URL API untuk Wokwi atau ESP32</label>
            <div class="copy-row">
                <input class="input" id="apiUrlInput" value="<?= e($apiUrl) ?>" readonly>
                <button class="btn btn-light" type="button" id="copyApiBtn">Copy</button>
            </div>
            <small>Masukkan URL ini ke variabel <b>API_URL</b> di sketch Wokwi.</small>
        </div>
        <div class="endpoint-box" style="margin-top:14px">
            <label>Contoh test dari browser</label>
            <div class="copy-row">
                <input class="input" id="testUrlInput" value="<?= e($apiUrl . '?uid=' . $validUid . '&gate_id=1&area_id=1') ?>" readonly>
                <button class="btn btn-light" type="button" id="copyTestBtn">Copy</button>
            </div>
            <small>Jika URL ini menghasilkan JSON, maka API web sudah menerima request.</small>
        </div>
        <div class="form-actions">
            <button class="btn btn-primary" type="button" id="pingBtn">Cek API</button>
            <button class="btn btn-success" type="button" id="testValidBtn">Test UID Valid</button>
            <button class="btn btn-danger" type="button" id="testInvalidBtn">Test UID Salah</button>
        </div>
        <div class="api-response" id="apiResponse">Belum ada response.</div>
    </div>

    <div class="card">
        <div class="card-title">
            <h3>Panduan URL Cepat</h3>
            <span class="badge badge-dark">Ngrok</span>
        </div>
        <div class="guide-list">
            <div>
                <b>Jika Laragon memakai domain .test</b>
                <p>Jalankan ngrok dengan host header, lalu pakai URL API tanpa nama folder.</p>
                <code>ngrok http --host-header=nama-folder.test 80</code>
            </div>
            <div>
                <b>Jika web dibuka dari folder localhost</b>
                <p>Jalankan ngrok port 80, lalu pakai URL API dengan nama folder proyek.</p>
                <code>ngrok http 80</code>
            </div>
            <div>
                <b>Header untuk Wokwi</b>
                <p>Sketch sudah memakai header ini agar tidak terkena halaman warning ngrok free.</p>
                <code>ngrok-skip-browser-warning: true</code>
            </div>
        </div>
    </div>
</div>

<div class="card" style="margin-top:18px">
    <div class="card-title">
        <h3>Live Riwayat Scan</h3>
        <span class="badge badge-success" id="liveBadge">Auto refresh aktif</span>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Waktu</th>
                    <th>UID</th>
                    <th>Pengguna</th>
                    <th>Kendaraan</th>
                    <th>Akses</th>
                    <th>Status</th>
                    <th>Gerbang</th>
                    <th>Keterangan</th>
                </tr>
            </thead>
            <tbody id="liveLogsBody">
                <tr><td colspan="8" class="empty">Memuat data...</td></tr>
            </tbody>
        </table>
    </div>
</div>

<script>
window.IOT_MONITOR_CONFIG = {
    apiUrl: <?= json_encode($apiUrl) ?>,
    pingUrl: <?= json_encode($pingUrl) ?>,
    latestUrl: 'api/latest.php?limit=12',
    statsUrl: 'api/stats.php',
    validUid: <?= json_encode($validUid) ?>,
    invalidUid: <?= json_encode($invalidUid) ?>
};
</script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
