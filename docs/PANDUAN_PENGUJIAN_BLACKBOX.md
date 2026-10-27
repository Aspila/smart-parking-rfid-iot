# Panduan Pengujian Black Box

Pengujian ini disusun untuk memastikan fungsi utama sistem parkir RFID IoT berjalan sesuai kebutuhan.

| No | Fitur | Skenario Uji | Data Uji | Hasil yang Diharapkan |
|---|---|---|---|---|
| 1 | Login | Admin memasukkan username dan password benar | admin / admin123 | Sistem masuk ke dashboard |
| 2 | Login | Admin memasukkan password salah | admin / salah | Sistem menolak login |
| 3 | Data Pengguna | Admin menambah pengguna baru | Nama, kode, jenis pengguna | Data tersimpan dan tampil pada tabel |
| 4 | Data Kendaraan | Admin menambah kendaraan pengguna | Nomor polisi, jenis kendaraan | Kendaraan terhubung dengan pengguna |
| 5 | Data RFID | Admin menambah UID baru | UID unik | UID tersimpan pada database |
| 6 | Validasi RFID | Scan UID terdaftar dan aktif | 04A1B2C3D4 | Akses diterima dan servo OPEN |
| 7 | Validasi RFID | Scan UID tidak terdaftar | 0000000000 | Akses ditolak dan servo CLOSE |
| 8 | Validasi RFID | Scan UID nonaktif | UID dengan status nonaktif | Akses ditolak |
| 9 | Riwayat Akses | Scan kartu valid | UID aktif | Log masuk atau keluar tersimpan |
| 10 | Laporan | Admin memilih periode laporan | Tanggal awal dan akhir | Rekap aktivitas tampil sesuai periode |
| 11 | Gerbang | Admin menambah data gerbang | Kode dan nama gerbang | Gerbang tampil pada daftar |
| 12 | API Scan | ESP32 mengirim UID ke API | /api/scan.php?uid=04A1B2C3D4 | API mengirim JSON valid |

## Catatan Uji

- Gunakan UID awal untuk pengujian cepat.
- Untuk menguji akses ditolak, gunakan UID yang tidak ada pada database.
- Untuk menguji kartu nonaktif, ubah status kartu di menu Data RFID menjadi Nonaktif.
- Untuk menguji pengguna nonaktif, ubah status pengguna di menu Data Pengguna menjadi Nonaktif.
