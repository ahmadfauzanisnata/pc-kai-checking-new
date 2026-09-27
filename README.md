# PC/LAPTOP CHECKING SYSTEM
### PT Kereta Api Indonesia (Persero)

Aplikasi web Enterprise untuk mengelola proses pemeriksaan keamanan dan kelayakan perangkat PC atau Laptop pegawai PT Kereta Api Indonesia (Persero). Mengatur siklus lengkap mulai dari pembuatan periode pemeriksaan wilayah, penugasan perangkat ke pemeriksa, pengisian checklist kepatuhan 12 butir standar keamanan informasi, verifikasi berjenjang oleh Admin Wilayah, pengesahan akhir oleh Super Admin, hingga penerbitan laporan resmi berformat PDF (**FR.SM/TI/017.002/10-2020**) dan ekspor data komprehensif ke Excel.

---

## 1. Arsitektur & Teknologi yang Digunakan

- **Backend Framework**: Laravel 12.x (PHP 8.2+)
- **Database**: MySQL 8.x / MariaDB
- **State Machine**: Service-driven Transition Engine (`InspectionStatusService`) dengan Database Transactions, Row-Level Locking, dan Audit Trail menyeluruh.
- **Frontend & Styling**: Tailwind CSS v4, Alpine.js, Blade Components, FontAwesome 6 Icons.
- **Visualisasi & Charts**: Chart.js (Grafik status pemeriksaan & statistik inventaris wilayah).
- **Notifikasi Interaktif**: SweetAlert2 & Alert Components.
- **Ekspor Dokumen**:
  - **PDF**: `barryvdh/laravel-dompdf` (Orientasi Landscape A4, Formulir Standar KAI FR.SM/TI/017.002/10-2020, watermark draft otomatis).
  - **Excel**: `maatwebsite/excel` (16 kolom data lengkap, autofit kolom, custom styling header).
- **API & Integrasi**: Laravel Sanctum (Token-based Authentication untuk REST API V1).

---

## 2. Role & Hak Akses (RBAC)

| Role | Cakupan (Scope) | Hak Akses Utama |
| :--- | :--- | :--- |
| **Super Admin** | Nasional (Seluruh Unit/Wilayah KAI) | - Kelola Master Data: Business Area, Unit, Pengguna, Perangkat, Pegawai<br>- Konfigurasi Formulir & Kriteria Kepatuhan<br>- Monitoring Audit Trail & Log Aktivitas Sistem<br>- Review & Persetujuan Akhir Pemeriksaan (Approve / Return ke Admin)<br>- Penguncian Data (Lock/Unlock dengan alasan tercatat)<br>- Pengarsipan (Archive)<br>- Unduh Dokumen PDF Resmi dan Laporan Excel Nasional |
| **Admin** | Terbatas pada Business Area masing-masing | - Kelola Unit, Pegawai, dan Perangkat dalam wilayahnya<br>- Buat Periode Pemeriksaan Wilayah<br>- Buat Penugasan Pemeriksaan (Batch Assignment ke Pemeriksa)<br>- Verifikasi Hasil Checklist dari Pemeriksa<br>- Meminta Revisi Hasil Pemeriksaan (Return for Revision dengan alasan)<br>- Meneruskan pemeriksaan yang terverifikasi ke Super Admin<br>- Ekspor data pegawai, perangkat, dan laporan pemeriksaan wilayah |
| **Inspector (Pemeriksa)** | Terbatas pada tugas yang ditugaskan | - Menerima daftar penugasan pemeriksaan perangkat<br>- Memulai pemeriksaan (Start Inspection)<br>- Mengisi checklist 12 butir kepatuhan keamanan informasi<br>- Menyimpan draft sementara<br>- Mengunggah bukti foto fisik perangkat dan tangkapan layar<br>- Mengirimkan hasil pemeriksaan ke Admin (Submit)<br>- Memperbaiki dan mengirim ulang jika diminta revisi (Resubmit)<br>- Melihat riwayat pemeriksaan yang telah selesai |

---

## 3. Alur Siklus Pemeriksaan (Inspection Lifecycle)

