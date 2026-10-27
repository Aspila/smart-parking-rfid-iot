<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
if (is_logged_in()) redirect('dashboard.php');
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $stmt = $pdo->prepare('SELECT * FROM admins WHERE username = ? AND status = 1 LIMIT 1');
    $stmt->execute([$username]);
    $admin = $stmt->fetch();
    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['admin'] = [
            'id_admin' => $admin['id_admin'],
            'username' => $admin['username'],
            'nama_admin' => $admin['nama_admin'],
            'role' => $admin['role'],
        ];
        $pdo->prepare('UPDATE admins SET last_login = NOW() WHERE id_admin = ?')->execute([$admin['id_admin']]);
        redirect('dashboard.php');
    } else {
        $error = 'Username atau password tidak sesuai.';
    }
}
$campusName = setting($pdo, 'campus_name', 'Universitas Faletehan');
$systemName = setting($pdo, 'system_name', 'Smart Parking RFID IoT');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0f172a">
    <title>Login - <?= e($systemName) ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="login-page">
    <div class="login-shell">
        <section class="login-visual">
            <div class="login-visual-content">
                <div>
                    <div class="login-logo">SP</div>
                    <h1><?= e($systemName) ?></h1>
                    <p><?= e($campusName) ?>. Kelola akses masuk dan keluar kendaraan dengan kartu RFID, log real time, dan laporan operasional.</p>
                </div>
                <div class="login-features">
                    <div class="login-feature"><span>◈</span> Validasi kartu RFID</div>
                    <div class="login-feature"><span>▣</span> Data kendaraan terpusat</div>
                    <div class="login-feature"><span>▤</span> Laporan akses parkir</div>
                </div>
            </div>
        </section>
        <section class="login-card">
            <h2>Masuk Dashboard</h2>
            <p>Gunakan akun admin untuk membuka panel pengelolaan sistem parkir.</p>
            <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
            <form method="post">
                <div class="form-group">
                    <label>Username</label>
                    <input class="input" type="text" name="username" value="admin" required autofocus>
                </div>
                <div class="form-group" style="margin-top:14px">
                    <label>Password</label>
                    <input class="input" type="password" name="password" value="admin123" required>
                </div>
                <button class="btn btn-primary btn-block" style="margin-top:20px" type="submit">Masuk Dashboard</button>
            </form>
            <div class="login-note">Akun awal: <b>admin</b> / <b>admin123</b>. Ganti password setelah sistem dipasang.</div>
        </section>
    </div>
</body>
</html>
