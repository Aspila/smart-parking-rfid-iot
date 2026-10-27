# Struktur Database

Database utama bernama `smart_parking_rfid`.

## Tabel Utama

1. `admins`
   - Menyimpan akun admin dan petugas.

2. `users`
   - Menyimpan data mahasiswa, dosen, staf, dan tamu.

3. `vehicles`
   - Menyimpan data kendaraan dan nomor polisi.

4. `rfid_cards`
   - Menyimpan UID RFID dan hubungan kartu dengan pengguna.

5. `parking_areas`
   - Menyimpan area parkir dan kapasitas.

6. `gates`
   - Menyimpan data gerbang IoT.

7. `access_logs`
   - Menyimpan semua aktivitas masuk, keluar, diterima, dan ditolak.

8. `system_settings`
   - Menyimpan konfigurasi sistem.

## Relasi Penting

- Satu pengguna dapat memiliki lebih dari satu kendaraan.
- Satu pengguna dapat dihubungkan dengan satu atau lebih kartu RFID.
- Setiap akses parkir menyimpan relasi ke pengguna, kartu, kendaraan, gerbang, dan area.