```
[DRAFT] -> Admin Buat Penugasan
   │
   ▼
[ASSIGNED] -> Ditugaskan ke Pemeriksa
   │
   ▼ (Pemeriksa klik "Mulai")
[IN_PROGRESS] -> Pengisian 12 Checklist & Bukti Foto
   │
   ▼ (Pemeriksa klik "Submit")
[PENDING_ADMIN_VERIFICATION] -> Admin Wilayah Mereview
   │                     │
   │ (Perlu Perbaikan)   │ (Disetujui Admin)
   ▼                     ▼
[REVISION_REQUIRED]   [ADMIN_VERIFIED]
   │                     │
   │ (Pemeriksa revisi)  ▼ (Admin teruskan)
   ▼                  [PENDING_SUPER_ADMIN_APPROVAL]
[RESUBMITTED]            │
   │                     ├── (Dikembalikan Super Admin) ──> [ADMIN_REVISION_REQUIRED]
   └─> (Admin review)    │
                         ▼ (Super Admin Approve)
                      [APPROVED] (Pemeriksaan Sah)
                         │
                         ├──> [LOCKED] (Dikunci dari Perubahan)
                         │       │
                         │       └──> (Buka Kunci dengan Alasan) ──> [ADMIN_REVISION_REQUIRED]
                         │
                         └──> [ARCHIVED] (Arsip Kepatuhan)
```

---

## 4. Format Penomoran Dokumen Otomatis

Nomor referensi pemeriksaan digenerate secara otomatis menggunakan format resmi:
```
{NOMOR_URUT_4_DIGIT}/TI/PC-CHECK/{BULAN_ROMAWI}/{TAHUN}
```
*Contoh:* `0001/TI/PC-CHECK/IX/2026`

Penomoran dijamin berurutan, unik, dan terlindungi dari kondisi balapan (*race condition*) melalui row-level locking transaksi database.

---

## 5. Standar 12 Butir Pemeriksaan Keamanan Informasi

Berdasarkan standar formulir **FR.SM/TI/017.002/10-2020**:
1. **Formulir NDA (Non-Disclosure Agreement)**: Status SUDAH / BELUM ditandatangani.
2. **Password Login Kompleks (Strong Password)**: Min 8 karakter, kombinasi huruf besar/kecil, angka, simbol.
3. **Screensaver Lock Otomatis**: Terkunci otomatis maks dalam 15 menit ketidakaktifan.
4. **Kepatuhan Clear Desk & Clear Screen**: Bebas dari dokumen rahasia atau password tertempel.
5. **Keberadaan File Terlarang / Bajakan**: Bebas dari keygen, patch, software bajakan, torrent.
6. **Instalasi Antivirus Resmi KAI**: Antivirus resmi terpasang dan aktif (Real-time Protection).
7. **Pembaruan Definisi Antivirus**: Signature virus up-to-date (maksimal 7 hari terakhir).
8. **Jadwal Full Scan Mingguan**: Pemindaian berkala otomatis terkonfigurasi.
9. **Lisensi Sistem Operasi Legal**: Windows resmi dan teraktivasi lisensi institusi KAI.
10. **Sinkronisasi Jam ke NTP Server KAI**: Waktu jam sistem tersinkronisasi ke NTP KAI.
11. **Stiker Label Fisik PC / Aset**: Stiker nomor aset tertempel rapi di fisik perangkat.
12. **Hak Akses Khusus**: Status Standard User (USER) vs Administrator Lokal (ADMIN).

---

## 6. Persyaratan Sistem

- **Sistem Operasi**: Windows 10/11, Linux (Ubuntu 20.04+), macOS
- **PHP**: Versi 8.2 atau lebih tinggi
  - Ekstensi PHP yang dibutuhkan: `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `gd` atau `imagick`, `zip`, `fileinfo`
- **Database Server**: MySQL 8.0+ / MariaDB 10.5+
- **Node.js**: Versi 18.x atau 20.x+ & NPM
- **Composer**: Versi 2.x

---

## 7. Langkah Instalasi & Menjalankan Aplikasi

Ikuti langkah-langkah di bawah ini untuk menjalankan aplikasi di lingkungan lokal:

### 1. Masuk ke Direktori Proyek
```bash
cd c:\INTERN\pc-kai-checking-new
```

### 2. Instal Dependensi Backend
```bash
composer install
```

### 3. Instal Dependensi Frontend & Kompilasi Asset
```bash
npm install
npm run build
```

### 4. Konfigurasi Environment (`.env`)
Salin file `.env.example` menjadi `.env` jika belum ada:
```bash
cp .env.example .env
```
Pastikan pengaturan koneksi database di file `.env` telah sesuai:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pc_laptop_checking_system
DB_USERNAME=root
DB_PASSWORD=
```

### 5. Generate Application Key
```bash
php artisan key:generate
```

