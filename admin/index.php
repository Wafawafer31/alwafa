<?php
require_once '../includes/config.php';
checkAdmin();

$page_title = "Admin Dashboard";
include '../includes/admin_header.php'; // Header khusus admin
?>

<div class="admin-main">
    <h1>Selamat Datang, Admin!</h1>
    <p>Gunakan menu di samping untuk mengelola website AlwafaHub.</p>
    
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Klien</h5>
                    <?php $client_count = $mysqli->query("SELECT COUNT(*) as count FROM clients")->fetch_assoc()['count']; ?>
                    <p class="card-text">Total klien terdaftar: <?php echo $client_count; ?></p>
                    <a href="/alwafahub/admin/clients" class="btn btn-primary">Kelola Klien</a>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Halaman CMS</h5>
                    <?php $page_count = $mysqli->query("SELECT COUNT(*) as count FROM pages")->fetch_assoc()['count']; ?>
                    <p class="card-text">Total halaman: <?php echo $page_count; ?></p>
                    <a href="/alwafahub/admin/pages" class="btn btn-primary">Kelola Halaman</a>
                </div>
            </div>
        </div>
    </div>
    
    <h2 class="mt-5">Sinkronisasi Google Drive</h2>
    <p>Anda dapat menjalankan sinkronisasi secara manual atau mengatur cron job untuk menjalankan file berikut secara berkala (misal: per 5 menit).</p>
    <code>/alwafahub/google_drive_sync.php</code>
    <br><br>
    <a href="/alwafahub/google_drive_sync.php" target="_blank" class="btn btn-secondary">Jalankan Sinkronisasi Manual</a>

</div>

<?php include '../includes/admin_footer.php'; ?>
