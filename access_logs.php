<?php
$pageTitle='Riwayat Akses';
$pageSubtitle='Pantau seluruh aktivitas masuk dan keluar kendaraan berdasarkan RFID.';
require_once __DIR__ . '/includes/header.php';
$q=trim($_GET['q']??'');$status=$_GET['status']??'';$jenis=$_GET['jenis']??'';$from=$_GET['from']??'';$to=$_GET['to']??'';
$where=[];$params=[];
if($q!==''){$where[]='(l.uid_kartu LIKE ? OR u.nama_user LIKE ? OR u.kode_user LIKE ? OR v.nomor_polisi LIKE ?)';$like="%$q%";$params=array_merge($params,[$like,$like,$like,$like]);}
if($status!==''){$where[]='l.status_akses=?';$params[]=$status;}
if($jenis!==''){$where[]='l.jenis_akses=?';$params[]=$jenis;}
if($from!==''){$where[]='DATE(l.waktu_akses)>=?';$params[]=$from;}
if($to!==''){$where[]='DATE(l.waktu_akses)<=?';$params[]=$to;}
$sqlWhere=$where?'WHERE '.implode(' AND ',$where):'';
$stmt=$pdo->prepare("SELECT l.*,u.nama_user,u.kode_user,u.jenis_user,v.nomor_polisi,g.nama_gate,a.nama_area FROM access_logs l LEFT JOIN users u ON u.id_user=l.id_user LEFT JOIN vehicles v ON v.id_vehicle=l.id_vehicle LEFT JOIN gates g ON g.id_gate=l.id_gate LEFT JOIN parking_areas a ON a.id_area=l.id_area $sqlWhere ORDER BY l.waktu_akses DESC,l.id_akses DESC LIMIT 250");
$stmt->execute($params);$rows=$stmt->fetchAll();
?>
<div class="card"><div class="toolbar"><h3 style="margin:0">Filter Riwayat</h3></div><form class="search-form"><input class="input" name="q" value="<?= e($q) ?>" placeholder="Nama, UID, kode, nopol"><select class="select" name="status"><option value="">Semua Status</option><option <?= $status==='Diterima'?'selected':'' ?>>Diterima</option><option <?= $status==='Ditolak'?'selected':'' ?>>Ditolak</option></select><select class="select" name="jenis"><option value="">Masuk & Keluar</option><option <?= $jenis==='Masuk'?'selected':'' ?>>Masuk</option><option <?= $jenis==='Keluar'?'selected':'' ?>>Keluar</option></select><input class="input" type="date" name="from" value="<?= e($from) ?>"><input class="input" type="date" name="to" value="<?= e($to) ?>"><button class="btn btn-primary">Terapkan</button><a class="btn btn-light" href="access_logs.php">Reset</a></form></div>
<div class="card" style="margin-top:18px"><div class="card-title"><h3>Data Riwayat</h3><span class="badge badge-dark"><?= count($rows) ?> data</span></div><div class="table-wrap"><table><thead><tr><th>Waktu</th><th>Pengguna</th><th>Kendaraan</th><th>UID</th><th>Akses</th><th>Status</th><th>Gerbang</th><th>Keterangan</th><th>Respon</th></tr></thead><tbody><?php foreach($rows as $r): ?><tr><td><?= e(date('d/m/Y H:i:s',strtotime($r['waktu_akses']))) ?></td><td><?= e($r['nama_user']?:'-') ?><br><small><?= e(($r['kode_user']?:'-').' | '.($r['jenis_user']?:'-')) ?></small></td><td><?= e($r['nomor_polisi']?:'-') ?></td><td><b><?= e($r['uid_kartu']) ?></b></td><td><?= e($r['jenis_akses']) ?></td><td><span class="badge <?= $r['status_akses']=='Diterima'?'badge-success':'badge-danger' ?>"><?= e($r['status_akses']) ?></span></td><td><?= e($r['nama_gate']?:'-') ?><br><small><?= e($r['nama_area']?:'-') ?></small></td><td><?= e($r['alasan']) ?></td><td><?= e($r['response_time_ms']) ?> ms</td></tr><?php endforeach; if(!$rows): ?><tr><td colspan="9" class="empty">Riwayat tidak ditemukan.</td></tr><?php endif; ?></tbody></table></div></div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
