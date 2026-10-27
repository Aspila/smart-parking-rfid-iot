<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function redirect($path) {
    header('Location: ' . $path);
    exit;
}

function is_logged_in() {
    return isset($_SESSION['admin']);
}

function require_login() {
    if (!is_logged_in()) {
        redirect('login.php');
    }
}

function flash($type = null, $message = null) {
    if ($type && $message) {
        $_SESSION['flash'] = ['type' => $type, 'message' => $message];
        return;
    }
    if (!empty($_SESSION['flash'])) {
        $data = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $data;
    }
    return null;
}

function setting($pdo, $key, $default = '') {
    $stmt = $pdo->prepare('SELECT setting_value FROM system_settings WHERE setting_key = ? LIMIT 1');
    $stmt->execute([$key]);
    $row = $stmt->fetch();
    return $row ? $row['setting_value'] : $default;
}

function set_setting($pdo, $key, $value) {
    $stmt = $pdo->prepare('INSERT INTO system_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)');
    return $stmt->execute([$key, $value]);
}

function active_menu($file) {
    return basename($_SERVER['PHP_SELF']) === $file ? 'active' : '';
}

function count_table($pdo, $table, $where = '1=1') {
    $stmt = $pdo->query("SELECT COUNT(*) total FROM {$table} WHERE {$where}");
    return (int)$stmt->fetch()['total'];
}

function current_parking_count($pdo) {
    $sql = "SELECT l1.* FROM access_logs l1
            INNER JOIN (
                SELECT uid_kartu, MAX(waktu_akses) AS max_time
                FROM access_logs
                WHERE status_akses='Diterima'
                GROUP BY uid_kartu
            ) lastlog ON lastlog.uid_kartu = l1.uid_kartu AND lastlog.max_time = l1.waktu_akses
            WHERE l1.jenis_akses='Masuk'";
    return (int)$pdo->query("SELECT COUNT(*) total FROM ({$sql}) x")->fetch()['total'];
}

function next_access_type($pdo, $uid) {
    $stmt = $pdo->prepare("SELECT jenis_akses FROM access_logs WHERE uid_kartu = ? AND status_akses='Diterima' ORDER BY waktu_akses DESC, id_akses DESC LIMIT 1");
    $stmt->execute([$uid]);
    $row = $stmt->fetch();
    if (!$row || $row['jenis_akses'] === 'Keluar') return 'Masuk';
    return 'Keluar';
}

function validate_rfid_access($pdo, $uid, $jenis_akses = null, $gate_id = null, $area_id = null, $ip = null) {
    $start = microtime(true);
    $uid = strtoupper(trim($uid));
    $jenis_akses = $jenis_akses ?: next_access_type($pdo, $uid);
    $gate_id = $gate_id ?: (int)setting($pdo, 'default_gate_id', 1);
    $area_id = $area_id ?: (int)setting($pdo, 'default_area_id', 1);

    $stmt = $pdo->prepare("SELECT r.*, u.nama_user, u.kode_user, u.jenis_user, u.status AS status_user
                           FROM rfid_cards r
                           LEFT JOIN users u ON u.id_user = r.id_user
                           WHERE r.uid_kartu = ? LIMIT 1");
    $stmt->execute([$uid]);
    $card = $stmt->fetch();

    $status = 'Ditolak';
    $reason = 'Kartu tidak terdaftar';
    $id_user = null;
    $id_rfid = null;
    $id_vehicle = null;
    $user_name = null;

    if ($card) {
        $id_rfid = $card['id_rfid'];
        $id_user = $card['id_user'];
        $user_name = $card['nama_user'];
        if ((int)$card['status_kartu'] !== 1) {
            $reason = 'Kartu tidak aktif';
        } elseif (!$card['id_user']) {
            $reason = 'Kartu belum terhubung dengan pengguna';
        } elseif ((int)$card['status_user'] !== 1) {
            $reason = 'Pengguna tidak aktif';
        } elseif (!empty($card['expired_at']) && $card['expired_at'] < date('Y-m-d')) {
            $reason = 'Masa berlaku kartu berakhir';
        } else {
            $status = 'Diterima';
            $reason = 'Akses valid';
            $v = $pdo->prepare('SELECT id_vehicle FROM vehicles WHERE id_user = ? AND status = 1 ORDER BY id_vehicle ASC LIMIT 1');
            $v->execute([$id_user]);
            $vehicle = $v->fetch();
            $id_vehicle = $vehicle ? $vehicle['id_vehicle'] : null;
        }
    }

    $response_time = (int)((microtime(true) - $start) * 1000);
    $insert = $pdo->prepare("INSERT INTO access_logs (id_user, id_rfid, id_vehicle, id_gate, id_area, uid_kartu, jenis_akses, status_akses, alasan, response_time_ms, device_ip)
                             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $insert->execute([$id_user, $id_rfid, $id_vehicle, $gate_id, $area_id, $uid, $jenis_akses, $status, $reason, $response_time, $ip]);

    if ($gate_id) {
        $pdo->prepare('UPDATE gates SET last_seen = NOW() WHERE id_gate = ?')->execute([$gate_id]);
    }

    return [
        'success' => $status === 'Diterima',
        'status' => $status,
        'message' => $reason,
        'uid' => $uid,
        'jenis_akses' => $jenis_akses,
        'nama_user' => $user_name,
        'id_log' => $pdo->lastInsertId(),
        'response_time_ms' => $response_time,
        'servo' => $status === 'Diterima' ? 'OPEN' : 'CLOSE',
        'delay_ms' => (int)setting($pdo, 'servo_open_delay', 3000),
    ];
}
?>
