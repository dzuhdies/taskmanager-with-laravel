# Task Manager (Laravel + MySQL)

Aplikasi **Task Manager** sederhana:
- Mobile UI: bottom navigation (fixed), FAB (+), bottom sheet detail
- Fitur: tambah, tandai selesai/pulihkan, hapus, history (arsip)
- Interaksi tanpa reload dengan **Fetch API (AJAX)**

## Setup (MySQL)

1) **Clone / salin project** ke lokal
2) **Jalankan** web server & MySQL (XAMPP/Laragon/dll).
3) **Buat database** baru:
   - Buka `Menu > MySQL > phpMyAdmin` (atau HeidiSQL)
   - Create database: `taskmanajer`
4) **konfigurasi `.env`** (disesuaikan)
5) **migrasi terlebih dahulu** `php artisan migrate`
6) **jalankan aplikasi** `php artisan serve`

Tips: Setelah mengubah `.env`, jalankan `php artisan config:clear`.

---

## Fitur Utama

- Aktif: Daftar bergaris berisi judul tugas. Di kiri ada checkbox untuk menandai Selesai (otomatis pindah ke History). Mengetuk baris akan membuka bottom sheet yang menampilkan deskripsi dan tombol Hapus. Tersedia FAB (+) untuk menambah tugas via modal.
- History: Daftar bergaris berisi judul dan “selesai pada”. Di kanan ada menu ⋮ (kebab) berisi Pulihkan atau Hapus. Tersedia tombol Clear untuk menghapus seluruh history.
