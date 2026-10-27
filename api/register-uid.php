<?php
require_once __DIR__ . '/_headers.php';
require_once __DIR__ . '/../config/database.php';
$uid = strtoupper(trim($_GET['uid'] ?? $_POST['uid'] ?? ''));
$label = trim($_GET['label'] ?? $_POST['label'] ?? 'Kartu Baru');
if ($uid === '') {
    echo json_encode(['success'=>false,'message'=>'UID wajib dikirim']);
    exit;
}
try {
    $stmt = $pdo->prepare("INSERT INTO rfid_cards (uid_kartu,label_kartu,jenis_kartu,status_kartu,tanggal_daftar) VALUES (?,?, 'KTM', 0, CURDATE())");
    $stmt->execute([$uid,$label]);
    echo json_encode(['success'=>true,'message'=>'UID tersimpan sebagai kartu nonaktif','uid'=>$uid]);
} catch (Throwable $e) {
    echo json_encode(['success'=>false,'message'=>'UID sudah tersedia atau data tidak valid']);
}
?>
