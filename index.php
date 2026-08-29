<?php
// NYALAKAN DEBUG MODE
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
mysqli_report(MYSQLI_REPORT_OFF);

session_start();

// KONFIGURASI DATABASE LOCALHOST (XAMPP)
$host = 'localhost';
$user = 'root'; 
$pass = ''; 
$db   = 'laporwarga_db'; 

// COBA KONEKSI
$conn = @new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("<div style='padding:20px; font-family:sans-serif; text-align:center;'>
            <h2 style='color:red;'>Koneksi Database Gagal!</h2>
            <p>Pesan Error: <b>" . $conn->connect_error . "</b></p>
         </div>");
}

// INISIALISASI SESSION
if (!isset($_SESSION['voted_reports'])) $_SESSION['voted_reports'] = [];
if (!isset($_SESSION['my_reports'])) $_SESSION['my_reports'] = [];
if (!isset($_SESSION['notif_status'])) $_SESSION['notif_status'] = [];

// API ENDPOINT UNTUK MENGECEK NOTIFIKASI REAL-TIME
if (isset($_GET['check_notifications'])) {
    header('Content-Type: application/json');
    if (empty($_SESSION['my_reports'])) { echo json_encode([]); exit; }
    
    $ids = implode(',', array_map('intval', $_SESSION['my_reports']));
    $res = $conn->query("SELECT id, title, status FROM reports WHERE id IN ($ids)");
    
    $updates = [];
    while($r = $res->fetch_assoc()){
        if (!isset($_SESSION['notif_status'][$r['id']]) || $_SESSION['notif_status'][$r['id']] != $r['status']) {
            if (isset($_SESSION['notif_status'][$r['id']])) {
                $updates[] = $r; // Ada perubahan status
            }
            $_SESSION['notif_status'][$r['id']] = $r['status']; // Update session ke status terbaru
        }
    }
    echo json_encode($updates);
    exit;
}

// FUNGSI UPLOAD FOTO
function uploadImage($fileKey) {
    if (isset($_FILES[$fileKey]) && $_FILES[$fileKey]['error'] == 0) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0755, true);
        
        $ext = strtolower(pathinfo($_FILES[$fileKey]["name"], PATHINFO_EXTENSION));
        if(in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
            $filename = time() . "_" . uniqid() . "." . $ext;
            $target_file = $target_dir . $filename;
            if (move_uploaded_file($_FILES[$fileKey]["tmp_name"], $target_file)) return $target_file;
        }
    }
    return null;
}

// HANDLE LOGOUT PENGURUS
if (isset($_GET['logout'])) {
    unset($_SESSION['admin_role']);
    $_SESSION['popup'] = ['title' => 'Sukses!', 'msg' => 'Sesi Pengurus Telah Ditutup.', 'type' => 'success'];
    header("Location: ./"); 
    exit;
}

// HANDLE SEMUA FORM SUBMIT PHP
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    // 1. TAMBAH LAPORAN (WARGA)
    if ($action === 'add_report') {
        $title = $conn->real_escape_string($_POST['title']);
        $category = $conn->real_escape_string($_POST['category']);
        $area = $conn->real_escape_string($_POST['area']); 
        $specific_address = $conn->real_escape_string($_POST['specific_address']);
        $description = $conn->real_escape_string($_POST['description']);
        $author = $conn->real_escape_string($_POST['author']);
        $verified_residence = $conn->real_escape_string($_POST['verified_residence']); 
        $photo = uploadImage('photo');

        $sql_reporter = "INSERT INTO reporters (name, verified_area) VALUES ('$author', '$verified_residence')";
        $conn->query($sql_reporter);

        $sql = "INSERT INTO reports (title, category, area, specific_address, description, author, report_photo) 
                VALUES ('$title', '$category', '$area', '$specific_address', '$description', '$author', '$photo')";
        
        if($conn->query($sql)) {
            $last_id = $conn->insert_id;
            $_SESSION['my_reports'][] = $last_id;
            $_SESSION['notif_status'][$last_id] = 'baru';
            $_SESSION['popup'] = ['title' => 'Berhasil Melapor!', 'msg' => 'Laporan Anda sudah masuk ke sistem dan menunggu diproses pengurus.', 'type' => 'success'];
        }
    } 
    // 2. UPDATE STATUS (ADMIN) - PERBAIKAN BUG ROLE
    elseif ($action === 'update_status') {
        $report_id = (int)$_POST['report_id'];
        $status = $conn->real_escape_string($_POST['status']);
        $resolver_role = isset($_SESSION['admin_role']) ? strtoupper($_SESSION['admin_role']) : 'RT';
        
        if($status === 'selesai') {
            $note = $conn->real_escape_string($_POST['res_note']);
            $res_photo = uploadImage('res_photo');
            $photo_sql = $res_photo ? ", res_photo='$res_photo'" : "";
            
            $sql = "UPDATE reports SET status='$status', resolved_at=CURRENT_TIMESTAMP, res_note='$note', resolved_by='$resolver_role' $photo_sql WHERE id=$report_id";
        } else {
            $sql = "UPDATE reports SET status='$status', resolved_at=NULL, res_note=NULL, resolved_by=NULL, res_photo=NULL WHERE id=$report_id";
        }
        
        if($conn->query($sql)) $_SESSION['popup'] = ['title' => 'Status Diperbarui', 'msg' => 'Progres penanganan laporan telah berhasil disimpan.', 'type' => 'success'];
    } 
    // 3. UPVOTE DUKUNGAN
    elseif ($action === 'upvote') {
        $report_id = (int)$_POST['report_id'];
        if (!in_array($report_id, $_SESSION['voted_reports'])) {
            $conn->query("UPDATE reports SET upvotes = upvotes + 1 WHERE id=$report_id");
            $_SESSION['voted_reports'][] = $report_id;
            $_SESSION['popup'] = ['title' => 'Dukungan Berhasil', 'msg' => 'Terima kasih atas kepedulian Anda!', 'type' => 'success'];
        } else {
            $_SESSION['popup'] = ['title' => 'Sudah Mendukung', 'msg' => 'Satu pengguna hanya dapat memberikan 1 dukungan pada laporan yang sama.', 'type' => 'error'];
        }
    }
    // 4. LOGIN PENGURUS
    elseif ($action === 'login_admin') {
        $role = $conn->real_escape_string($_POST['role']);
        $password = $conn->real_escape_string($_POST['password']);
        $sql = "SELECT * FROM admins WHERE role='$role' AND password='$password'";
        $res = $conn->query($sql);
        if($res && $res->num_rows > 0) {
            $_SESSION['admin_role'] = $role;
            $_SESSION['popup'] = ['title' => 'Login Berhasil', 'msg' => 'Selamat Datang, Pengurus!', 'type' => 'success'];
        } else {
            $_SESSION['popup'] = ['title' => 'Gagal Masuk', 'msg' => 'Kata sandi salah! Silakan coba lagi.', 'type' => 'error'];
        }
    }

    header("Location: ./"); 
    exit;
}

