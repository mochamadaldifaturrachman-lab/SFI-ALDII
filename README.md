# Student Finance Intelligence (SFI)

PHP Native + MySQL + Bootstrap 5. Dijalankan lokal dengan WAMP.

## Instalasi
1. Ekstrak folder `SFI` ke `C:\wamp64\www\SFI`.
2. Jalankan WAMP sampai ikon hijau.
3. Buka `http://localhost/phpmyadmin`, tab **Import**, pilih `database.sql`, klik **Import** (database `sfi_db` dibuat otomatis).
4. Cek `config/database.php`: default user `root`, password kosong. Ubah jika berbeda. Jika folder bukan `SFI`, ubah `BASE_URL`.
5. Buka `http://localhost/SFI/`, daftar akun, lalu mulai mencatat.

## Cara kerja saldo
Saldo dompet tidak disimpan, tetapi dihitung: saldo awal + pemasukan - pengeluaran - transfer keluar + transfer masuk.
Karena itu edit/hapus transaksi selalu benar, transfer tidak dihitung sebagai pemasukan/pengeluaran, dan tidak ada hitungan ganda.
Pembagian uang (kebutuhan, tabungan, dana darurat) adalah batas alokasi; pengeluaran dihitung sebagai pemakaian uang kebutuhan.

## Struktur
auth/ (login, daftar, keluar), pages/ (dashboard, dompet, transaksi, pembagian uang, prediksi, akun), includes/, config/, assets/, database.sql.

## Catatan
Bootstrap, Bootstrap Icons, dan Chart.js dimuat dari CDN, jadi perlu internet saat membuka halaman.
