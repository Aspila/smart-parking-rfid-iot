<?php
$pageTitle='Admin';
$pageSubtitle='Kelola akun admin dan petugas parkir yang dapat masuk ke sistem.';
require_once __DIR__ . '/includes/header.php';
$action=$_POST['action'] ?? $_GET['action'] ?? '';
if($_SERVER['REQUEST_METHOD']==='POST'){
    $username=trim($_POST['username']??'');
    $nama=trim($_POST['nama_admin']??'');
    $role=$_POST['role']??'petugas';
    $status=(int)($_POST['status']??1);
    $password=$_POST['password']??'';
    try{
        if($action==='create'){
            if($password===''){ throw new Exception('Password wajib diisi.'); }
            $hash=password_hash($password,PASSWORD_DEFAULT);
            $pdo->prepare('INSERT INTO admins (username,password,nama_admin,role,status) VALUES (?,?,?,?,?)')->execute([$username,$hash,$nama,$role,$status]);
            flash('success','Akun admin berhasil ditambahkan.');
        }
        if($action==='update'){
            $id=(int)$_POST['id_admin'];
            if($password!==''){
                $hash=password_hash($password,PASSWORD_DEFAULT);
                $pdo->prepare('UPDATE admins SET username=?, password=?, nama_admin=?, role=?, status=? WHERE id_admin=?')->execute([$username,$hash,$nama,$role,$status,$id]);
            }else{
                $pdo->prepare('UPDATE admins SET username=?, nama_admin=?, role=?, status=? WHERE id_admin=?')->execute([$username,$nama,$role,$status,$id]);
            }
            flash('success','Akun admin berhasil diperbarui.');
        }
    }catch(Exception $e){ flash('danger','Gagal menyimpan akun. Pastikan username tidak sama dan data lengkap.'); }
    redirect('admins.php');
}
if($action==='delete'){
    $id=(int)$_GET['id'];
    if($id === (int)($_SESSION['admin']['id_admin']??0)){ flash('danger','Akun yang sedang digunakan tidak dapat dihapus.'); }
    else{ $pdo->prepare('DELETE FROM admins WHERE id_admin=?')->execute([$id]); flash('success','Akun admin berhasil dihapus.'); }
    redirect('admins.php');
}
$edit=null;if(isset($_GET['edit'])){$s=$pdo->prepare('SELECT * FROM admins WHERE id_admin=?');$s->execute([(int)$_GET['edit']]);$edit=$s->fetch();}
$rows=$pdo->query('SELECT * FROM admins ORDER BY id_admin DESC')->fetchAll();
?>
<div class="grid grid-2">
<div class="card"><div class="card-title"><h3><?= $edit?'Edit Admin':'Tambah Admin' ?></h3></div><form method="post"><input type="hidden" name="action" value="<?= $edit?'update':'create' ?>"><?php if($edit): ?><input type="hidden" name="id_admin" value="<?= (int)$edit['id_admin'] ?>"><?php endif; ?><div class="form-grid"><div class="form-group"><label>Username</label><input class="input" name="username" value="<?= e($edit['username']??'') ?>" required></div><div class="form-group"><label>Nama Admin</label><input class="input" name="nama_admin" value="<?= e($edit['nama_admin']??'') ?>" required></div><div class="form-group"><label>Password <?= $edit?'Baru':'' ?></label><input class="input" type="password" name="password" <?= $edit?'':'required' ?> placeholder="<?= $edit?'Kosongkan jika tidak diganti':'Minimal 6 karakter' ?>"></div><div class="form-group"><label>Role</label><select class="select" name="role"><option value="super_admin" <?= ($edit['role']??'')=='super_admin'?'selected':'' ?>>Super Admin</option><option value="petugas" <?= ($edit['role']??'petugas')=='petugas'?'selected':'' ?>>Petugas</option></select></div><div class="form-group"><label>Status</label><select class="select" name="status"><option value="1" <?= ($edit['status']??1)==1?'selected':'' ?>>Aktif</option><option value="0" <?= ($edit['status']??1)==0?'selected':'' ?>>Nonaktif</option></select></div></div><div class="form-actions"><button class="btn btn-primary">Simpan</button><?php if($edit): ?><a class="btn btn-light" href="admins.php">Batal Edit</a><?php endif; ?></div></form></div>
<div class="card"><div class="card-title"><h3>Hak Akses</h3></div><p>Akun admin digunakan untuk mengelola sistem melalui website. Role <b>Super Admin</b> dapat digunakan untuk pengelola utama, sedangkan role <b>Petugas</b> untuk operator parkir.</p><div class="alert alert-warning">Ganti password bawaan setelah sistem dipasang di perangkat sebenarnya.</div></div>
</div>
<div class="card" style="margin-top:18px"><div class="card-title"><h3>Daftar Admin</h3></div><div class="table-wrap"><table><thead><tr><th>Username</th><th>Nama</th><th>Role</th><th>Login Terakhir</th><th>Status</th><th>Aksi</th></tr></thead><tbody><?php foreach($rows as $r): ?><tr><td><b><?= e($r['username']) ?></b></td><td><?= e($r['nama_admin']) ?></td><td><?= e($r['role']) ?></td><td><?= e($r['last_login']?:'-') ?></td><td><span class="badge <?= $r['status']?'badge-success':'badge-danger' ?>"><?= $r['status']?'Aktif':'Nonaktif' ?></span></td><td><a class="btn btn-warning btn-sm" href="admins.php?edit=<?= $r['id_admin'] ?>">Edit</a> <a class="btn btn-danger btn-sm" data-confirm="Hapus akun admin ini?" href="admins.php?action=delete&id=<?= $r['id_admin'] ?>">Hapus</a></td></tr><?php endforeach; ?></tbody></table></div></div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
