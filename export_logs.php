<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_login();
$from=$_GET['from']??date('Y-m-01');
$to=$_GET['to']??date('Y-m-d');
$stmt=$pdo->prepare("SELECT l.waktu_akses,u.kode_user,u.nama_user,u.jenis_user,v.nomor_polisi,l.uid_kartu,l.jenis_akses,l.status_akses,l.alasan,g.nama_gate,a.nama_area,l.response_time_ms
                     FROM access_logs l
                     LEFT JOIN users u ON u.id_user=l.id_user
                     LEFT JOIN vehicles v ON v.id_vehicle=l.id_vehicle
                     LEFT JOIN gates g ON g.id_gate=l.id_gate
                     LEFT JOIN parking_areas a ON a.id_area=l.id_area
                     WHERE DATE(l.waktu_akses) BETWEEN ? AND ?
                     ORDER BY l.waktu_akses DESC");
$stmt->execute([$from,$to]);
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="laporan_akses_parkir_'.$from.'_sd_'.$to.'.csv"');
$out=fopen('php://output','w');
fputcsv($out,['Waktu','Kode Pengguna','Nama Pengguna','Jenis Pengguna','Nomor Polisi','UID Kartu','Jenis Akses','Status Akses','Keterangan','Gerbang','Area','Response Time ms']);
while($row=$stmt->fetch()) fputcsv($out,$row);
fclose($out);
exit;
?>
