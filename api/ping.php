<?php
require_once __DIR__ . '/_headers.php';
require_once __DIR__ . '/../config/database.php';

try {
    $dbName = $pdo->query('SELECT DATABASE() AS db_name')->fetch()['db_name'] ?? '-';
    $logs = (int)$pdo->query('SELECT COUNT(*) AS total FROM access_logs')->fetch()['total'];
    echo json_encode([
        'success' => true,
        'status' => 'Online',
        'message' => 'API siap menerima request dari Wokwi atau ESP32.',
        'database' => $dbName,
        'total_logs' => $logs,
        'server_time' => date('Y-m-d H:i:s'),
        'api_version' => 'v7-live-monitor'
    ], JSON_PRETTY_PRINT);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'status' => 'Error',
        'message' => $e->getMessage(),
        'server_time' => date('Y-m-d H:i:s'),
        'api_version' => 'v7-live-monitor'
    ], JSON_PRETTY_PRINT);
}
?>
