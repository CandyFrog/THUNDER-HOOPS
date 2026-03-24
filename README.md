# Basketball Arcade System

Sistem IoT untuk mengelola permainan Basketball Arcade dengan monitoring real-time, statistik lengkap, dan sistem manajemen game.

## Fitur Utama
- **Real-time Monitoring**: Lacak skor dan durasi permainan secara *live*.
- **Manajemen User / Admin**: Sistem login dan registrasi dengan hak akses.
- **Statistik Lengkap**: Riwayat permainan, total game, dan rasio kemenangan.
- **Tampilan Responsif**: Antarmuka modern yang dioptimalkan untuk Desktop dan *Smartphone*.

## Persyaratan Sistem
- Web Server (Apache/Nginx via XAMPP, Laragon, dll.)
- PHP 7.4 atau lebih baru (mendukung `mysqli`)
- MySQL atau MariaDB
- Hardware IoT (ESP8266/ESP32) yang dikonfigurasi untuk menembak API.

## Cara Instalasi

1. **Clone atau Ekstrak File**
   Letakkan *folder* proyek ini (`thunder-hoops`) ke dalam direktori *public_html* atau *htdocs* atau *www* pada web server Anda (contoh: `d:\laragon\www\thunder-hoops`).

2. **Setup Database**
   - Buka phpMyAdmin (biasanya di `http://localhost/phpmyadmin`).
   - Buat database baru dengan nama yang sesuai (misal: `basketball_arcade`).
   - Import file `basketball_arcade.sql` yang ada di dalam *folder* _root_ proyek ini ke dalam database tersebut.

3. **Konfigurasi Database**
   - Buka file `config/koneksi.php`.
   - Pastikan informasi `hostname` (biasanya `localhost`), `username` (biasanya `root`), `password`, dan `database` sesuai dengan pengaturan database Anda.

4. **Jalankan Aplikasi**
   - Akses aplikasi melalui browser dengan alamat `http://localhost/thunder-hoops`.
   - Gunakan fitur **Register** untuk membuat akun baru atau **Login** dengan akun admin yang sudah ada (jika Anda menambahkannya melalui SQL).

## Konfigurasi API (Untuk IoT Mikrokontroler)
Tujuan utama REST API (`api/`) adalah untuk menerima data lemparan bola dari sensor ESP. Pastikan koneksi WiFi pada modul hardware sudah terhubung pada jaringan yang sama dengan server ini dan menembak endpoint sesuai dengan path yang dirancang (`/api/receive.php` dan sejenisnya).
