# Cara Pakai Wokwi + Ngrok

## 1. Jalankan web

Buka Laragon, lalu klik `Start All`.

Login ke web, lalu buka menu `Live IoT`.

## 2. Jalankan ngrok

Jika web dibuka melalui domain Laragon `.test`, pakai:

```bash
ngrok http --host-header=nama-folder.test 80
```

Jika web dibuka melalui folder localhost, pakai:

```bash
ngrok http 80
```

## 3. Ambil URL API dari web

Buka menu `Live IoT`, lalu copy bagian **URL API untuk Wokwi atau ESP32**.

Contoh:

```text
https://xxxx.ngrok-free.app/api/scan.php
```

atau:

```text
https://xxxx.ngrok-free.app/smart-parking-rfid-iot-v7-live-monitor/api/scan.php
```

## 4. Masukkan ke sketch Wokwi

Ganti baris ini:

```cpp
String API_URL = "https://ISI_URL_NGROK_KAMU.ngrok-free.app/api/scan.php";
```

menjadi URL API dari menu `Live IoT`.

## 5. Jalankan simulasi

- Tekan tombol hijau untuk UID valid.
- Tekan tombol merah untuk UID tidak terdaftar.
- Atau klik modul RFID, pilih kartu, lalu tap.

Buka web pada menu `Live IoT` atau `Riwayat Akses` untuk melihat log scan.

## Catatan

Sketch sudah menambahkan header berikut agar request dari Wokwi tidak tertahan halaman peringatan ngrok free:

```cpp
http.addHeader("ngrok-skip-browser-warning", "true");
```
