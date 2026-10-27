# Smart Parking RFID IoT

Proyek prototipe **sistem parkir cerdas berbasis web dan Internet of Things (IoT)** yang mengintegrasikan RFID, ESP32, API, dan database MySQL. Sistem ini dirancang untuk mendukung validasi akses kendaraan serta pemantauan aktivitas parkir melalui dashboard web.

> **Status proyek:** prototipe untuk pembelajaran, demonstrasi, dan pengembangan. Lakukan pengujian perangkat serta peninjauan keamanan sebelum digunakan di lingkungan operasional.

## Fitur

- Login admin/petugas dan dashboard ringkasan aktivitas.
- Pengelolaan data pengguna, kendaraan, kartu RFID, gerbang, dan area parkir.
- Validasi UID RFID berdasarkan status kartu, pengguna, dan masa berlaku.
- API untuk menerima pemindaian dari ESP32/ESP8266.
- Riwayat akses masuk/keluar dan akses yang ditolak.
- Laporan aktivitas parkir berdasarkan periode.
- Monitoring IoT dan endpoint pemeriksaan API.
- Kontrol servo palang serta indikator LED/buzzer pada sketch mikrokontroler.
- Simulasi perangkat melalui Wokwi.
- Dokumentasi alur sistem, struktur database, dan skenario pengujian Black Box.

## Teknologi

- **Backend:** PHP
- **Database:** MySQL/MariaDB
- **Frontend:** HTML, CSS, JavaScript, Bootstrap
- **IoT:** ESP32/ESP8266, RFID RC522, servo, LED, buzzer
- **Firmware:** Arduino C++
- **Simulasi:** Wokwi
- **Lingkungan pengembangan lokal:** XAMPP atau Laragon

## Arsitektur singkat

1. Kartu RFID ditempelkan ke reader RC522.
2. ESP32 membaca UID kartu dan mengirimkannya ke endpoint API.
3. API PHP memvalidasi UID terhadap data pada MySQL.
4. API memberikan respons akses diterima/ditolak dalam format JSON.
5. Jika akses diterima, firmware menjalankan servo untuk membuka palang dan memberi indikator melalui LED/buzzer.
6. Aktivitas dicatat pada riwayat akses dan ditampilkan di dashboard web.

## Struktur repositori

```text
smart-parking-rfid-iot/
├── api/                 # Endpoint scan, statistik, heartbeat, dan pemeriksaan API
├── arduino/             # Sketch ESP32/ESP8266
├── assets/              # CSS dan JavaScript antarmuka
├── config/              # Konfigurasi koneksi database
├── database/            # SQL schema dan data contoh
├── docs/                # Alur sistem, struktur database, pengujian Black Box
├── includes/            # Komponen dan fungsi PHP bersama
├── wokwi/               # Sketch dan diagram simulasi Wokwi
├── dashboard.php
├── iot_monitor.php
├── login.php
└── README.md
```

## Persyaratan

- PHP 8.x dengan ekstensi PDO MySQL aktif.
- MySQL atau MariaDB.
- XAMPP atau Laragon.
- Browser modern.
- Untuk perangkat fisik: ESP32/ESP8266, MFRC522, servo, LED, buzzer, dan kabel yang sesuai.

## Cara menjalankan aplikasi web secara lokal

1. Unduh atau clone repositori ini ke folder web server lokal, misalnya `C:\xampp\htdocs\smart-parking-rfid-iot` atau folder `www` Laragon.
2. Jalankan Apache dan MySQL dari XAMPP/Laragon.
3. Buka phpMyAdmin, pilih menu **Import**, lalu impor `database/smart_parking_rfid.sql`.
4. Periksa `config/database.php`. Sesuaikan host, nama database, username, dan password dengan konfigurasi lokal.
5. Buka `http://localhost/smart-parking-rfid-iot/` (sesuaikan dengan nama folder yang digunakan).
6. Login menggunakan akun demo di bawah, lalu ganti password setelah masuk jika fitur pengelolaan akun tersedia.

### Akun demo lokal

- **Username:** `admin`
- **Password:** `admin123`

Akun tersebut hanya untuk pengujian lokal. Jangan gunakan kredensial demo di server publik atau produksi.

> **Peringatan database:** file SQL berisi perintah untuk menghapus dan membuat ulang tabel pada database `smart_parking_rfid`. Impor hanya ke database pengujian yang datanya boleh diganti. Cadangkan data terlebih dahulu jika database sudah berisi data penting.

## Menjalankan simulasi Wokwi

1. Buka folder `wokwi/` dan impor `diagram.json`, `sketch.ino`, serta `libraries.txt` ke proyek Wokwi yang sesuai.
2. Jalankan web lokal terlebih dahulu dan pastikan endpoint API dapat diakses.
3. Untuk menghubungkan simulasi Wokwi ke server lokal, ikuti `wokwi/CARA_PAKAI_WOKWI_NGROK.md`.
4. Ganti placeholder `API_URL` di `wokwi/sketch.ino` dengan URL endpoint API milikmu.
5. Jalankan simulasi dan uji UID valid maupun tidak terdaftar. Periksa respons API, dashboard, dan riwayat akses.

Jangan mengunggah URL tunnel aktif, token, password Wi-Fi pribadi, atau kredensial server ke repositori publik.

## Menjalankan firmware di perangkat ESP32

1. Buka `arduino/esp32_rfid_servo_smart_parking.ino` melalui Arduino IDE.
2. Pilih board dan port yang sesuai.
3. Pasang library yang diperlukan: `MFRC522`, `ArduinoJson`, dan `ESP32Servo` untuk ESP32.
4. Isi `WIFI_SSID`, `WIFI_PASSWORD`, dan `API_URL` dengan nilai lokal milikmu. Jangan menggunakan kredensial Wi-Fi pribadi di file yang akan dipublikasikan.
5. Pastikan ESP32 dan server berada pada jaringan yang dapat saling menjangkau. Untuk server lokal, gunakan alamat IP komputer server, bukan `localhost`.
6. Upload sketch, buka Serial Monitor pada baud rate `115200`, lalu uji kartu.

Periksa `arduino/esp8266_pin_note.txt` apabila menyesuaikan proyek untuk ESP8266. Wiring dan dukungan pin harus diverifikasi sesuai board dan modul yang dipakai.

## Endpoint API utama

| Endpoint | Kegunaan |
|---|---|
| `api/scan.php` | Memvalidasi UID dan mencatat akses RFID |
| `api/latest.php` | Mengambil riwayat scan terbaru |
| `api/stats.php` | Mengambil statistik untuk dashboard |
| `api/ping.php` | Memeriksa respons API |
| `api/device-heartbeat.php` | Memperbarui status komunikasi gerbang/perangkat |
| `api/register-uid.php` | Endpoint terkait pendaftaran UID |

Endpoint membutuhkan konfigurasi aplikasi dan database yang sesuai. Lihat kode di folder `api/` untuk parameter dan format respons yang tersedia.

## Dokumentasi

- [Alur Sistem](docs/ALUR_SISTEM.md)
- [Struktur Database](docs/STRUKTUR_DATABASE.md)
- [Panduan Pengujian Black Box](docs/PANDUAN_PENGUJIAN_BLACKBOX.md)
- [Panduan Wokwi + ngrok](wokwi/CARA_PAKAI_WOKWI_NGROK.md)


## Tujuan proyek

Proyek ini dibuat sebagai sarana pembelajaran integrasi aplikasi web, database, API, dan perangkat IoT untuk proses parkir berbasis RFID. Kontribusi, masukan, dan saran perbaikan sangat dihargai.
