<?php
require_once __DIR__ . '/_headers.php';
require_once __DIR__ . '/../config/database.php';
$limit = min(50, max(1, (int)($_GET['limit'] ?? 10)));
$stmt = $pdo->prepare("SELECT l.id_akses,l.uid_kartu,l.waktu_akses,l.jenis_akses,l.status_akses,l.alasan,u.nama_user,u.kode_user,v.nomor_polisi,g.nama_gate
                       FROM access_logs l
                       LEFT JOIN users u ON u.id_user=l.id_user
                       LEFT JOIN vehicles v ON v.id_vehicle=l.id_vehicle
                       LEFT JOIN gates g ON g.id_gate=l.id_gate
                       ORDER BY l.id_akses DESC LIMIT ?");
$stmt->bindValue(1,$limit,PDO::PARAM_INT);
$stmt->execute();
echo json_encode(['success'=>true,'data'=>$stmt->fetchAll()], JSON_PRETTY_PRINT);
?>