### 6. Buat Database & Jalankan Migrasi Beserta Seeder
Pastikan MySQL service telah berjalan di komputer Anda, lalu jalankan:
```bash
php artisan migrate:fresh --seed
```

### 7. Buat Symlink Storage Publik
Untuk memastikan berkas bukti lampiran dapat diakses:
```bash
php artisan storage:link
```

### 8. Jalankan Server Lokal
```bash
php artisan serve
```
Aplikasi kini dapat diakses melalui browser pada: **`http://127.0.0.1:8000`**

---

## 8. Akun Default Pengujian (Hasil Seeder)

Semua akun menggunakan kata sandi standar: **`password`**

| Role | Email Login | NIPP | Kata Sandi | Wilayah Kerja |
| :--- | :--- | :--- | :--- | :--- |
| **Super Admin** | `superadmin@example.com` | `SA001` | `password` | Kantor Pusat (Semua Area) |
| **Admin Wilayah** | `admin@example.com` | `ADM001` | `password` | DAOP 1 Jakarta |
| **Pemeriksa (Inspector)** | `pemeriksa@example.com` | `INS001` | `password` | DAOP 1 Jakarta |

*Catatan: Formulir login mendukung login menggunakan **Email** maupun **NIPP**.*

---

## 9. Menjalankan Pengujian Otomatis (Automated Tests)

Aplikasi dilengkapi dengan suite pengujian komprehensif (Unit, Feature, Workflow, Authorization, Reports, dan API):
```bash
php artisan test
```
*Hasil:* **24 passed (83 assertions)**

---

## 10. Dokumentasi Endpoint REST API V1

Semua endpoint API diawali dengan prefiks `/api/v1/` dan mengembalikan respon berformat JSON standar:

### Autentikasi
- `POST /api/v1/auth/login`: Login dengan email/nipp & password -> Menghasilkan Sanctum Bearer Token
- `POST /api/v1/auth/logout`: Revoke access token aktif *(Auth required)*
- `GET /api/v1/auth/me`: Informasi profil pengguna yang sedang login *(Auth required)*

### Dashboard & Ringkasan
- `GET /api/v1/dashboard`: Statistik ringkas yang disesuaikan dengan role pengguna aktif *(Auth required)*
- `GET /api/v1/reports/summary`: Ringkasan kepatuhan dan status temuan *(Auth required)*

### Master Data
- `GET /api/v1/master/business-areas`: Daftar Business Area aktif *(Auth required)*
- `GET /api/v1/master/units`: Daftar Unit Kerja *(Auth required)*
- `GET /api/v1/master/employees`: Daftar Pegawai dengan pagination & pencarian *(Auth required)*
- `GET /api/v1/master/devices`: Daftar Perangkat PC/Laptop *(Auth required)*

### Pemeriksaan (Inspections)
- `GET /api/v1/inspections`: Daftar pemeriksaan sesuai hak akses pengguna *(Auth required)*
- `GET /api/v1/inspections/{id}`: Detail lengkap pemeriksaan, checklist, dan bukti *(Auth required)*
- `POST /api/v1/inspections/{id}/checklist`: Simpan draft checklist *(Inspector only)*
- `POST /api/v1/inspections/{id}/submit`: Kirim hasil pemeriksaan ke Admin *(Inspector only)*
- `POST /api/v1/inspections/{id}/attachments`: Unggah berkas bukti lampiran *(Inspector only)*
- `GET /api/v1/inspections/{id}/history`: Riwayat audit trail dan status *(Auth required)*

---

## 11. Panduan Troubleshooting Umum

1. **Error: Database connection refused / SQLSTATE[HY000] [2002]**:
   - Pastikan service MySQL/MariaDB (misal via XAMPP atau Windows Service) sedang aktif di port `3306`.
   - Periksa konfigurasi `DB_HOST`, `DB_PORT`, `DB_USERNAME`, dan `DB_PASSWORD` di file `.env`.

2. **Error: Berkas lampiran tidak tampil di browser**:
   - Jalankan `php artisan storage:link` untuk memastikan symlink `public/storage` terhubung ke `storage/app/public`.

3. **Tampilan CSS / Desain berantakan**:
   - Pastikan build frontend telah dijalankan dengan `npm run build`.

4. **PDF tidak memuat teks atau error DomPDF**:
   - Pastikan ekstensi PHP `gd` atau `imagick` dan `mbstring` telah diaktifkan di `php.ini`.

---
*Dikembangkan dengan standar kehandalan tinggi untuk Direktorat Pengamanan Informasi & Teknologi PT Kereta Api Indonesia (Persero).*
