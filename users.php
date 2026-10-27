<?php
$pageTitle='Data Pengguna';
$pageSubtitle='Kelola mahasiswa, dosen, staf, dan tamu yang memiliki akses parkir.';
require_once __DIR__ . '/includes/header.php';
$action=$_POST['action'] ?? $_GET['action'] ?? '';
if($_SERVER['REQUEST_METHOD']==='POST'){
    $kode=trim($_POST['kode_user']??'');
    $nama=trim($_POST['nama_user']??'');
    $jenis=$_POST['jenis_user']??'Mahasiswa';
    $unit=trim($_POST['prodi_unit']??'');
    $hp=trim($_POST['no_hp']??'');
    $email=trim($_POST['email']??'');
    $alamat=trim($_POST['alamat']??'');
    $status=(int)($_POST['status']??1);
    try{
        if($action==='create'){
            $stmt=$pdo->prepare('INSERT INTO users (kode_user,nama_user,jenis_user,prodi_unit,no_hp,email,alamat,status) VALUES (?,?,?,?,?,?,?,?)');
            $stmt->execute([$kode,$nama,$jenis,$unit,$hp,$email,$alamat,$status]);
            flash('success','Data pengguna berhasil ditambahkan.');
        }elseif($action==='update'){
            $id=(int)$_POST['id_user'];
            $stmt=$pdo->prepare('UPDATE users SET kode_user=?, nama_user=?, jenis_user=?, prodi_unit=?, no_hp=?, email=?, alamat=?, status=? WHERE id_user=?');
            $stmt->execute([$kode,$nama,$jenis,$unit,$hp,$email,$alamat,$status,$id]);
            flash('success','Data pengguna berhasil diperbarui.');
        }
    }catch(Exception $e){ flash('danger','Gagal menyimpan data. Pastikan kode pengguna tidak sama.'); }
    redirect('users.php');
}
if($action==='delete'){
    try{$pdo->prepare('DELETE FROM users WHERE id_user=?')->execute([(int)$_GET['id']]); flash('success','Data pengguna berhasil dihapus.');}
    catch(Exception $e){flash('danger','Data tidak dapat dihapus karena masih digunakan pada tabel lain.');}
    redirect('users.php');
}
$q=trim($_GET['q']??'');
$edit=null;
if(isset($_GET['edit'])){ $s=$pdo->prepare('SELECT * FROM users WHERE id_user=?');$s->execute([(int)$_GET['edit']]);$edit=$s->fetch(); }
$sql='SELECT * FROM users WHERE nama_user LIKE ? OR kode_user LIKE ? OR jenis_user LIKE ? ORDER BY id_user DESC';
$stmt=$pdo->prepare($sql);$like="%$q%";$stmt->execute([$like,$like,$like]);$rows=$stmt->fetchAll();
?>
<div class="grid grid-2">
    <div class="card">
        <div class="card-title"><h3><?= $edit?'Edit Pengguna':'Tambah Pengguna' ?></h3></div>
        <form method="post">
            <input type="hidden" name="action" value="<?= $edit?'update':'create' ?>">
            <?php if($edit): ?><input type="hidden" name="id_user" value="<?= (int)$edit['id_user'] ?>"><?php endif; ?>
            <div class="form-grid">
                <div class="form-group"><label>Kode/NIM/NIP</label><input class="input" name="kode_user" value="<?= e($edit['kode_user']??'') ?>" required></div>
                <div class="form-group"><label>Nama Pengguna</label><input class="input" name="nama_user" value="<?= e($edit['nama_user']??'') ?>" required></div>
                <div class="form-group"><label>Jenis Pengguna</label><select class="select" name="jenis_user"><?php foreach(['Mahasiswa','Dosen','Staf','Tamu'] as $j): ?><option <?= ($edit['jenis_user']??'')==$j?'selected':'' ?>><?= $j ?></option><?php endforeach; ?></select></div>
                <div class="form-group"><label>Prodi/Unit</label><input class="input" name="prodi_unit" value="<?= e($edit['prodi_unit']??'') ?>"></div>
                <div class="form-group"><label>No. HP</label><input class="input" name="no_hp" value="<?= e($edit['no_hp']??'') ?>"></div>
                <div class="form-group"><label>Email</label><input class="input" type="email" name="email" value="<?= e($edit['email']??'') ?>"></div>
                <div class="form-group"><label>Status</label><select class="select" name="status"><option value="1" <?= ($edit['status']??1)==1?'selected':'' ?>>Aktif</option><option value="0" <?= ($edit['status']??1)==0?'selected':'' ?>>Nonaktif</option></select></div>
            </div>
            <div class="form-group" style="margin-top:14px"><label>Alamat</label><textarea name="alamat"><?= e($edit['alamat']??'') ?></textarea></div>
            <div class="form-actions"><button class="btn btn-primary" type="submit">Simpan</button><?php if($edit): ?><a class="btn btn-light" href="users.php">Batal Edit</a><?php endif; ?></div>
        </form>
    </div>
    <div class="card">
        <div class="card-title"><h3>Catatan Penggunaan</h3></div>
        <p>Data pengguna menjadi dasar validasi kartu RFID. Kartu aktif tetap ditolak jika pengguna dinonaktifkan.</p>
        <div class="alert alert-info">Untuk pengguna tamu, isi jenis pengguna sebagai <b>Tamu</b> dan atur masa berlaku kartu pada menu Data RFID.</div>
    </div>
</div>
<div class="card" style="margin-top:18px">
    <div class="toolbar"><h3 style="margin:0">Daftar Pengguna</h3><form class="search-form"><input class="input" name="q" placeholder="Cari nama, kode, jenis" value="<?= e($q) ?>"><button class="btn btn-light">Cari</button></form></div>
    <div class="table-wrap"><table><thead><tr><th>Kode</th><th>Nama</th><th>Jenis</th><th>Unit</th><th>Kontak</th><th>Status</th><th>Aksi</th></tr></thead><tbody>
        <?php foreach($rows as $r): ?><tr><td><b><?= e($r['kode_user']) ?></b></td><td><?= e($r['nama_user']) ?></td><td><?= e($r['jenis_user']) ?></td><td><?= e($r['prodi_unit']) ?></td><td><?= e($r['no_hp']) ?><br><small><?= e($r['email']) ?></small></td><td><span class="badge <?= $r['status']?'badge-success':'badge-danger' ?>"><?= $r['status']?'Aktif':'Nonaktif' ?></span></td><td><a class="btn btn-warning btn-sm" href="users.php?edit=<?= $r['id_user'] ?>">Edit</a> <a class="btn btn-danger btn-sm" data-confirm="Hapus data pengguna ini?" href="users.php?action=delete&id=<?= $r['id_user'] ?>">Hapus</a></td></tr><?php endforeach; if(!$rows): ?><tr><td colspan="7" class="empty">Data tidak ditemukan.</td></tr><?php endif; ?>
    </tbody></table></div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
