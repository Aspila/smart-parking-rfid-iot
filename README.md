# Smart Parking RFID IoT

Sistem ini dibuat untuk proposal **Implementasi Teknologi RFID pada Sistem Parkir Cerdas Berbasis Internet of Things (IoT) di Lingkungan Kampus**.

## Fitur Utama

1. Login admin dan petugas.
2. Dashboard monitoring dengan ringkasan pengguna, kartu RFID, kendaraan, log akses, kapasitas, dan aktivitas harian.
3. Kelola data pengguna: mahasiswa, dosen, staf, dan tamu.
4. Kelola data kendaraan dan nomor polisi.
5. Kelola kartu RFID: KTM, e-KTP, kartu tamu, dan kartu khusus.
6. Validasi UID kartu terdaftar, aktif, terhubung dengan pengguna, dan masa berlaku.
7. Simulasi scan RFID dari website.
8. API scan RFID untuk ESP32 atau ESP8266.
9. Riwayat akses masuk dan keluar.
10. Laporan aktivitas parkir berdasarkan periode.
11. Pengaturan gerbang, area parkir, kapasitas, dan durasi servo.
12. Kode Arduino untuk RFID RC522 dan servo palang otomatis.

## Kebutuhan

- XAMPP dengan PHP 8.x dan MySQL atau MariaDB.
- Browser modern.
- ESP32 atau ESP8266.
- RFID RC522.
- Motor servo.
- Kabel jumper dan sumber daya 5V.

## Instalasi di XAMPP

1. Ekstrak folder ini ke:

   `C:\xampp\htdocs\smart-parking-rfid-iot-v2`

2. Jalankan **Apache** dan **MySQL** dari XAMPP Control Panel.

3. Buka phpMyAdmin.

4. Import file:

   `database/smart_parking_rfid.sql`

5. Buka sistem di browser:

   `http://localhost/smart-parking-rfid-iot-v2/`

## Akun Awal

- Username: `admin`
- Password: `admin123`

## UID Kartu Awal untuk Uji Coba

- `04A1B2C3D4`
- `04D5E6F7A8`
- `04ABCDEF12`
- `04F1F2F3F4`

UID lain akan ditolak oleh sistem.

## Endpoint API

### 1. Scan RFID

`GET /api/scan.php?uid=04A1B2C3D4&gate_id=1&area_id=1`

Contoh respons diterima:

```json
{
  "success": true,
  "status": "Diterima",
  "message": "Akses valid",
  "uid": "04A1B2C3D4",
  "jenis_akses": "Masuk",
  "nama_user": "Agam As Perila",
  "servo": "OPEN",
  "delay_ms": 3000
}
```

Contoh respons ditolak:

```json
{
  "success": false,
  "status": "Ditolak",
  "message": "Kartu tidak terdaftar",
  "servo": "CLOSE"
}
```

### 2. Riwayat Terbaru

`GET /api/latest.php?limit=10`

### 3. Statistik Dashboard

`GET /api/stats.php`

### 4. Heartbeat Gerbang

`GET /api/device-heartbeat.php?gate_id=1`

## Menghubungkan ESP32 atau ESP8266

1. Buka file:

   `arduino/esp32_rfid_servo_smart_parking.ino`

2. Ubah SSID dan password WiFi.

3. Ubah alamat API sesuai IP komputer XAMPP.

   Contoh:

   `http://192.168.1.5/smart-parking-rfid-iot-v2/api/scan.php`

4. Upload kode ke board.

5. Tempelkan kartu RFID ke reader.

6. Jika UID valid, API mengirim instruksi `OPEN`, servo membuka palang, lalu sistem mencatat log akses.

## Struktur Folder

```text
smart-parking-rfid-iot-v2/
├── api/
├── arduino/
├── assets/
├── config/
├── database/
├── includes/
├── dashboard.php
├── users.php
├── vehicles.php
├── rfid_cards.php
├── scan.php
├── access_logs.php
├── reports.php
├── gates.php
├── settings.php
└── login.php
```

## Catatan Penting

- File `config/database.php` memakai konfigurasi standar XAMPP: user `root`, password kosong.
- Jika nama database berbeda, ubah nilai `$DB_NAME` pada file tersebut.
- Untuk akses dari ESP32 atau ESP8266, gunakan IP komputer, bukan `localhost`.

## Update v7 Live IoT Monitor

Versi ini menambahkan halaman `iot_monitor.php` untuk membantu pengujian Wokwi atau ESP32 melalui ngrok.

Fitur tambahan:

1. Menampilkan URL API yang harus dimasukkan ke `API_URL` pada sketch Wokwi.
2. Tombol cek API melalui `api/ping.php`.
3. Tombol test UID valid dan UID salah langsung dari web.
4. Live riwayat scan yang otomatis refresh setiap 2,5 detik.
5. API `scan.php` menerima GET, POST form, dan POST JSON.
6. API menambahkan header CORS dan header no-cache.

Urutan uji cepat:

1. Jalankan Laragon.
2. Buka web dan login.
3. Buka menu `Live IoT`.
4. Klik `Cek API`.
5. Klik `Test UID Valid`.
6. Cek apakah riwayat scan bertambah.
7. Salin URL API ke `API_URL` pada sketch Wokwi.
8. Jalankan simulasi Wokwi.
9. Tempel kartu atau tekan tombol UID valid.
10. Lihat halaman `Live IoT` atau `Riwayat Akses`.

Jika memakai domain Laragon `.test`, contoh perintah ngrok:

```bash
ngrok http --host-header=smart-parking-rfid-iot-v7-live-monitor.test 80
```

Jika memakai folder localhost, contoh perintah ngrok:

```bash
ngrok http 80
```
