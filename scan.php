<?php
$pageTitle='Scan RFID';
$pageSubtitle='Uji validasi kartu RFID secara manual sebelum dihubungkan dengan ESP32/ESP8266.';
require_once __DIR__ . '/includes/header.php';
$result=null;
if($_SERVER['REQUEST_METHOD']==='POST'){
    $uid=$_POST['uid']??'';
    $jenis=$_POST['jenis_akses']??null;
    $gate=(int)($_POST['id_gate']??0) ?: null;
    $area=(int)($_POST['id_area']??0) ?: null;
    $result=validate_rfid_access($pdo,$uid,$jenis,$gate,$area,$_SERVER['REMOTE_ADDR']??null);
}
$gates=$pdo->query('SELECT id_gate,nama_gate,kode_gate FROM gates WHERE status=1 ORDER BY nama_gate')->fetchAll();
$areas=$pdo->query('SELECT id_area,nama_area FROM parking_areas WHERE status=1 ORDER BY nama_area')->fetchAll();
$latest=$pdo->query("SELECT l.*,u.nama_user,u.kode_user FROM access_logs l LEFT JOIN users u ON u.id_user=l.id_user ORDER BY l.id_akses DESC LIMIT 6")->fetchAll();
?>
<div class="grid grid-2">
    <div class="card scan-box">
        <div class="scan-icon">◎</div>
        <h2 style="margin:0 0 8px">Simulasi Scan RFID</h2>
        <p style="color:#64748b;margin-top:0">Masukkan UID kartu. Sistem akan memvalidasi data dan membuat log akses.</p>
        <form method="post" style="text-align:left;margin-top:18px">
            <div class="form-group"><label>UID Kartu</label><input class="input" name="uid" placeholder="Contoh: 04A1B2C3D4" required autofocus></div>
            <div class="form-grid" style="margin-top:14px">
                <div class="form-group"><label>Jenis Akses</label><select class="select" name="jenis_akses"><option value="">Otomatis</option><option>Masuk</option><option>Keluar</option></select></div>
                <div class="form-group"><label>Gerbang</label><select class="select" name="id_gate"><?php foreach($gates as $g): ?><option value="<?= $g['id_gate'] ?>"><?= e($g['kode_gate'].' - '.$g['nama_gate']) ?></option><?php endforeach; ?></select></div>
                <div class="form-group"><label>Area Parkir</label><select class="select" name="id_area"><?php foreach($areas as $a): ?><option value="<?= $a['id_area'] ?>"><?= e($a['nama_area']) ?></option><?php endforeach; ?></select></div>
            </div>
            <button class="btn btn-primary btn-block" style="margin-top:16px">Validasi Kartu</button>
        </form>
        <?php if($result): ?>
        <div class="scan-result">
            <h3 style="margin-top:0"><span class="status-dot <?= $result['success']?'dot-green':'dot-red' ?>"></span><?= e($result['status']) ?></h3>
            <p><b>UID:</b> <?= e($result['uid']) ?></p>
            <p><b>Pengguna:</b> <?= e($result['nama_user'] ?: '-') ?></p>
            <p><b>Jenis akses:</b> <?= e($result['jenis_akses']) ?></p>
            <p><b>Instruksi servo:</b> <?= e($result['servo']) ?></p>
            <p><b>Keterangan:</b> <?= e($result['message']) ?></p>
        </div>
        <?php endif; ?>
    </div>
    <div class="card">
        <div class="card-title"><h3>Scan Terakhir</h3><a class="btn btn-light btn-sm" href="access_logs.php">Riwayat</a></div>
        <div class="table-wrap"><table><thead><tr><th>Waktu</th><th>UID</th><th>Pengguna</th><th>Akses</th><th>Status</th></tr></thead><tbody><?php foreach($latest as $r): ?><tr><td><?= e(date('H:i:s',strtotime($r['waktu_akses']))) ?></td><td><b><?= e($r['uid_kartu']) ?></b></td><td><?= e($r['nama_user']?:'-') ?></td><td><?= e($r['jenis_akses']) ?></td><td><span class="badge <?= $r['status_akses']=='Diterima'?'badge-success':'badge-danger' ?>"><?= e($r['status_akses']) ?></span></td></tr><?php endforeach; ?></tbody></table></div>
        <div class="alert alert-info" style="margin-top:16px">UID data awal yang valid: <b>04A1B2C3D4</b>, <b>04D5E6F7A8</b>, <b>04ABCDEF12</b>, <b>04F1F2F3F4</b>.</div>
    </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
