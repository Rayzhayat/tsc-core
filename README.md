# TSC Core — Transportation Management System + Analytics
Aplikasi berbasis web untuk monitoring dan analisis data pengiriman (shipment) secara efisien, mulai dari tracking harian hingga analisis margin per customer dan vendor.
---
### Kenapa saya membuat aplikasi ini?
Aplikasi ini dibangun sebagai tools operasional nyata sekaligus portfolio. Dirancang untuk membantu tim logistik dan finance dalam memonitor data pengiriman, menganalisis profitabilitas, serta mendeteksi data yang belum lengkap secara otomatis.
### Untuk siapa?
Untuk kalian yang mau belajar atau butuh inspirasi tentang aplikasi web berbasis PHP & MySQL, terutama di bidang logistik, transportasi, dan finance.
### Boleh dimodifikasi?
**Sangat boleh!** Tapi tetap sertakan credit siapa yang membuat aplikasi ini.
### Boleh dijual?
**Dilarang keras!** Aplikasi ini gratis dan bebas digunakan oleh siapapun.
---
### Tech Stack
| Layer | Teknologi |
|---|---|
| **Backend** | PHP 7.4+, CodeIgniter 3 |
| **Frontend** | HTML5, CSS3, JavaScript |
| **UI Template** | Tabler UI + CoreUI |
| **Database** | MySQL |
| **Chart** | Chart.js 4 |
| **Icons** | Font Awesome 5 |
| **Data Input** | Google Sheets → Export CSV → Import |
---
### Fitur
1. **Autentikasi**
   Login & logout dengan role-based access control.
2. **Import CSV**
   Upload data monitoring dari Google Sheets. Mendukung sheet type:
   - FTL Non SPX
   - FTL A1 SPX
   - FTL Dedicated
   - FTL COC SPX
   - FTL Reguler SPX
   - Dailyrent
3. **Analytics Dashboard**
   - Summary total shipment, revenue, margin & unfulfill
   - Trend margin per bulan (per sheet type / total)
   - Top 5 customer berdasarkan revenue
   - Profitability per customer
   - Top 5 vendor support
   - Rata-rata shipment per bulan per customer
   - Rute non-profitable
   - Rute yang sering unfulfill
4. **Daily Monitoring**
   Pantau shipment harian dengan deteksi otomatis data bolong, unfulfill, dan pending payment.
5. **Weekly Report**
   Laporan mingguan: performa per customer, data bolong, pending payment, dan unfulfill dalam satu halaman.
6. **Finance**
   Modul khusus finance untuk memantau status pembayaran dari user dan ke vendor, nomor invoice, serta laporan keuangan per periode.
7. **Export CSV**
   Export data analytics & monitoring ke CSV untuk laporan atau bahan presentasi.
---
### Role
| Role | Akses |
|---|---|
| `superadmin` | Akses penuh — import, analytics, finance |
| `finance_staff` | Import CSV + analytics + finance |
| `head_of_departemen` | Analytics (view only) |
| `operational_lead` | Analytics (view only) |
---
### Demo Login
Gunakan akun berikut untuk mencoba aplikasi:

| Field | Value |
|---|---|
| **Username** | `raynorhayat` |
| **Password** | `raynor` |
| **Role** | `superadmin` |

> Akun ini memiliki akses penuh ke seluruh fitur aplikasi.
---
### Instalasi & Konfigurasi
1. Clone atau download repositori ini
2. Masuk ke folder project
3. Jalankan `composer install`
4. Buka `application/config/config.php`, ubah `$config['base_url']` sesuai path lokal kalian
5. Sesuaikan konfigurasi database di `application/config/database.php`
6. Import file SQL dari folder `db-tsc/` ke database kalian
7. Jalankan aplikasi
---
### Tentang Saya
**Raynor Hayat** — Developer aplikasi TMS & Analytics berbasis web (PHP, CodeIgniter 3, MySQL).
