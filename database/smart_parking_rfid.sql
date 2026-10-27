-- Smart Parking RFID IoT
-- Import melalui phpMyAdmin pada XAMPP

CREATE DATABASE IF NOT EXISTS smart_parking_rfid CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE smart_parking_rfid;

SET FOREIGN_KEY_CHECKS=0;
DROP TABLE IF EXISTS access_logs;
DROP TABLE IF EXISTS rfid_cards;
DROP TABLE IF EXISTS vehicles;
DROP TABLE IF EXISTS gates;
DROP TABLE IF EXISTS parking_areas;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS admins;
DROP TABLE IF EXISTS system_settings;
SET FOREIGN_KEY_CHECKS=1;

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;

CREATE TABLE IF NOT EXISTS admins (
    id_admin INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nama_admin VARCHAR(100) NOT NULL,
    role ENUM('super_admin','petugas') NOT NULL DEFAULT 'petugas',
    status TINYINT(1) NOT NULL DEFAULT 1,
    last_login DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS users (
    id_user INT AUTO_INCREMENT PRIMARY KEY,
    kode_user VARCHAR(30) NOT NULL UNIQUE,
    nama_user VARCHAR(120) NOT NULL,
    jenis_user ENUM('Mahasiswa','Dosen','Staf','Tamu') NOT NULL,
    prodi_unit VARCHAR(120) NULL,
    no_hp VARCHAR(30) NULL,
    email VARCHAR(120) NULL,
    alamat TEXT NULL,
    status TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS vehicles (
    id_vehicle INT AUTO_INCREMENT PRIMARY KEY,
    id_user INT NOT NULL,
    nomor_polisi VARCHAR(20) NOT NULL,
    jenis_kendaraan ENUM('Motor','Mobil') NOT NULL DEFAULT 'Motor',
    merek VARCHAR(80) NULL,
    warna VARCHAR(60) NULL,
    status TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_vehicles_user FOREIGN KEY (id_user) REFERENCES users(id_user) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS rfid_cards (
    id_rfid INT AUTO_INCREMENT PRIMARY KEY,
    id_user INT NULL,
    uid_kartu VARCHAR(64) NOT NULL UNIQUE,
    label_kartu VARCHAR(100) NULL,
    jenis_kartu ENUM('KTM','e-KTP','Kartu Tamu','Kartu Khusus') NOT NULL DEFAULT 'KTM',
    status_kartu TINYINT(1) NOT NULL DEFAULT 1,
    tanggal_daftar DATE NOT NULL,
    expired_at DATE NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_rfid_user FOREIGN KEY (id_user) REFERENCES users(id_user) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS parking_areas (
    id_area INT AUTO_INCREMENT PRIMARY KEY,
    nama_area VARCHAR(100) NOT NULL,
    kapasitas INT NOT NULL DEFAULT 0,
    lokasi VARCHAR(120) NULL,
    status TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS gates (
    id_gate INT AUTO_INCREMENT PRIMARY KEY,
    kode_gate VARCHAR(30) NOT NULL UNIQUE,
    nama_gate VARCHAR(100) NOT NULL,
    lokasi VARCHAR(120) NULL,
    mode_gate ENUM('Masuk','Keluar','Masuk-Keluar') NOT NULL DEFAULT 'Masuk-Keluar',
    status TINYINT(1) NOT NULL DEFAULT 1,
    last_seen DATETIME NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS access_logs (
    id_akses INT AUTO_INCREMENT PRIMARY KEY,
    id_user INT NULL,
    id_rfid INT NULL,
    id_vehicle INT NULL,
    id_gate INT NULL,
    id_area INT NULL,
    uid_kartu VARCHAR(64) NOT NULL,
    waktu_akses DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    jenis_akses ENUM('Masuk','Keluar') NOT NULL,
    status_akses ENUM('Diterima','Ditolak') NOT NULL,
    alasan VARCHAR(255) NULL,
    response_time_ms INT NULL,
    device_ip VARCHAR(60) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_waktu_akses (waktu_akses),
    INDEX idx_uid (uid_kartu),
    CONSTRAINT fk_logs_user FOREIGN KEY (id_user) REFERENCES users(id_user) ON DELETE SET NULL,
    CONSTRAINT fk_logs_rfid FOREIGN KEY (id_rfid) REFERENCES rfid_cards(id_rfid) ON DELETE SET NULL,
    CONSTRAINT fk_logs_vehicle FOREIGN KEY (id_vehicle) REFERENCES vehicles(id_vehicle) ON DELETE SET NULL,
    CONSTRAINT fk_logs_gate FOREIGN KEY (id_gate) REFERENCES gates(id_gate) ON DELETE SET NULL,
    CONSTRAINT fk_logs_area FOREIGN KEY (id_area) REFERENCES parking_areas(id_area) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS system_settings (
    id_setting INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(80) NOT NULL UNIQUE,
    setting_value TEXT NULL,
    updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO admins (username, password, nama_admin, role, status) VALUES
('admin', '$2y$12$.RoRVXR/aSmTCLYR9gxkJuu/XYeU6Ii3C0EOCqxdaKDdyTTVto1f.', 'Administrator Sistem', 'super_admin', 1)
ON DUPLICATE KEY UPDATE username=username;

INSERT INTO users (kode_user, nama_user, jenis_user, prodi_unit, no_hp, email, status) VALUES
('1122031002', 'Agam As Perila', 'Mahasiswa', 'Informatika', '081234567890', 'agam@kampus.ac.id', 1),
('DSN001', 'Febri', 'Dosen', 'Informatika', '081298765432', 'febri@kampus.ac.id', 1),
('STF001', 'Petugas Parkir', 'Staf', 'Keamanan Kampus', '081211112222', 'parkir@kampus.ac.id', 1),
('TMU001', 'Tamu Terdaftar', 'Tamu', 'Kunjungan', '081233334444', 'tamu@example.com', 1)
ON DUPLICATE KEY UPDATE nama_user=VALUES(nama_user);

INSERT INTO vehicles (id_user, nomor_polisi, jenis_kendaraan, merek, warna, status) VALUES
(1, 'A 1234 UF', 'Motor', 'Honda', 'Hitam', 1),
(2, 'A 5678 DS', 'Mobil', 'Toyota', 'Putih', 1),
(3, 'A 9999 ST', 'Motor', 'Yamaha', 'Merah', 1),
(4, 'A 1111 TM', 'Mobil', 'Daihatsu', 'Silver', 1);

INSERT INTO rfid_cards (id_user, uid_kartu, label_kartu, jenis_kartu, status_kartu, tanggal_daftar) VALUES
(1, '04A1B2C3D4', 'KTM Agam', 'KTM', 1, CURDATE()),
(2, '04D5E6F7A8', 'Kartu Dosen', 'KTM', 1, CURDATE()),
(3, '04ABCDEF12', 'Kartu Petugas', 'Kartu Khusus', 1, CURDATE()),
(4, '04F1F2F3F4', 'Kartu Tamu', 'Kartu Tamu', 1, CURDATE())
ON DUPLICATE KEY UPDATE uid_kartu=uid_kartu;

INSERT INTO parking_areas (nama_area, kapasitas, lokasi, status) VALUES
('Area Parkir Utama', 120, 'Gerbang Utama Kampus', 1),
('Area Parkir Dosen dan Staf', 45, 'Gedung Rektorat', 1)
ON DUPLICATE KEY UPDATE nama_area=VALUES(nama_area);

INSERT INTO gates (kode_gate, nama_gate, lokasi, mode_gate, status) VALUES
('GATE-01', 'Gerbang Utama', 'Pintu Utama', 'Masuk-Keluar', 1),
('GATE-02', 'Gerbang Belakang', 'Pintu Belakang', 'Masuk-Keluar', 1)
ON DUPLICATE KEY UPDATE nama_gate=VALUES(nama_gate);

INSERT INTO system_settings (setting_key, setting_value) VALUES
('campus_name', 'Universitas Faletehan'),
('system_name', 'Smart Parking RFID IoT'),
('default_area_id', '1'),
('default_gate_id', '1'),
('servo_open_delay', '3000'),
('require_vehicle_data', '0')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);

INSERT INTO access_logs (id_user, id_rfid, id_vehicle, id_gate, id_area, uid_kartu, waktu_akses, jenis_akses, status_akses, alasan, response_time_ms, device_ip) VALUES
(1,1,1,1,1,'04A1B2C3D4', DATE_SUB(NOW(), INTERVAL 3 HOUR), 'Masuk','Diterima','Akses valid',120,'192.168.1.10'),
(2,2,2,1,1,'04D5E6F7A8', DATE_SUB(NOW(), INTERVAL 2 HOUR), 'Masuk','Diterima','Akses valid',98,'192.168.1.10'),
(NULL,NULL,NULL,1,1,'0000000000', DATE_SUB(NOW(), INTERVAL 1 HOUR), 'Masuk','Ditolak','Kartu tidak terdaftar',105,'192.168.1.10'),
(1,1,1,1,1,'04A1B2C3D4', DATE_SUB(NOW(), INTERVAL 30 MINUTE), 'Keluar','Diterima','Akses valid',112,'192.168.1.10');

COMMIT;
