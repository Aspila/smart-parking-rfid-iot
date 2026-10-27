<?php
require_once __DIR__ . '/_headers.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
$data = [
    'users' => count_table($pdo,'users'),
    'cards' => count_table($pdo,'rfid_cards'),
    'vehicles' => count_table($pdo,'vehicles'),
    'logs' => count_table($pdo,'access_logs'),
    'accepted_today' => count_table($pdo,'access_logs',"DATE(waktu_akses)=CURDATE() AND status_akses='Diterima'"),
    'rejected_today' => count_table($pdo,'access_logs',"DATE(waktu_akses)=CURDATE() AND status_akses='Ditolak'"),
    'current_parking' => current_parking_count($pdo),
];
echo json_encode(['success'=>true,'data'=>$data], JSON_PRETTY_PRINT);
?>