// MENGAMBIL DATA BLOK VALID
$valid_blocks_db = [];
$block_res = $conn->query("SELECT block_name FROM valid_blocks");
if ($block_res) {
    while($b = $block_res->fetch_assoc()) {
        $valid_blocks_db[] = $b['block_name'];
    }
}

// AMBIL DATA LAPORAN
$reports_db = [];
$result = $conn->query("SELECT * FROM reports ORDER BY created_at DESC");
if(!$result) {
    die("<div style='padding:20px; font-family:sans-serif; text-align:center;'><h2 style='color:#C1432D;'>Error Tabel Database! Pastikan kamu sudah menjalankan Alter Table untuk resolved_by.</h2></div>");
}
while($row = $result->fetch_assoc()){ 
    $reports_db[] = $row; 
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <title>LaporWarga</title>
  
  <link id="manifest-link" rel="manifest" href="">
  <meta name="theme-color" content="#1D5C4A">
  <meta name="mobile-web-app-capable" content="yes">
  <!-- Favicon -->
  <link rel="icon" type="image/png" href="laporwarga.png">
  <link rel="apple-touch-icon" href="laporwarga.png">
  
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  
  <!-- CSS DIPISAH -->
  <link rel="stylesheet" href="style.css">

  <script>
    (function() {
      const savedTheme = localStorage.getItem("lr-theme") || "light";
      document.documentElement.setAttribute("data-theme", savedTheme);
      
      window.addEventListener('DOMContentLoaded', () => {
          const manifestData = {
              "name": "LaporWarga", "short_name": "LaporWarga",
              "start_url": "./", // Relative path untuk localhost
              "display": "standalone",
              "background_color": "#FFFDF0", "theme_color": "#1D5C4A",
              "icons": [{"src": "laporwarga.png", "sizes": "512x512", "type": "image/png"}]
          };
          const manifestBlob = new Blob([JSON.stringify(manifestData)], {type: 'application/json'});
          document.getElementById('manifest-link').setAttribute('href', URL.createObjectURL(manifestBlob));
      });
    })();
  </script>
</head>
<body>

<div id="lr-app">
  <div class="header-area">
    <div style="display:flex; justify-content:space-between; align-items:flex-start;">
      <div>
        <div style="display:flex; align-items:center; gap:14px;">
          <div style="width:56px; height:56px; border-radius:12px; background:#fff; display:flex; align-items:center; justify-content:center; overflow:hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
            <img src="laporwarga.png" alt="Logo LaporWarga" style="width:100%; height:100%; object-fit:cover;">
          </div>
          <span class="lr-display" style="font-size:28px; font-weight:700;">LaporWarga</span>
        </div>
        <p style="margin:12px 0 0; font-size:14px; color:#CFE0D6; max-width:500px; line-height:1.6;">
          Warga Perlu Aksi Nyata. Bukan Kata Kata!
        </p>
      </div>
      <button id="theme-toggle" class="lr-btn" aria-label="Toggle Theme" style="background:rgba(255,255,255,0.15); color:#fff; border-radius:50%; width:44px; height:44px; flex-shrink:0;">
        <svg id="icon-moon" class="icon-svg" viewBox="0 0 24 24"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path></svg>
        <svg id="icon-sun" class="icon-svg" viewBox="0 0 24 24" style="display:none;"><circle cx="12" cy="12" r="5"></circle><line x1="12" y1="1" x2="12" y2="3"></line><line x1="12" y1="21" x2="12" y2="23"></line><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line><line x1="1" y1="12" x2="3" y2="12"></line><line x1="21" y1="12" x2="23" y2="12"></line><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line></svg>
      </button>
    </div>
  </div>

  <div id="lr-stats" class="stats-container"></div>

  <div class="chart-panel-container">
    <div class="lr-card fade-anim" style="padding:24px; display:flex; flex-direction:column;">
      <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:24px; flex-wrap:wrap; gap:16px;">
        <div>
          <h2 class="lr-display" style="font-weight:700; font-size:18px; margin:0;">Total Penyelesaian Masalah</h2>
          <p style="font-size:13px; color:var(--text-muted); margin:4px 0 0;">Jumlah laporan yang berhasil diselesaikan.</p>
        </div>
        <div style="width:180px;">
          <select id="chart-filter-select" class="input-field" style="margin:0; padding:10px 16px; font-weight:600;">
            <option value="mingguan">1 Minggu Terakhir</option>
            <option value="bulanan" selected>1 Bulan Terakhir</option>
            <option value="tahunan">1 Tahun Terakhir</option>
          </select>
        </div>
      </div>
      <div id="progress-chart-wrapper" class="progress-chart-container"></div>
    </div>

    <div class="lr-card fade-anim" id="lr-panel" style="padding:24px; position: relative; display:flex; flex-direction:column; justify-content:center;"></div>
  </div>

  <div style="padding:0 24px 40px; margin-top:10px;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px; flex-wrap:wrap; gap:16px;">
      <div id="lr-cat-tabs" style="display:flex; gap:10px; overflow-x:auto; padding-bottom:4px; scrollbar-width: none;"></div>
      <div style="width:200px;">
        <select id="status-filter-select" class="input-field" style="margin:0; padding:10px 16px; font-weight:600;">
            <option value="semua">Semua Status</option>
            <option value="baru">Baru Dilaporkan</option>
            <option value="diproses">Sedang Diproses</option>
            <option value="selesai">Telah Selesai</option>
        </select>
      </div>
    </div>
    <div id="lr-list" style="display:grid; grid-template-columns:repeat(auto-fill, minmax(280px, 1fr)); gap:20px;"></div>
  </div>

  <div style="text-align:center; padding: 24px 24px 100px; border-top: 1px solid var(--line); margin-top: 20px;">
    <p style="font-size:13px; color:var(--text-muted);">
      LaporWarga Sistem &copy; 2026. <br>
      <button id="btn-open-login" class="lr-btn" style="margin-top:16px; background:var(--line); color:var(--text-main); padding:10px 20px; border-radius:8px; font-weight:600; font-size:13px;">
        <svg class="icon-svg icon-sm" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
        Akses Pengurus (Admin)
      </button>
    </p>
  </div>

  <button id="lr-fab" class="lr-btn fade-anim" aria-label="Buat Laporan Baru" style="position:fixed; bottom:30px; right:30px; background:var(--accent); color:var(--primary-dark); width:60px; height:60px; border-radius:50%; box-shadow:0 8px 24px rgba(0,0,0,0.25); z-index:10;">
    <svg class="icon-svg icon-lg" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
  </button>

  <!-- CUSTOM POP-UP NOTIFICATION -->
  <div id="modal-popup" class="modal-overlay" style="z-index: 999; align-items:center;">
    <div class="modal-content" style="max-width:320px; text-align:center; padding: 32px 24px; animation: popin 0.3s cubic-bezier(0.2, 0.8, 0.2, 1) forwards; margin:auto;">
      <div id="popup-icon-container" style="display:inline-flex; align-items:center; justify-content:center; width:64px; height:64px; border-radius:50%; margin-bottom:20px;"></div>
      <h3 id="popup-title" class="lr-display" style="font-size:20px; margin:0 0 8px; font-weight:700;"></h3>
      <p id="popup-message" style="font-size:14px; color:var(--text-muted); margin:0 0 24px; line-height:1.5;"></p>
      <button onclick="closeModal('modal-popup')" class="lr-btn" style="width:100%; background:var(--primary); color:#fff; padding:12px; border-radius:10px; font-weight:600; font-size:14px;">Tutup</button>
    </div>
  </div>

  <!-- IMAGE VIEWER MODAL -->
  <div id="modal-image-viewer" class="modal-overlay" style="z-index: 1000; align-items: center; justify-content: center; padding:0; background: rgba(0,0,0,0.85);" onclick="closeModal('modal-image-viewer')">
      <button class="lr-btn" style="position:absolute; top:20px; right:20px; background:rgba(255,255,255,0.2); color:#fff; width:44px; height:44px; border-radius:50%; z-index:1001;">
          <svg class="icon-svg" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
      </button>
      <img id="viewer-img" src="" style="max-width:100%; max-height:100vh; object-fit:contain; animation: popin 0.3s ease;">
  </div>

  <!-- MODAL VERIFIKASI WARGA -->
  <div id="modal-verify-warga" class="modal-overlay">
    <div class="modal-content fade-anim" style="max-width:400px;">
      <div style="display:flex; justify-content:flex-end; margin-bottom: -30px; position:relative; z-index:2;">
        <button type="button" onclick="closeModal('modal-verify-warga')" class="lr-btn modal-close-btn" aria-label="Tutup">
          <svg class="icon-svg icon-sm" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
        </button>
      </div>
      
      <div style="text-align:center; margin-bottom: 24px;">
        <div style="display:inline-flex; align-items:center; justify-content:center; width:56px; height:56px; background:rgba(29, 92, 74, 0.1); border-radius:50%; color:var(--primary); margin-bottom:16px;">
         <svg class="icon-svg icon-lg" viewBox="0 0 24 24" style="width:28px; height:28px;">
            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline>
          </svg>
        </div>
        <h3 class="lr-display" style="font-size:22px; margin:0 0 8px; font-weight:700;">Verifikasi Identitas</h3>
        <p style="font-size:14px; color:var(--text-muted); margin:0;">Ketik nama Blok tempat tinggal Anda.</p>
      </div>

      <label style="font-size:13px; font-weight:600; color:var(--text-main);">Ketik Blok Asal Anda</label>
      <input type="text" id="verify-input" class="input-field" placeholder="Masukan Nama Blok Anda" style="margin-bottom:24px; text-transform: capitalize;">
      
      <button type="button" id="btn-do-verify" class="lr-btn" style="width:100%; background:var(--primary); color:#fff; padding:14px; border-radius:10px; font-weight:600; font-size:14px;">
        Lanjut Melapor
      </button>
    </div>
  </div>

  <!-- MODAL LAPOR WARGA -->
  <div id="modal-warga" class="modal-overlay">
    <div class="modal-content fade-anim" style="max-width:500px;">
      <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
        <h3 class="lr-display" style="font-weight:700; font-size:20px; margin:0; color:var(--text-main);">Form Laporan Baru</h3>
        <button type="button" onclick="closeModal('modal-warga')" class="lr-btn modal-close-btn" aria-label="Tutup">
          <svg class="icon-svg" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
        </button>
      </div>
      <p style="font-size:13px; color:var(--text-muted); margin-bottom:24px; line-height:1.5;">Anda terverifikasi sebagai warga <b id="display-verified-area" style="color:var(--primary);"></b>.</p>
      
      <form method="POST" action="" enctype="multipart/form-data">
        <input type="hidden" name="action" value="add_report">
        <input type="hidden" name="verified_residence" id="hidden-verified-area" value="">
        
        <label style="font-size:13px; font-weight:600; color:var(--text-main);">Judul Masalah</label>
        <input type="text" name="title" class="input-field" placeholder="Cth: Pipa air bocor" required style="margin-bottom:16px;">
        
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:16px;">
          <div>
            <label style="font-size:13px; font-weight:600; color:var(--text-main);">Kategori</label>
            <select name="category" id="f-category-select" class="input-field" required></select>
          </div>
          <div>
            <label style="font-size:13px; font-weight:600; color:var(--text-main);">Lokasi Kejadian</label>
            <select name="area" id="f-area-select" class="input-field" required></select>
          </div>
        </div>

        <label style="font-size:13px; font-weight:600; color:var(--text-main); display:flex; align-items:center; gap:6px; margin-bottom: 2px;">
          <svg class="icon-svg icon-sm" viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
          Alamat Spesifik Titik Lokasi
        </label>
        <input type="text" name="specific_address" class="input-field" placeholder="Cth: Di Dekat Pos Satpam" required style="margin-bottom:16px;">
        
        <label style="font-size:13px; font-weight:600; color:var(--text-main);">Deskripsi Detail</label>
        <textarea name="description" class="input-field" rows="4" placeholder="Jelaskan kondisi secara detail..." required style="margin-bottom:16px; resize:vertical;"></textarea>
        
        <label style="font-size:13px; font-weight:600; display:flex; align-items:center; gap:6px; color:var(--text-main); margin-bottom: 2px;">
          <svg class="icon-svg icon-sm" viewBox="0 0 24 24"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path><circle cx="12" cy="13" r="4"></circle></svg> 
          Foto Bukti Laporan (Opsional)
        </label>
        <div class="file-upload-wrapper">
          <input type="file" name="photo" id="f-photo" accept="image/*" class="file-upload-input">
          <div class="file-upload-custom">
            <svg class="icon-svg icon-sm" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
            <span class="file-upload-text" id="f-photo-text">Pilih file gambar...</span>
          </div>
        </div>
        <img id="f-photo-preview" class="image-preview" onclick="viewImage(this.src)" style="margin-bottom: 16px; margin-top: 12px;">

        <label style="font-size:13px; font-weight:600; color:var(--text-main); display:block; margin-top:12px;">Nama Anda (Pelapor)</label>
        <input type="text" name="author" class="input-field" placeholder="Ketik nama asli Anda..." required style="margin-bottom:24px;">
        
        <button type="submit" class="lr-btn" style="width:100%; background:var(--primary); color:#fff; padding:14px; border-radius:10px; font-size:15px; font-weight:600;">
          Kirim Laporan
        </button>
      </form>
    </div>
  </div>

  <!-- MODAL LOGIN ADMIN -->
  <div id="modal-login" class="modal-overlay">
    <div class="modal-content fade-anim" style="max-width:400px;">
      <div style="display:flex; justify-content:flex-end; margin-bottom: -30px; position:relative; z-index:2;">
        <button type="button" onclick="closeModal('modal-login')" class="lr-btn modal-close-btn" aria-label="Tutup">
          <svg class="icon-svg icon-sm" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
        </button>
      </div>
      
      <div style="text-align:center; margin-bottom: 24px;">
        <div id="admin-avatar-icon" style="display:inline-flex; align-items:center; justify-content:center; width:56px; height:56px; background:rgba(29, 92, 74, 0.1); border-radius:50%; color:var(--primary); margin-bottom:16px; cursor:pointer;">
         <svg class="icon-svg icon-lg" viewBox="0 0 24 24" style="width:28px; height:28px;">
            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
            <circle cx="12" cy="7" r="4"></circle>
          </svg>
        </div>
        <h3 class="lr-display" style="font-size:22px; margin:0 0 8px; font-weight:700;">Portal Pengurus</h3>
        <p style="font-size:14px; color:var(--text-muted); margin:0;">Silakan pilih peran dan masukkan kata sandi.</p>
      </div>

      <form method="POST" action="">
        <input type="hidden" name="action" value="login_admin">

        <label style="font-size:13px; font-weight:600; color:var(--text-main);">Peran Akses</label>
        <select name="role" class="input-field" style="margin-bottom:16px;" required>
            <option value="rt">Ketua RT</option>
            <option value="rw">Ketua RW</option>
        </select>
        
        <label style="font-size:13px; font-weight:600; color:var(--text-main);">Kata Sandi</label>
        <div class="pwd-wrapper" style="margin-bottom:24px;">
          <input type="password" name="password" id="admin-pin" class="input-field" placeholder="Masukkan kata sandi" required style="padding:14px 48px 14px 16px;">
          <button type="button" id="toggle-login-pwd" class="lr-btn-icon" aria-label="Tampilkan Password">
            <svg class="icon-svg icon-lg" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
          </button>
        </div>
        
        <div style="display:flex; gap:12px;">
          <button type="button" onclick="closeModal('modal-login')" class="lr-btn" style="flex:1; background:var(--line); color:var(--text-main); padding:14px; border-radius:10px; font-weight:600; font-size:14px;">Batal</button>
          <button type="submit" class="lr-btn" style="flex:1; background:var(--primary); color:#fff; padding:14px; border-radius:10px; font-weight:600; font-size:14px;">Masuk</button>
        </div>
      </form>
    </div>
  </div>

  <!-- MODAL UPDATE STATUS ADMIN -->
  <div id="modal-update" class="modal-overlay">
    <div class="modal-content fade-anim" style="max-width:480px;">
      <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
        <h3 class="lr-display" style="font-weight:700; font-size:20px; margin:0;">Tindak Lanjut Laporan</h3>
        <button type="button" onclick="closeModal('modal-update')" class="lr-btn modal-close-btn" aria-label="Tutup">
          <svg class="icon-svg icon-sm" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
        </button>
      </div>
      
      <form method="POST" action="" enctype="multipart/form-data">
        <input type="hidden" name="action" value="update_status">
        <input type="hidden" name="report_id" id="hidden-report-id">

        <label style="font-size:13px; font-weight:600; color:var(--text-main);">Ubah Status Laporan</label>
        <select name="status" id="u-status-select" class="input-field" style="margin-bottom:20px;" required>
            <option value="baru">Baru Dilaporkan</option>
            <option value="diproses">Sedang Ditangani / Diproses</option>
            <option value="selesai">Penyelesaian Selesai</option>
        </select>
        
        <div id="u-selesai-fields" style="display:none; background:var(--input-bg); padding:20px; border-radius:12px; border:1px solid var(--line); margin-bottom:24px;">
          <label style="font-size:13px; font-weight:600; display:flex; align-items:center; gap:6px; color:var(--text-main);">
            <svg class="icon-svg icon-sm" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg> 
            Catatan Penanganan Wajib
          </label>
          <textarea name="res_note" id="u-note" class="input-field" rows="3" placeholder="Tulis rincian perbaikan..." style="margin-bottom:16px; resize:vertical;"></textarea>
          
          <label style="font-size:13px; font-weight:600; display:flex; align-items:center; gap:6px; color:var(--text-main); margin-bottom: 2px;">
            <svg class="icon-svg icon-sm" viewBox="0 0 24 24"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path><circle cx="12" cy="13" r="4"></circle></svg> 
            Foto Bukti (Opsional)
          </label>
          <div class="file-upload-wrapper">
            <input type="file" name="res_photo" id="u-photo" accept="image/*" class="file-upload-input">
            <div class="file-upload-custom">
              <svg class="icon-svg icon-sm" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
              <span class="file-upload-text" id="u-photo-text">Pilih file gambar...</span>
            </div>
          </div>
          <img id="u-photo-preview" class="image-preview" onclick="viewImage(this.src)" style="margin-top: 12px;">
        </div>
        
        <button type="submit" class="lr-btn" style="width:100%; background:var(--primary); color:#fff; padding:14px; border-radius:10px; font-size:15px; font-weight:600;">
          Simpan Perubahan
        </button>
      </form>
    </div>
  </div>

</div>

<script>
(function () {
  const ICONS = {
    doc: `<svg class="icon-svg" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>`,
    process: `<svg class="icon-svg" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>`,
    check: `<svg class="icon-svg" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>`,
    error: `<svg class="icon-svg" viewBox="0 0 24 24" style="color:var(--danger)"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>`,
    users: `<svg class="icon-svg" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle></svg>`,
    upvote: `<svg class="icon-svg icon-sm" viewBox="0 0 24 24"><path d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3zM7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3"></path></svg>`,
    location: `<svg class="icon-svg icon-sm" viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>`,
    home: `<svg class="icon-svg icon-xl floating-icon" viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>`,
    admin: `<svg class="icon-svg icon-sm" viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>`,
    logout: `<svg class="icon-svg icon-sm" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>`,
    eyeOpen: `<svg class="icon-svg icon-sm" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>`,
    eyeClosed: `<svg class="icon-svg icon-sm" viewBox="0 0 24 24"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>`,
    archive: `<svg class="icon-svg icon-sm" viewBox="0 0 24 24"><polyline points="21 8 21 21 3 21 3 8"></polyline><rect x="1" y="3" width="22" height="5"></rect><line x1="10" y1="12" x2="14" y2="12"></line></svg>`
  };

  const CATS = [
    { id: "infrastruktur", label: "Infrastruktur", color: "#1D5C4A" },
    { id: "kebersihan", label: "Kebersihan", color: "#2F7A5C" },
    { id: "fasilitas", label: "Fasilitas Umum", color: "#3D6FE8" },
    { id: "keamanan", label: "Keamanan", color: "#C1432D" },
  ];
  
  const RESIDENTIAL_BLOCKS = <?php echo json_encode($valid_blocks_db); ?>;
  const FACILITIES = ["Balai Warga", "Taman Bermain Anak Anak", "Pos Satpam"];
  const ALL_AREAS = [...RESIDENTIAL_BLOCKS, ...FACILITIES];

  const STATUS = {
    baru: { label: "Baru Dilaporkan", css: "status-baru" },
    diproses: { label: "Sedang Ditangani", css: "status-diproses" },
    selesai: { label: "Telah Selesai", css: "status-selesai" },
  };
  const DAY = 86400000;
  
  const dbReports = <?php echo json_encode($reports_db); ?>;
  const votedReports = <?php echo json_encode(isset($_SESSION['voted_reports']) ? $_SESSION['voted_reports'] : []); ?>;
  
  // MENGAMBIL DATA ARSIP LOKAL
  let archivedReports = JSON.parse(localStorage.getItem('lr_archived') || '[]');

  let reports = dbReports.map(r => ({
      id: r.id,
      title: r.title,
      desc: r.description,
      author: r.author,
      category: r.category,
      area: r.area,
      specificAddress: r.specific_address,
      status: r.status,
      upvotes: parseInt(r.upvotes),
      createdAt: new Date(r.created_at).getTime(),
      resolvedAt: r.resolved_at ? new Date(r.resolved_at).getTime() : null,
      resNote: r.res_note,
      resolvedBy: r.resolved_by ? r.resolved_by.toUpperCase() : 'RT',
      resPhoto: r.res_photo,
      reportPhoto: r.report_photo
  }));

  let catFilter = "semua";
  let statusFilter = "semua";
  let selectedId = null;
  let adminMode = <?php echo isset($_SESSION['admin_role']) ? 'true' : 'false'; ?>;
  let adminCurrentRole = "<?php echo isset($_SESSION['admin_role']) ? $_SESSION['admin_role'] : ''; ?>"; 
  let chartTimeframe = "bulanan"; 
  const $ = (id) => document.getElementById(id);

  function sanitize(str) {
    if(!str) return "";
    const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#x27;' };
    return str.replace(/[&<>"']/g, match => map[match]);
  }

  function setupThemeIcons() {
      const savedTheme = localStorage.getItem("lr-theme") || "light";
      if(savedTheme === "dark") { $("icon-moon").style.display = "none"; $("icon-sun").style.display = "block"; } 
      else { $("icon-moon").style.display = "block"; $("icon-sun").style.display = "none"; }
  }

  function catInfo(id) { return CATS.find((c) => c.id === id) || CATS[0]; }

  function showPopup(title, msg, type = 'success') {
    $("popup-title").innerText = title;
    $("popup-message").innerText = msg;
    const iconContainer = $("popup-icon-container");
    if(type === 'success') {
        iconContainer.innerHTML = ICONS.check;
        iconContainer.style.background = "rgba(47, 122, 92, 0.1)"; 
        iconContainer.style.color = "var(--success)";
    } else {
        iconContainer.innerHTML = ICONS.error;
        iconContainer.style.background = "rgba(193, 67, 45, 0.1)"; 
        iconContainer.style.color = "var(--danger)";
    }
    openModal('modal-popup');
  }

  window.openModal = function(id) { $(id).style.display = "flex"; }
  window.closeModal = function(id) { $(id).style.display = "none"; }
  window.viewImage = function(src) { $("viewer-img").src = src; openModal("modal-image-viewer"); };

  function renderStats() {
    const total = reports.length;
    const selesai = reports.filter((r) => r.status === "selesai").length;
    const proses = reports.filter((r) => r.status !== "selesai").length;
    const warga = new Set(reports.map((r) => r.author)).size;
    
    const statData = [
      { label: "Total Laporan", val: total, svg: ICONS.doc, filter: "semua" },
      { label: "Sedang Diproses", val: proses, svg: ICONS.process, filter: "diproses" },
      { label: "Terselesaikan", val: selesai, svg: ICONS.check, filter: "selesai" },
      { label: "Partisipasi Warga", val: warga, svg: ICONS.users, filter: "semua" }
    ];

    $("lr-stats").innerHTML = statData.map((d) => `
      <div class="stat-card fade-anim" data-filter="${d.filter}" tabindex="0" role="button">
        <div class="icon-wrapper">${d.svg}</div>
        <div class="value">${d.val}</div>
        <span class="label">${d.label}</span>
      </div>
    `).join("");

    $("lr-stats").querySelectorAll(".stat-card").forEach(btn => {
      btn.addEventListener("click", () => {
        statusFilter = btn.getAttribute("data-filter");
        $("status-filter-select").value = statusFilter;
        renderList();
        $("lr-cat-tabs").parentNode.scrollIntoView({ behavior: 'smooth', block: 'start' });
      });
    });
  }

  function renderChart() {
    const now = new Date();
    let data = [];
    const resolved = reports.filter(r => r.status === "selesai" && r.resolvedAt);

    if (chartTimeframe === 'mingguan') {
      const days = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];
      for (let i = 6; i >= 0; i--) {
        let d = new Date(now.getTime() - (i * DAY));
        let count = resolved.filter(r => {
          let rd = new Date(r.resolvedAt);
          return rd.getDate() === d.getDate() && rd.getMonth() === d.getMonth() && rd.getFullYear() === d.getFullYear();
        }).length;
        data.push({ label: days[d.getDay()], count: count });
      }
    } else if (chartTimeframe === 'bulanan') {
      for (let i = 3; i >= 0; i--) {
        let end = now.getTime() - (i * 7 * DAY);
        let start = end - (7 * DAY);
        let count = resolved.filter(r => r.resolvedAt >= start && r.resolvedAt < end).length;
        data.push({ label: `Minggu ${4-i}`, count: count });
      }
    } else if (chartTimeframe === 'tahunan') {
      const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
      for (let i = 11; i >= 0; i--) {
        let d = new Date(now.getFullYear(), now.getMonth() - i, 1);
        let count = resolved.filter(r => {
          let rd = new Date(r.resolvedAt);
          return rd.getMonth() === d.getMonth() && rd.getFullYear() === d.getFullYear();
        }).length;
        data.push({ label: months[d.getMonth()], count: count });
      }
    }

    const max = Math.max(1, ...data.map(d => d.count));
    let chartHTML = data.map((d, index) => {
      const percentage = (d.count / max) * 100;
      setTimeout(() => { const fill = document.getElementById(`p-fill-${index}`); if(fill) fill.style.width = `${percentage}%`; }, 50 + (index * 100));
      return `
        <div class="progress-row">
          <span class="progress-label">${d.label}</span>
          <div class="progress-track"><div id="p-fill-${index}" class="progress-fill" style="width: 0%;"></div></div>
          <span class="progress-value">${d.count} laporan</span>
        </div>
      `;
    }).join('');
    $("progress-chart-wrapper").innerHTML = chartHTML;
  }

  function renderPanel() {
    const panel = $("lr-panel");
    const selected = reports.find((r) => r.id == selectedId);
    
    if (selected) {
      let resolutionHTML = "";
      let archiveBtnHTML = ""; 
      
      if(selected.status === "selesai") {
        resolutionHTML = `
          <div class="fade-anim" style="margin-top:20px; padding:20px; background:var(--bg-main); border-left:4px solid var(--success); border-radius:12px;">
            <h4 style="font-size:14px; color:var(--success); margin-bottom:8px; display:flex; align-items:center; gap:6px;">${ICONS.check} Laporan Telah Diselesaikan</h4>
            <p style="font-size:14px; color:var(--text-main); line-height:1.6; margin:0;"><b>Catatan Ketua ${sanitize(selected.resolvedBy)}:</b> ${sanitize(selected.resNote) || "Penanganan selesai."}</p>
            ${selected.resPhoto ? `<img src="${sanitize(selected.resPhoto)}" class="photo-evidence" alt="Bukti Selesai Admin" onclick="viewImage(this.src)">` : ''}
          </div>
        `;
        
        // Logika Tampilan Tombol Arsip 
        const isArchived = archivedReports.includes(selected.id.toString());
        if (isArchived) {
            archiveBtnHTML = `<button id="btn-toggle-archive" class="lr-btn" style="flex:1; width:100%; background:transparent; border:2px solid var(--line); color:var(--text-muted); padding:14px; border-radius:10px; font-size:14px; font-weight:600;">${ICONS.archive} Keluarkan dari Arsip</button>`;
        } else {
            archiveBtnHTML = `<button id="btn-toggle-archive" class="lr-btn" style="flex:1; width:100%; background:var(--bg-main); color:var(--text-main); border: 2px solid var(--line); padding:14px; border-radius:10px; font-size:14px; font-weight:600;">${ICONS.archive} Simpan ke Arsip Lokal</button>`;
        }
      }
      
      const hasVoted = votedReports.includes(parseInt(selected.id));
      const voteBtnHTML = hasVoted 
        ? `<button type="button" class="lr-btn" style="flex:1; background:var(--line); color:var(--text-muted); padding:14px; border-radius:10px; font-size:14px; font-weight:600; cursor:not-allowed;">
             ${ICONS.check} Anda Telah Mendukung (${selected.upvotes})
           </button>`
        : `<form method="POST" action="" style="flex:1;">
             <input type="hidden" name="action" value="upvote">
             <input type="hidden" name="report_id" value="${selected.id}">
             <button type="submit" class="lr-btn" style="width:100%; height:100%; background:var(--primary); color:#fff; padding:14px; border-radius:10px; font-size:14px; font-weight:600;">
               ${ICONS.upvote} Dukung (${selected.upvotes})
             </button>
           </form>`;

      panel.innerHTML = `
        <div class="fade-anim" style="display:flex; flex-direction:column; height:100%;">
          <div style="display:flex; justify-content:space-between; align-items:flex-start;">
            <div style="display:flex; align-items:center;">
              <span style="display:inline-flex; align-items:center; width:fit-content; padding:6px 14px; border-radius:99px; background:${catInfo(selected.category).color}; color:#fff; font-size:11px; font-weight:700; white-space:nowrap;">
                ${catInfo(selected.category).label}
              </span>
            </div>
            <button id="lr-panel-close" class="lr-btn modal-close-btn" aria-label="Tutup Detail"><svg class="icon-svg icon-sm" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg></button>
          </div>
          <h3 class="lr-display" style="font-size:24px; margin:20px 0 12px;">${sanitize(selected.title)}</h3>
          <div style="display:flex; flex-direction:column; gap:6px; font-size:13px; color:var(--text-muted); margin-bottom:20px;">
            <div>${ICONS.location} Titik Lokasi: <b>${sanitize(selected.area)}</b></div>
            <div style="display:flex; align-items:center; gap:6px;"><svg class="icon-svg icon-sm" viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg> Rincian: <b>${sanitize(selected.specificAddress)}</b></div>
            <div>Oleh: <b>${sanitize(selected.author)}</b></div>
          </div>
          ${selected.reportPhoto ? `<img src="${sanitize(selected.reportPhoto)}" class="photo-evidence" style="margin-bottom:16px;" alt="Bukti Foto" onclick="viewImage(this.src)">` : ''}
          <p style="font-size:15px; line-height:1.7; color:var(--text-main); background:var(--bg-main); padding:20px; border-radius:12px; border: 1px solid var(--line); margin:0;">${sanitize(selected.desc)}</p>
          ${resolutionHTML}
          
          <div style="display:flex; flex-direction:column; gap:12px; margin-top:auto; padding-top:24px;">
              <div style="display:flex; gap:12px; flex-wrap:wrap;">
                ${voteBtnHTML}
                ${adminMode ? `<button id="btn-open-update" class="lr-btn" style="flex:1; background:var(--accent); color:var(--primary-dark); padding:14px; border-radius:10px; font-size:14px; font-weight:600;">${ICONS.admin} Update Status</button>` : ""}
              </div>
              ${archiveBtnHTML}
          </div>
        </div>
      `;
      $("lr-panel-close").onclick = () => { selectedId = null; render(); };
      
      // LOGIKA AKSI TOMBOL ARSIP
      if ($("btn-toggle-archive")) {
          $("btn-toggle-archive").onclick = () => {
              const strId = selected.id.toString();
              if (archivedReports.includes(strId)) {
                  archivedReports = archivedReports.filter(id => id !== strId);
                  showPopup("Berhasil Dikeluarkan", "Laporan telah dikembalikan ke daftar utama.", "success");
              } else {
                  archivedReports.push(strId);
                  showPopup("Berhasil Diarsipkan", "Laporan ini berada di Arsip Saya.", "success");
              }
              localStorage.setItem('lr_archived', JSON.stringify(archivedReports));
              if(catFilter === "arsip" && !archivedReports.includes(strId)) selectedId = null; 
              render();
          };
      }
      
      if (adminMode) {
        $("btn-open-update").onclick = () => {
          $("hidden-report-id").value = selected.id; $("u-status-select").value = selected.status; $("u-note").value = selected.resNote || "";
          $("u-photo-preview").src = selected.resPhoto ? selected.resPhoto : ""; $("u-photo-preview").style.display = selected.resPhoto ? "block" : "none";
          $("u-selesai-fields").style.display = selected.status === "selesai" ? "block" : "none";
          openModal('modal-update');
        };
      }
    } else {
      panel.innerHTML = `
        <div style="text-align:center; padding:30px 0;" class="fade-anim">
          <div style="margin-bottom:24px;">${ICONS.home}</div>
          <h3 class="lr-display" style="font-size:22px; margin-bottom:12px;">Pusat Laporan Aktif</h3>
          <p style="font-size:14px; line-height:1.7; color:var(--text-muted); max-width:85%; margin: 0 auto;">Pilih salah satu laporan di daftar bawah untuk melihat rincian progres perbaikan beserta bukti foto kerjanya.</p>
          ${adminMode ? `
          <div class="fade-anim" style="margin-top:30px; padding:12px; background:rgba(47, 122, 92, 0.05); border:1px solid var(--success); color:var(--success); border-radius:10px; font-weight:600; font-size:13px;">Akses Ketua ${adminCurrentRole.toUpperCase()} Aktif</div>
          <div style="display:flex; gap:10px; justify-content:center; margin-top:16px;">
            <a href="?logout=1" class="lr-btn" style="text-decoration:none; background:var(--danger); color:#fff; padding:10px 16px; border-radius:8px; font-weight:600; font-size:13px;">${ICONS.logout} Keluar Sesi</a>
          </div>` : ''}
        </div>
      `;
    }
  }

  function renderTabs() {
    const tabs = [{ id: "semua", label: "Semua Laporan" }, ...CATS, { id: "arsip", label: "Arsip Laporan" }];
    $("lr-cat-tabs").innerHTML = tabs.map(c => `<button class="lr-tab ${catFilter === c.id ? "active" : ""}" data-cat="${c.id}">${c.label}</button>`).join("");
    $("lr-cat-tabs").querySelectorAll("button").forEach(btn => { btn.onclick = () => { catFilter = btn.dataset.cat; render(); }; });
  }

  function renderList() {
    const list = reports
      .filter(r => {
        const strId = r.id.toString();
        const isArchived = archivedReports.includes(strId);
        
        // JIKA DI TAB ARSIP, TAMPILKAN HANYA YANG DIARSIPKAN
        if (catFilter === "arsip") return isArchived;
        
        // JIKA DI TAB LAINNYA, SEMBUNYIKAN YANG DIARSIPKAN
        if (isArchived) return false;
        
        return (catFilter === "semua" || r.category === catFilter) && 
               (statusFilter === "semua" || r.status === statusFilter);
      })
      .sort((a, b) => b.createdAt - a.createdAt);

    $("lr-list").innerHTML = list.length === 0
      ? `<div class="fade-anim" style="font-size:14px; color:var(--text-muted); grid-column:1/-1; text-align:center; padding:40px; border:1px dashed var(--line); border-radius:12px;">Belum ada data laporan pada kategori ini.</div>`
      : list.map(r => `
        <div class="lr-card fade-anim" data-id="${r.id}" style="padding:20px; cursor:pointer; display:flex; flex-direction:column;">
          <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:16px; gap:10px;">
            <span style="display:inline-flex; align-items:center; width:fit-content; padding:4px 10px; border-radius:6px; background:${catInfo(r.category).color || '#5C6D64'}; color:#fff; font-size:11px; font-weight:700; white-space:nowrap;">${catInfo(r.category).label || 'Arsip'}</span>
            <span style="display:inline-flex; align-items:center; width:fit-content; font-size:12px; font-weight:600; color:var(--text-main); white-space:nowrap;">
              <span class="status-dot ${STATUS[r.status].css}"></span> ${STATUS[r.status].label}
            </span>
          </div>
          <h4 style="font-size:16px; font-weight:600; margin:0 0 10px; line-height:1.5;">${sanitize(r.title)}</h4>
          <p style="font-size:13px; color:var(--text-muted); margin:0; display:flex; align-items:center; gap:4px;">${ICONS.location} ${sanitize(r.area)}</p>
          <p style="font-size:12px; color:var(--text-muted); margin:4px 0 0 0; display:flex; align-items:center; gap:4px;"><svg class="icon-svg" style="width:14px; height:14px;" viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path></svg> ${sanitize(r.specificAddress)}</p>
          <div style="border-top: 1px solid var(--line); margin-top:auto; padding-top:16px; display:flex; justify-content:space-between; align-items:center;">
            <span style="font-size:13px; font-weight:500; color:var(--text-main);">${sanitize(r.author)}</span>
            <span style="font-size:13px; font-weight:700; color:var(--primary); display:flex; align-items:center; gap:4px;">${ICONS.upvote} ${r.upvotes}</span>
          </div>
        </div>
      `).join("");
      
    $("lr-list").querySelectorAll("[data-id]").forEach(el => {
      el.onclick = () => { selectedId = el.dataset.id; render(); window.scrollTo({top: $("lr-panel").offsetTop - 20, behavior: 'smooth'}); };
    });
  }

  function render() { renderStats(); renderChart(); renderPanel(); renderTabs(); renderList(); }

  $("chart-filter-select").addEventListener("change", function() { chartTimeframe = this.value; renderChart(); });
  $("status-filter-select").addEventListener("change", function() { statusFilter = this.value; render(); });
  $("u-status-select").addEventListener("change", function() { $("u-selesai-fields").style.display = this.value === "selesai" ? "block" : "none"; });

  $("theme-toggle").addEventListener("click", function() {
    const isDark = document.body.getAttribute("data-theme") === "dark";
    const newTheme = isDark ? "light" : "dark";
    document.body.setAttribute("data-theme", newTheme);
    document.documentElement.setAttribute("data-theme", newTheme);
    localStorage.setItem("lr-theme", newTheme);
    $("icon-moon").style.display = newTheme === "dark" ? "none" : "block";
    $("icon-sun").style.display = newTheme === "dark" ? "block" : "none";
  });

  document.querySelectorAll('input, textarea, select').forEach(el => {
    el.addEventListener('focus', function() { setTimeout(() => { this.scrollIntoView({ behavior: 'smooth', block: 'center' }); }, 300); });
  });

  window.togglePwd = function(inputId, btnEl) {
    const inp = $(inputId);
    if(inp.type === "password") { inp.type = "text"; btnEl.innerHTML = ICONS.eyeOpen; } 
    else { inp.type = "password"; btnEl.innerHTML = ICONS.eyeClosed; }
  };
  $("toggle-login-pwd").addEventListener("click", function() { togglePwd("admin-pin", this); });

  $("btn-open-login").addEventListener("click", () => {
    if(adminMode) return showPopup("Akses Ditolak", "Anda sudah berada di sesi pengurus.", "error");
    $("admin-pin").value = ""; $("admin-pin").type = "password"; $("toggle-login-pwd").innerHTML = ICONS.eyeClosed; 
    openModal('modal-login'); 
  });

  $("f-category-select").innerHTML = CATS.map(c => `<option value="${c.id}">${c.label}</option>`).join("");
  $("f-area-select").innerHTML = ALL_AREAS.map(a => `<option value="${a}">${a}</option>`).join("");
  
  $("lr-fab").addEventListener("click", () => { 
      $("verify-input").value = "";
      
      if("Notification" in window && Notification.permission !== "granted" && Notification.permission !== "denied") {
          Notification.requestPermission();
      }

      openModal('modal-verify-warga'); 
  });

  $("btn-do-verify").addEventListener("click", () => {
      const rawVal = $("verify-input").value.trim();
      if(!rawVal) return showPopup("Gagal Verifikasi", "Mohon ketik nama Blok domisili Anda!", "error");
      
      let matchedBlock = null;
      for (let b of RESIDENTIAL_BLOCKS) {
          let cleanDb = b.toLowerCase().replace(/^blok\s+/i, '').trim();
          let cleanInput = rawVal.toLowerCase().replace(/^blok\s+/i, '').trim();
          
          if (cleanDb === cleanInput) {
              matchedBlock = b; 
              break;
          }
      }
      
      if(matchedBlock) {
          $("hidden-verified-area").value = matchedBlock;
          $("display-verified-area").innerText = matchedBlock;
          closeModal('modal-verify-warga');
          openModal('modal-warga');
      } else {
          showPopup("Akses Ditolak!", "Lingkungan tidak terdaftar di wilayah ini.", "error");
      }
  });

  $("f-photo").addEventListener("change", function(e) {
    const file = e.target.files[0];
    if (file && file.type.startsWith('image/')) {
      $("f-photo-text").textContent = file.name;
      const reader = new FileReader();
      reader.onload = function(ev) { $("f-photo-preview").src = ev.target.result; $("f-photo-preview").style.display = "block"; };
      reader.readAsDataURL(file);
    } else { this.value = ""; $("f-photo-text").textContent = "Pilih file gambar..."; $("f-photo-preview").style.display = "none"; }
  });

  $("u-photo").addEventListener("change", function(e) {
    const file = e.target.files[0];
    if (file && file.type.startsWith('image/')) {
      $("u-photo-text").textContent = file.name;
      const reader = new FileReader();
      reader.onload = function(ev) { $("u-photo-preview").src = ev.target.result; $("u-photo-preview").style.display = "block"; };
      reader.readAsDataURL(file);
    } else { this.value = ""; $("u-photo-text").textContent = "Pilih file gambar..."; $("u-photo-preview").style.display = "none"; }
  });

  setInterval(() => {
      // Menggunakan relative path '?check_...' untuk XAMPP
      fetch('?check_notifications=1&t=' + Date.now())
      .then(response => response.json())
      .then(data => {
          data.forEach(rep => {
              let msg = `Laporan Anda "${rep.title}" kini berstatus: ${STATUS[rep.status].label}`;
              showPopup("Update Status Laporan!", msg, "success");
              
              if ("Notification" in window && Notification.permission === "granted") {
                  new Notification("LaporWarga System", { 
                      body: msg, 
                      icon: "laporwarga.png"
                  });
              }
              
              let targetReport = reports.find(r => r.id == rep.id);
              if(targetReport) {
                  targetReport.status = rep.status;
                  if(selectedId == rep.id) renderPanel();
                  renderList();
              }
          });
      }).catch(e => console.log(e));
  }, 10000); 

  <?php if(isset($_SESSION['popup'])): ?>
    showPopup("<?php echo $_SESSION['popup']['title']; ?>", "<?php echo $_SESSION['popup']['msg']; ?>", "<?php echo $_SESSION['popup']['type']; ?>");
  <?php unset($_SESSION['popup']); endif; ?>

  setupThemeIcons(); 
  render();
})();
</script>
</body>
</html>