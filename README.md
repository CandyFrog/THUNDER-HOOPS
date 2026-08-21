# THUNDER HOOPS - Web Application & Dashboard (IoT Hub)

Dashboard web interaktif untuk sistem arcade basket IoT **THUNDER HOOPS** berbasis **PHP & MySQL**. Web ini berfungsi sebagai pusat kendali permainan, monitoring live, statistik skor, manajemen pengguna, serta jembatan API untuk mikrokontroler Arduino.

---

## Repository Terkait (Firmware Hardware)

Proyek web ini terintegrasi langsung dengan perangkat keras (hardware) dan firmware Arduino:  
- **Firmware Arduino Nano V3:** [https://github.com/IQBAL-03/Arduino-Nano-V3](https://github.com/IQBAL-03/Arduino-Nano-V3)

---

## Fitur Utama

- **Kontrol Game Real-Time**: Kirim perintah **START** dan **RESET** langsung dari web dashboard ke perangkat Arduino.
- **Pengaturan Durasi**: Kustomisasi durasi pertandingan secara dinamis.
- **Monitoring & Statistik**: Rekapitulasi hasil game, riwayat skor pemain Kiri vs Kanan, pemenang, dan total statistik pertandingan.
- **Manajemen Akun & Autentikasi**: Hak akses Admin dan User, edit profil, serta foto profil.
- **REST API Terpadu**: Endpoint siap pakai untuk komunikasi data dengan mikrokontroler (ESP8266/ESP32/Arduino).

---

## Persyaratan Sistem

- **Web Server:** Apache / Nginx (XAMPP, Laragon, atau hosting web)
- **PHP:** Versi 7.4 atau lebih baru (ekstensi mysqli aktif)
- **Database:** MySQL / MariaDB
- **Hardware IoT:** Arduino Nano V3 + ESP8266 / ESP32 dengan firmware [Arduino-Nano-V3](https://github.com/IQBAL-03/Arduino-Nano-V3)

---

## Cara Instalasi & Menjalankan

1. **Clone / Pindahkan Proyek**
   Letakkan folder proyek ini ke direktori web server Anda:
   - Laragon: d:\laragon\www\THUNDER-HOOPS atau C:\laragon\www\THUNDER-HOOPS
   - XAMPP: C:\xampp\htdocs\THUNDER-HOOPS

2. **Import Database**
   - Buka phpMyAdmin (http://localhost/phpmyadmin).
   - Buat database baru bernama asketball_arcade.
   - Import file asketball_arcade.sql yang tersedia di direktori root proyek.

3. **Konfigurasi Koneksi Database**
   Buka file config/koneksi.php dan sesuaikan kredensial jika berbeda:
   `php
   System.Management.Automation.Internal.Host.InternalHost = "localhost";
    = "root";
    = "";
      = "basketball_arcade";
   `

4. **Akses Dashboard**
   Buka browser dan akses:
   http://localhost/THUNDER-HOOPS

---

## Daftar API Endpoint (Integrasi IoT)

Seluruh endpoint API berada di dalam folder /api/ dan siap menerima / mengirim data ke mikrokontroler:

| Endpoint | Method | Keterangan & Parameter |
| :--- | :--- | :--- |
| /api/get_settings.php | GET | Mengambil durasi game & perintah terkini (game_command: start/reset/idle). Tambahkan ?ack=1 untuk mereset command ke idle setelah dibaca Arduino. |
| /api/receive.php | GET | Menerima hasil pertandingan dari Arduino.<br>Param: ?skor_kiri=[int]&skor_kanan=[int]&durasi=[int]&pemenang=[KIRI/KANAN/SERI] |
| /api/clear_command.php | GET | Mereset status perintah ke idle. |
| /api/dashboard_stats.php | GET | Endpoint JSON data statistik untuk grafik/dashboard web. |

---

## Kontributor & Lisensi
Dikembangkan untuk proyek game arcade basket IoT terintegrasi antara Web Dashboard & Arduino Nano V3.