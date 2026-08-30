<div align="center">
<p align="center">
    <img src="laporwarga.png" alt="Logo LaporWarga" width="150"/>
  </p> 
     LAPOR WARGA
    
  ### Warga Perlu Aksi Nyata. Bukan Kata Kata!
  
  [![Live Demo](https://img.shields.io/badge/🚀_Live_Demo-Visit_Site-success?style=for-the-badge)](https://laporwarga.infinityfree.io/)
  [![GitHub](https://img.shields.io/badge/GitHub-Repository-181717?style=for-the-badge&logo=github)](https://[https://github.com/thallalw/laporwarga-itechnocup26])
  [![License](https://img.shields.io/badge/License-MIT-blue?style=for-the-badge)](LICENSE)
  
  **Submission for ITECHNO CUP 2026 - Web Development**
  
  **By OneSolution**
  
</div>

---

## 📋 Daftar Isi

- [Tentang Proyek](#-tentang-proyek)
- [Fitur Unggulan](#-fitur-unggulan)
- [Demo & Screenshot](#-demo--screenshot)
- [Teknologi](#-teknologi)
- [Arsitektur Sistem](#-arsitektur-sistem)
- [Instalasi & Setup](#-instalasi--setup)
- [Penggunaan](#-penggunaan)
- [API Documentation](#-api-documentation)
- [Testing](#-testing)
- [Tim Developer](#-tim-pengembang)
- [Lisensi](#-lisensi)

---

## 👥 Tim Developer

| Nama | Peran | GitHub |
|------|-------|--------|
| **Andhika Athallah Putra Darsono** | Project Lead & Full Stack Developer | [GitHub](https://github.com/thallalw) |
| **Muhammad Ridho Hidayat** | Frontend Developer | [GitHub](https://github.com/ridho-aja) |
| **Andhika Athallah Putra Darsono** | Backend Developer | [GitHub](https://github.com/thallalw) |
| **Adri Manggala Ariyanto** | UI/UX Designer | [GitHub](https://github.com/afoggti-ops) |

---

## 🎯 Tentang Proyek

### Latar Belakang

[Dalam lingkungan tempat tinggal (RT/RW), proses pelaporan masalah fasilitas umum seringkali tidak terstruktur, sulit dilacak, dan kurang transparan. Warga sering tidak tahu apakah laporan mereka (seperti pipa bocor, jalan rusak, atau masalah keamanan) sudah ditangani oleh pengurus atau belum."]

### Solusi yang Ditawarkan

[**LaporWarga** hadir sebagai platform pelaporan digital yang interaktif dan transparan. Warga dapat memantau status perbaikan secara *real-time*, memberikan dukungan (*upvote*) pada laporan warga lain, dan pengurus dapat dengan mudah memperbarui progres penanganan beserta bukti foto kerja secara langsung.]

### Tujuan Proyek

- 🎯 **Tujuan Utama**: Menciptakan sistem pelaporan lingkungan yang transparan, cepat, dan mudah diakses oleh warga maupun pengurus RT/RW.
- 📊 **Target Pengguna**: Warga perumahan/komplek dan jajaran pengurus RT/RW setempat.
- 💡 **Value Proposition**: LaporWarga bukan sekadar formulir pengaduan biasa, melainkan platform yang didesain untuk menjembatani komunikasi efektif antara warga dan pengurus lingkungan. Berikut adalah keunggulan utama aplikasi ini:

1. **Anti-Spam & Laporan Valid (Verified Residence)**
   Sistem mewajibkan pengguna untuk memverifikasi domisili mereka (berdasarkan data blok perumahan yang sah) sebelum melapor[cite: 1, 4]. Ini memastikan bahwa setiap laporan yang masuk berasal dari warga asli, bukan dari pihak luar yang tidak bertanggung jawab.
2. **Penentuan Prioritas Berbasis Komunitas (Upvote System)**
   Masalah yang paling banyak mendapatkan dukungan (*upvote*) dari warga lain akan lebih mudah diidentifikasi[cite: 1]. Hal ini membantu pengurus RT/RW bekerja lebih efisien dengan memprioritaskan masalah yang paling mendesak bagi banyak orang.
3. **Transparansi Bukti Kerja (Evidence-Based Resolution)**
   Aplikasi mencegah perbaikan yang "hanya sekadar janji". Saat pengurus menandai laporan telah "Selesai", sistem mewajibkan mereka untuk menuliskan rincian tindakan dan melampirkan foto bukti hasil kerja[cite: 1].
4. **Performa Ringan & Inklusif (Mendukung SDG 11)**
   Dibangun dengan teknologi *Native/Vanilla* (tanpa memuat *framework* eksternal yang berat), LaporWarga sangat ringan dan cepat diakses[cite: 1]. Hal ini menjamin inklusivitas digital, di mana warga dengan perangkat pintar berspesifikasi rendah sekalipun tetap bisa berpartisipasi dalam membangun komunitasnya[cite: 6]

---

## ✨ Fitur Unggulan

### Fitur Utama

| Fitur | Deskripsi | Keunggulan |
|----------|--------------|---------------|
| **Pelaporan Terverifikasi** | Warga dapat melapor dengan memverifikasi blok domisili (contoh: Blok H, CC2). | Mencegah *spam* dan memastikan laporan berasal dari warga asli wilayah tersebut. |
| **Portal Admin RT/RW** | Akses khusus pengurus untuk memperbarui status laporan (Baru, Diproses, Selesai) dilengkapi dengan bukti foto perbaikan. | Memberikan transparansi aksi nyata dari pengurus secara langsung kepada warga. |
| **Sistem Dukungan (Upvote)** | Warga lain dapat memberikan 1 dukungan (*upvote*) pada laporan yang sama. | Membantu pengurus memprioritaskan penyelesaian masalah yang paling banyak dikeluhkan. |
| **Notifikasi Real-time** | Peringatan *pop-up* otomatis saat ada perubahan status laporan dari admin. | Warga tidak perlu memuat ulang halaman untuk memantau kemajuan laporan. |

### Fitur Tambahan

- **Arsip Lokal**: Fitur penyimpanan (*bookmark*) laporan ke *localStorage* di perangkat pengguna.
- **Dark / Light Mode**: Tampilan UI adaptif yang nyaman digunakan siang maupun malam.
- **Grafik Analitik**: Visualisasi persentase penyelesaian masalah (Mingguan, Bulanan, Tahunan) di *dashboard* utama.
- **PWA (Progressive Web App) Ready**: Mendukung instalasi sebagai aplikasi di layar utama ponsel warga.

---

## 📸 Demo & Screenshot

### Live Demo

🔗 **[Kunjungi Website](https://laporwarga.infinityfree.io/)**

### Screenshot Aplikasi

<div align="center">
  <img src="[URL_SCREENSHOT_1.png]" alt="Homepage" width="800"/>
  <p><em>Homepage - Menampilkan grafik progres perbaikan dan daftar laporan aktif</em></p>
  
  <img src="[URL_SCREENSHOT_2.png]" alt="Dashboard" width="800"/>
  <p><em>Laporan - Tampilan form pengaduan dengan unggah foto bukti</em></p>
  
  <img src="[URL_SCREENSHOT_3.png]" alt="Feature" width="800"/>
  <p><em>Akses Pengurus - Akses login Ketua RT/RW dan pembaruan status laporan</em></p>
</div>


## 🛠️ Teknologi

### Tech Stack

#### Frontend
```
Bahasa       : HTML5, CSS3, JavaScript (Vanilla ES6)
UI/UX        : Custom CSS dengan CSS Variables (Dukungan Dark Mode)
Interaktivitas: Fetch API (AJAX Polling untuk Notifikasi Real-time)
```

#### Backend
```
Bahasa       : PHP (Native)
Database     : MySQL
Environment  : XAMPP / Localhost
```

#### DevOps & Tools
```
Deployment   : Infinityfree.io (Free PHP Hosting)
CI/CD        : GitHub Actions (Auto-deploy via FTP to htdocs)
Testing      : Manual Browser Testing & Postman (API Endpoint Testing)
Monitoring   : PHP Native Error Logging & Infinityfree Control Panel Analytics
```

### Alasan Pemilihan Teknologi

| Teknologi | Alasan Pemilihan |
|-----------|------------------|
| **PHP & MySQL** | Proses server-side yang ringan, handal, dan mudah dikonfigurasi untuk implementasi fungsi CRUD sederhana seperti pembuatan laporan dan manajemen status. |
| **Vanilla JS & Custom CSS** | Meminimalisir ukuran file assets dari library eksternal sehingga waktu muat (load time) website sangat cepat, sangat optimal untuk pengguna seluler. |
| **AJAX Polling** | Memungkinkan fitur Real-time Notification tanpa memerlukan infrastruktur WebSocket yang kompleks pada shared hosting. |

### Dependencies Utama

```Karena LaporWarga dibangun menggunakan arsitektur **Native PHP** dan **Vanilla JavaScript**, proyek ini bersifat mandiri (*standalone*) dan tidak membutuhkan *package manager* eksternal seperti `npm` atau `composer`.
Kebutuhan sistem (*system requirements*) utama untuk menjalankan aplikasi ini hanyalah:
- **PHP**: Versi 7.4 atau lebih baru (Disarankan PHP 8+)
- **Database**: MySQL 5.7+ atau MariaDB
- **Web Server**: Apache / Nginx (via XAMPP/Laragon/Infinityfree)
```

---

## 🏗️ Arsitektur Sistem

### System Architecture

```
Diagram di bawah ini menggambarkan alur kerja aplikasi LaporWarga, mulai dari interaksi pengguna (Warga dan Admin) pada antarmuka *Frontend*, pemrosesan data di *Backend* (PHP), hingga penyimpanan ke Database dan *Local Storage*.

```mermaid
graph TD
    %% Styling
    classDef user fill:#1D5C4A,stroke:#fff,stroke-width:2px,color:#fff;
    classDef front fill:#E8B93D,stroke:#333,stroke-width:2px,color:#12211D;
    classDef back fill:#2F7A5C,stroke:#fff,stroke-width:2px,color:#fff;
    classDef db fill:#C1432D,stroke:#fff,stroke-width:2px,color:#fff;

    %% Actors
    Warga(["Warga / Pelapor"]) ::: user
    Admin(["Admin RT / RW"]) ::: user

    %% System Components
    subgraph Client ["Client Side / Browser"]
        UI["Frontend UI (HTML, CSS, Vanilla JS)"] ::: front
    end

    subgraph Server ["Server Side / Infinityfree"]
        PHP["Backend System (PHP Native)"] ::: back
        Folder["File Storage (Folder uploads)"] ::: back
    end

    subgraph DatabaseLayer ["Database Layer"]
        DB[("MySQL Database (laporwarga_db)")] ::: db
    end

    %% Flows
    Warga -->|Akses Web, Lapor, Upvote| UI
    Admin -->|Login, Update Status| UI
    
    UI -->|HTTP POST| PHP
    UI -.->|AJAX GET Polling| PHP
    PHP -.->|JSON Response| UI
    PHP -->|HTML Render| UI

    PHP <-->|Query SQL CRUD| DB
    PHP -->|Simpan Gambar| Folder
```
### Database Schema (ERD)

```
erDiagram
    REPORTS {
        int id PK
        varchar title
        varchar category
        varchar area
        varchar specific_address
        text description
        varchar author
        varchar report_photo
        enum status "baru, diproses, selesai"
        int upvotes
        timestamp created_at
        timestamp resolved_at
        varchar resolved_by
        text res_note
        varchar res_photo
    }
    
    ADMINS {
        int id PK
        varchar role
        varchar username
        varchar password
    }
    
    VALID_BLOCKS {
        int id PK
        varchar block_name
    }
    
    REPORTERS {
        int id PK
        varchar name
        varchar verified_area
        timestamp created_at
    }

    %% Relationships
    VALID_BLOCKS ||--o{ REPORTERS : "memvalidasi area"
    REPORTERS ||--o{ REPORTS : "membuat"
    ADMINS ||--o{ REPORTS : "menyelesaikan"
```

### Folder Structure
Karena menggunakan arsitektur *Native PHP*, struktur direktori proyek ini sangat sederhana dan ringan:

```text
laporwarga/
├── uploads/             # Direktori (auto-generated) untuk menyimpan foto lampiran laporan & bukti kerja
├── index.php            # Berkas utama aplikasi (berisi logika Backend PHP, UI HTML, dan Vanilla JS)
├── style.css            # Berkas styling (CSS Variables, Dark/Light Mode)
├── laporwarga_db.sql    # Skema dan dump data awal untuk diimpor ke MySQL/MariaDB
├── laporwarga.png       # Aset logo dan ikon web/PWA
└── README.md            # Dokumentasi proyek

---

## ⚙️ Instalasi & Setup

### Prerequisites
Node.js & npm (Opsional, jika ingin mengelola dependencies lain)
XAMPP / Laragon (Apache & MySQL)
Git

### Langkah Instalasi

#### 1️⃣ Clone Repository

```bash
git clone https://github.com/thallalw/laporwarga-itechnocup26.git
cd [laporwarga-itechnocup26]
```

#### 2️⃣ Setup Server Lokal

```
Pindahkan seluruh source code ke dalam direktori htdocs (jika menggunakan XAMPP) atau direktori publik web server Anda.
Nyalakan modul Apache dan MySQL melalui XAMPP Control Panel.
```

#### 3️⃣ Setup Database
```
Buka antarmuka phpMyAdmin (http://localhost/phpmyadmin).
Buat database baru dengan nama laporwarga_db.
Import file laporwarga_db.sql yang tersedia di dalam repositori.
```

#### 4️⃣ Konfigurasi Akses Database

```Jika pengaturan database lokal Anda berbeda, silakan ubah pada bagian konfigurasi di file index.php
$host = 'localhost';$user = 'root'; 
$pass = '';$db   = 'laporwarga_db';
```

#### 5️⃣ Membuat folder untuk code
```buat folder laporwarga di htdocs.
Akses aplikasi melalui browser di alamat: http://localhost/[laporwarga]/
```

Aplikasi akan berjalan di `http://localhost:3000`

---

## 🚀 Penggunaan

### Menjalankan Aplikasi

Karena menggunakan arsitektur PHP Native, aplikasi ini tidak memerlukan proses *build* atau *compile*. Anda bisa menjalankannya dengan dua cara praktis:

**Cara 1: Menggunakan XAMPP/Laragon (Direkomendasikan)**
1. Pastikan folder proyek LaporWarga berada di dalam direktori publik (contoh: `htdocs` untuk XAMPP).
2. Pastikan modul **Apache** dan **MySQL** sudah dalam status *Running* di Control Panel.
3. Buka *browser* dan akses alamat berikut:
   ```text
   http://localhost/[laporwarga]/

### User Guide

#### Untuk Pengguna Umum
1. **Verifikasi & Melapor**: Klik tombol FAB (ikon Plus/Pen) di pojok kanan bawah. Masukkan nama Blok untuk verifikasi (contoh: "Blok H").
2. **Kirim Detail**: Lengkapi form yang mencakup kategori masalah, deskripsi kejadian, alamat spesifik, dan lampirkan foto keluhan jika ada.
3. **Mendukung Laporan**: Untuk isu yang penting, warga bisa klik tombol Dukung (Upvote) pada daftar laporan di halaman utama.

#### Untuk Admin
1. **Login Pengurus**: Gulir ke bagian paling bawah halaman utama, klik tombol Akses Pengurus (Admin).
2. **Kredensial**: Pilih peran (RT/RW). (Default password RT/RW di database: MasyarakatSentosa).
3. **Tindak Lanjut**: Klik laporan warga, pilih Update Status.
4. **Penyelesaian**: Jika status diubah menjadi "Penyelesaian Selesai", admin diwajibkan menulis catatan tindakan yang telah diambil dan mengunggah foto bukti perbaikannya.
---

## 📚 API Documentation

### Base URL

```
Development: http://localhost/[nama-folder]/
Production:  https://[domain]/
```

### Endpoints

```
POST / (action=add_report)    # Mengirim laporan baru beserta foto
POST / (action=update_status) # Admin memperbarui progres laporan
POST / (action=upvote)        # Memberikan dukungan pada laporan
POST / (action=login_admin)   # Verifikasi kredensial RT/RW
```
#### Authentication

```http
GET /?check_notifications=1   # Endpoint JSON untuk mengecek perubahan status laporan (AJAX Polling)
GET /?logout=1                # Menutup sesi (logout) admin
```

#### [Resource 1]

```http
GET    /api/[resource]       # Get all
GET    /api/[resource]/:id   # Get by ID
POST   /api/[resource]       # Create
PUT    /api/[resource]/:id   # Update
DELETE /api/[resource]/:id   # Delete
```

### Example Request

```frontend
// Fungsi ini berjalan secara periodik (setiap 10 detik)
fetch('?check_notifications=1&t=' + Date.now())
  .then(response => response.json())
  .then(data => {
      data.forEach(rep => {
          console.log(`Update: Laporan ID ${rep.id} kini berstatus ${rep.status}`);
          // Menampilkan pop-up notifikasi ke warga
      });
  })
  .catch(error => console.error('Error fetching notifications:', error));
```



---

## 🧪 Testing

### Running Tests

Aplikasi LaporWarga dibangun menggunakan arsitektur Native PHP dan Vanilla JavaScript tanpa *framework* eksternal tambahan, proses pengujian (*testing*) difokuskan pada pengujian fungsional manual (*Manual Functional Testing*) dan pengujian antarmuka (*UI/UX Testing*).

### Metode Pengujian
Berikut adalah skenario pengujian yang telah dilakukan untuk memastikan kelancaran aplikasi:

1. **Functional Testing (Uji Fungsionalitas)**
   - **Form Laporan**: Memastikan warga hanya bisa melapor jika memasukkan nama blok yang valid (sesuai database)[cite: 1, 4]. Menguji proses unggah (*upload*) gambar agar masuk ke folder `uploads/` dengan format dan ukuran yang benar[cite: 1].
   - **Sistem Upvote**: Menguji logika sesi (*session*) agar satu pengguna tidak dapat memberikan dukungan (*upvote*) berkali-kali pada laporan yang sama[cite: 1].
   - **Portal Admin**: Memastikan pembaruan status laporan (Baru --> Diproses --> Selesai) tersimpan ke database, dan field "Catatan" & "Foto Bukti" muncul saat status diselesaikan[cite: 1].

2. **Real-time Feature Testing (Uji Notifikasi)**
   - Menguji *endpoint* AJAX Polling (`?check_notifications=1`) dengan menggunakan dua tab *browser* yang berbeda (satu tab Warga, satu tab Admin) untuk memastikan *pop-up* notifikasi muncul seketika saat ada perubahan status[cite: 1].

3. **Responsive UI & Cross-Browser Testing**
   - Menggunakan fitur *Inspect Element (Device Toolbar)* pada browser untuk memastikan tampilan CSS responsif dan optimal di layar ponsel maupun desktop[cite: 3].
   - Menguji kelancaran transisi antara *Light Mode* dan *Dark Mode* pada sisi klien (JavaScript & CSS Variables)[cite: 1, 3].

### Test Coverage

Karena proyek ini menggunakan pendekatan pengujian fungsional manual (*Manual Functional Testing*), metrik *code coverage* kuantitatif (seperti persentase *Statements*, *Branches*, *Functions*, dan *Lines*) yang biasanya dihasilkan oleh *tools automated testing* tidak diterapkan. 

Meskipun demikian, kami telah melakukan pengujian menyeluruh dan memastikan bahwa **100% fungsionalitas utama (Core Features)** dari aplikasi LaporWarga—termasuk validasi pelapor, kelancaran form unggah gambar, sistem *upvote*, akses portal admin, dan sinkronisasi notifikasi *real-time*—telah lulus uji coba dan berjalan dengan sempurna di lingkungan lokal.

## 📄 Lisensi

Proyek ini dilisensikan di bawah [MIT License](LICENSE) - lihat file LICENSE untuk detail lebih lanjut.

---

<div align="center">

  **Made with ❤️ by OneSoution for ITECHNO CUP 2026**

  
</div>

