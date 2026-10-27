# Alur Sistem Smart Parking RFID IoT

## Alur Masuk Kendaraan

1. Pengguna menempelkan KTM, e-KTP, atau kartu RFID pada RFID reader.
2. RFID reader membaca UID kartu.
3. ESP32 atau ESP8266 mengirim UID ke API sistem.
4. Server mencocokkan UID dengan database MySQL.
5. Jika UID terdaftar, aktif, dan terhubung dengan pengguna aktif, akses diterima.
6. API mengirim instruksi `OPEN` ke mikrokontroler.
7. Servo membuka palang parkir.
8. Sistem menyimpan riwayat akses masuk.
9. Website menampilkan log akses pada dashboard dan halaman riwayat.

## Alur Keluar Kendaraan

1. Pengguna menempelkan kartu RFID pada reader.
2. Sistem membaca UID dan melihat log terakhir.
3. Jika log terakhir adalah masuk, sistem menentukan akses berikutnya sebagai keluar.
4. Jika data valid, akses diterima.
5. Servo membuka palang.
6. Sistem menyimpan riwayat akses keluar.

## Alur Akses Ditolak

1. UID tidak ditemukan, kartu nonaktif, pengguna nonaktif, atau masa berlaku habis.
2. Sistem menyimpan log dengan status `Ditolak`.
3. API mengirim instruksi `CLOSE`.
4. Palang tetap tertutup.
