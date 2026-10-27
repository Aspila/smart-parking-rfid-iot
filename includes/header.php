<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';
require_login();
$campusName = setting($pdo, 'campus_name', 'Universitas Faletehan');
$systemName = setting($pdo, 'system_name', 'Smart Parking RFID IoT');
$currentFile = basename($_SERVER['PHP_SELF']);
$currentDate = date('d M Y');
$adminName = $_SESSION['admin']['nama_admin'] ?? 'Admin';
$adminRole = $_SESSION['admin']['role'] ?? 'Administrator';
$adminUsername = $_SESSION['admin']['username'] ?? 'admin';
$adminInitials = strtoupper(substr(trim($adminName), 0, 1));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0f172a">
    <title><?= e(($pageTitle ?? 'Dashboard') . ' - ' . $systemName) ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="app-shell">
    <aside class="sidebar" id="sidebar">
        <div class="brand">
            <div class="brand-icon">SP</div>
            <div>
                <strong><?= e($systemName) ?></strong>
                <span><?= e($campusName) ?></span>
            </div>
        </div>
        <nav class="menu">
            <a class="<?= active_menu('dashboard.php') ?>" href="dashboard.php"><span>▦</span> Dashboard</a>
            <a class="<?= active_menu('users.php') ?>" href="users.php"><span>☷</span> Data Pengguna</a>
            <a class="<?= active_menu('vehicles.php') ?>" href="vehicles.php"><span>▣</span> Data Kendaraan</a>
            <a class="<?= active_menu('rfid_cards.php') ?>" href="rfid_cards.php"><span>◈</span> Data RFID</a>
            <a class="<?= active_menu('scan.php') ?>" href="scan.php"><span>◎</span> Scan RFID</a>
            <a class="<?= active_menu('iot_monitor.php') ?>" href="iot_monitor.php"><span>◌</span> Live IoT</a>
            <a class="<?= active_menu('access_logs.php') ?>" href="access_logs.php"><span>☰</span> Riwayat Akses</a>
            <a class="<?= active_menu('reports.php') ?>" href="reports.php"><span>▤</span> Laporan</a>
            <a class="<?= active_menu('gates.php') ?>" href="gates.php"><span>⌁</span> Gerbang</a>
            <a class="<?= active_menu('admins.php') ?>" href="admins.php"><span>▧</span> Admin</a>
            <a class="<?= active_menu('settings.php') ?>" href="settings.php"><span>⚙</span> Pengaturan</a>
        </nav>
        <div class="sidebar-footer">
            <small>Login sebagai</small>
            <b><?= e($adminName) ?></b>
        </div>
    </aside>
    <div class="mobile-backdrop" id="mobileBackdrop"></div>
    <main class="main">
        <header class="topbar">
            <div class="topbar-left">
                <button class="hamburger" id="menuBtn" type="button" aria-label="Buka menu">☰</button>
                <div class="topbar-title">
                    <span class="topbar-kicker">Panel Admin</span>
                    <h1><?= e($pageTitle ?? 'Dashboard') ?></h1>
                    <p><?= e($pageSubtitle ?? 'Monitoring sistem parkir cerdas berbasis RFID dan IoT') ?></p>
                </div>
            </div>
            <div class="topbar-actions">
                <div class="topbar-date"><span>Hari ini</span><b><?= e($currentDate) ?></b></div>
                <a class="btn btn-light quick-scan" href="scan.php">Scan RFID</a>
                <div class="profile-menu" id="profileMenu">
                    <button class="profile-trigger" id="profileTrigger" type="button" aria-haspopup="true" aria-expanded="false">
                        <span class="profile-avatar"><?= e($adminInitials) ?></span>
                        <span class="profile-text">
                            <b><?= e($adminName) ?></b>
                            <small><?= e($adminRole) ?></small>
                        </span>
                        <span class="profile-chevron">⌄</span>
                    </button>
                    <div class="profile-dropdown" role="menu" aria-labelledby="profileTrigger">
                        <div class="profile-card">
                            <span class="profile-avatar large"><?= e($adminInitials) ?></span>
                            <div>
                                <b><?= e($adminName) ?></b>
                                <small>@<?= e($adminUsername) ?></small>
                            </div>
                        </div>
                        <a href="settings.php" role="menuitem"><span>⚙</span> Pengaturan</a>
                        <a href="admins.php" role="menuitem"><span>▧</span> Kelola Admin</a>
                        <div class="dropdown-divider"></div>
                        <a class="logout-link" href="logout.php" role="menuitem"><span>↳</span> Logout</a>
                    </div>
                </div>
            </div>
        </header>
        <?php if ($f = flash()): ?>
            <div class="alert alert-<?= e($f['type']) ?>"><?= e($f['message']) ?></div>
        <?php endif; ?>
