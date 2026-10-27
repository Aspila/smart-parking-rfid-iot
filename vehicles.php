<?php
$pageTitle='Data Kendaraan';
$pageSubtitle='Kelola nomor polisi dan identitas kendaraan yang terhubung dengan pengguna.';
require_once __DIR__ . '/includes/header.php';
$action=$_POST['action'] ?? $_GET['action'] ?? '';
if($_SERVER['REQUEST_METHOD']==='POST'){
    $id_user=(int)($_POST['id_user']??0);$nopol=strtoupper(trim($_POST['nomor_polisi']??''));$jenis=$_POST['jenis_kendaraan']??'Motor';$merek=trim($_POST['merek']??'');$warna=trim($_POST['warna']??'');$status=(int)($_POST['status']??1);
    if($action==='create'){$pdo->prepare('INSERT INTO vehicles (id_user,nomor_polisi,jenis_kendaraan,merek,warna,status) VALUES (?,?,?,?,?,?)')->execute([$id_user,$nopol,$jenis,$merek,$warna,$status]);flash('success','Data kendaraan berhasil ditambahkan.');}
    if($action==='update'){$pdo->prepare('UPDATE vehicles SET id_user=?, nomor_polisi=?, jenis_kendaraan=?, merek=?, warna=?, status=? WHERE id_vehicle=?')->execute([$id_user,$nopol,$jenis,$merek,$warna,$status,(int)$_POST['id_vehicle']]);flash('success','Data kendaraan berhasil diperbarui.');}
    redirect('vehicles.php');
}
if($action==='delete'){$pdo->prepare('DELETE FROM vehicles WHERE id_vehicle=?')->execute([(int)$_GET['id']]);flash('success','Data kendaraan berhasil dihapus.');redirect('vehicles.php');}
$users=$pdo->query('SELECT id_user,kode_user,nama_user FROM users WHERE status=1 ORDER BY nama_user')->fetchAll();
$edit=null;if(isset($_GET['edit'])){$s=$pdo->prepare('SELECT * FROM vehicles WHERE id_vehicle=?');$s->execute([(int)$_GET['edit']]);$edit=$s->fetch();}
$q=trim($_GET['q']??'');$like="%$q%";
$stmt=$pdo->prepare('SELECT v.*,u.nama_user,u.kode_user FROM vehicles v JOIN users u ON u.id_user=v.id_user WHERE v.nomor_polisi LIKE ? OR u.nama_user LIKE ? OR u.kode_user LIKE ? ORDER BY v.id_vehicle DESC');$stmt->execute([$like,$like,$like]);$rows=$stmt->fetchAll();
?>
<div class="card">
    <div class="card-title"><h3><?= $edit?'Edit Kendaraan':'Tambah Kendaraan' ?></h3></div>
    <form method="post">
        <input type="hidden" name="action" value="<?= $edit?'update':'create' ?>"><?php if($edit): ?><input type="hidden" name="id_vehicle" value="<?= $edit['id_vehicle'] ?>"><?php endif; ?>
        <div class="form-grid">
            <div class="form-group"><label>Pemilik</label><select class="select" name="id_user" required><option value="">Pilih pengguna</option><?php foreach($users as $u): ?><option value="<?= $u['id_user'] ?>" <?= ($edit['id_user']??'')==$u['id_user']?'selected':'' ?>><?= e($u['kode_user'].' - '.$u['nama_user']) ?></option><?php endforeach; ?></select></div>
            <div class="form-group"><label>Nomor Polisi</label><input class="input" name="nomor_polisi" value="<?= e($edit['nomor_polisi']??'') ?>" required></div>
            <div class="form-group"><label>Jenis Kendaraan</label><select class="select" name="jenis_kendaraan"><option <?= ($edit['jenis_kendaraan']??'')=='Motor'?'selected':'' ?>>Motor</option><option <?= ($edit['jenis_kendaraan']??'')=='Mobil'?'selected':'' ?>>Mobil</option></select></div>
            <div class="form-group"><label>Merek</label><input class="input" name="merek" value="<?= e($edit['merek']??'') ?>"></div>
            <div class="form-group"><label>Warna</label><input class="input" name="warna" value="<?= e($edit['warna']??'') ?>"></div>
            <div class="form-group"><label>Status</label><select class="select" name="status"><option value="1" <?= ($edit['status']??1)==1?'selected':'' ?>>Aktif</option><option value="0" <?= ($edit['status']??1)==0?'selected':'' ?>>Nonaktif</option></select></div>
        </div><div class="form-actions"><button class="btn btn-primary">Simpan</button><?php if($edit): ?><a class="btn btn-light" href="vehicles.php">Batal Edit</a><?php endif; ?></div>
    </form>
</div>
<div class="card" style="margin-top:18px"><div class="toolbar"><h3 style="margin:0">Daftar Kendaraan</h3><form class="search-form"><input class="input" name="q" value="<?= e($q) ?>" placeholder="Cari nopol/pemilik"><button class="btn btn-light">Cari</button></form></div><div class="table-wrap"><table><thead><tr><th>Nopol</th><th>Pemilik</th><th>Jenis</th><th>Merek</th><th>Warna</th><th>Status</th><th>Aksi</th></tr></thead><tbody><?php foreach($rows as $r): ?><tr><td><b><?= e($r['nomor_polisi']) ?></b></td><td><?= e($r['nama_user']) ?><br><small><?= e($r['kode_user']) ?></small></td><td><?= e($r['jenis_kendaraan']) ?></td><td><?= e($r['merek']) ?></td><td><?= e($r['warna']) ?></td><td><span class="badge <?= $r['status']?'badge-success':'badge-danger' ?>"><?= $r['status']?'Aktif':'Nonaktif' ?></span></td><td><a class="btn btn-warning btn-sm" href="vehicles.php?edit=<?= $r['id_vehicle'] ?>">Edit</a> <a class="btn btn-danger btn-sm" data-confirm="Hapus data kendaraan ini?" href="vehicles.php?action=delete&id=<?= $r['id_vehicle'] ?>">Hapus</a></td></tr><?php endforeach; if(!$rows): ?><tr><td colspan="7" class="empty">Data kendaraan belum tersedia.</td></tr><?php endif; ?></tbody></table></div></div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
