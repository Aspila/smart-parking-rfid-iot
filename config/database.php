<?php
// Konfigurasi database untuk XAMPP
$DB_HOST = 'localhost';
$DB_NAME = 'smart_parking_rfid';
$DB_USER = 'root';
$DB_PASS = '';
$DB_CHARSET = 'utf8mb4';

try {
    $dsn = "mysql:host={$DB_HOST};dbname={$DB_NAME};charset={$DB_CHARSET}";
    $pdo = new PDO($dsn, $DB_USER, $DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo '<div style="font-family:Arial;max-width:780px;margin:60px auto;padding:24px;border:1px solid #fecaca;border-radius:16px;background:#fff1f2;color:#7f1d1d">';
    echo '<h2 style="margin-top:0">Koneksi database gagal</h2>';
    echo '<p>Pastikan Apache dan MySQL aktif di XAMPP, lalu import file <b>database/smart_parking_rfid.sql</b>.</p>';
    echo '<p><b>Detail:</b> ' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '</div>';
    exit;
}
?>
