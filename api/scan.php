<?php
require_once __DIR__ . '/_headers.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

try {
    $input = [];
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $raw = file_get_contents('php://input');
        $json = json_decode($raw, true);
        if (is_array($json)) {
            $input = $json;
        }
    }

    $uid = $_GET['uid'] ?? $_POST['uid'] ?? $input['uid'] ?? $input['card_uid'] ?? '';
    $uid = strtoupper(str_replace([' ', ':', '-'], '', trim((string)$uid)));

    $jenis = $_GET['jenis_akses'] ?? $_POST['jenis_akses'] ?? $input['jenis_akses'] ?? null;
    $gateId = $_GET['gate_id'] ?? $_POST['gate_id'] ?? $input['gate_id'] ?? null;
    $areaId = $_GET['area_id'] ?? $_POST['area_id'] ?? $input['area_id'] ?? null;
    $gateId = $gateId !== null && $gateId !== '' ? (int)$gateId : null;
    $areaId = $areaId !== null && $areaId !== '' ? (int)$areaId : null;

    if ($uid === '') {
        echo json_encode([
            'success' => false,
            'status' => 'Ditolak',
            'message' => 'UID wajib dikirim',
            'servo' => 'CLOSE',
            'server_time' => date('Y-m-d H:i:s'),
            'api_version' => 'v7-live-monitor'
        ], JSON_PRETTY_PRINT);
        exit;
    }

    $result = validate_rfid_access($pdo, $uid, $jenis, $gateId, $areaId, $_SERVER['REMOTE_ADDR'] ?? null);
    $result['logged'] = true;
    $result['server_time'] = date('Y-m-d H:i:s');
    $result['api_version'] = 'v7-live-monitor';

    echo json_encode($result, JSON_PRETTY_PRINT);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'status' => 'Error',
        'message' => $e->getMessage(),
        'servo' => 'CLOSE',
        'server_time' => date('Y-m-d H:i:s'),
        'api_version' => 'v7-live-monitor'
    ], JSON_PRETTY_PRINT);
}
?>
