<?php
require_once __DIR__ . '/_headers.php';
require_once __DIR__ . '/../config/database.php';
$gateId = (int)($_GET['gate_id'] ?? $_POST['gate_id'] ?? 0);
if ($gateId <= 0) {
    echo json_encode(['success'=>false,'message'=>'gate_id wajib dikirim']);
    exit;
}
$stmt = $pdo->prepare('UPDATE gates SET last_seen = NOW() WHERE id_gate = ?');
$stmt->execute([$gateId]);
echo json_encode(['success'=>true,'message'=>'Heartbeat diterima','gate_id'=>$gateId]);
?>
