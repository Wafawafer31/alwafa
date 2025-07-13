<?php
define('IS_ADMIN_PAGE', true);
require_once '../includes/config.php';
checkAdmin();

// Handle form submissions (Add/Edit Client)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $slug = createSlug($_POST['slug'] ?: $name);
    $event_date = $_POST['event_date'];
    $gdrive_id = $_POST['google_drive_folder_id'];
    $ftp_folder = $_POST['ftp_folder'] ?? null;
    $ftp_password = $_POST['ftp_password'] ? password_hash($_POST['ftp_password'], PASSWORD_DEFAULT) : null; // Enkripsi password FTP
    $client_id = $_POST['client_id'];

    if ($client_id) { // Update
        // Jangan update password jika kosong
        if ($ftp_password) {
            $stmt = $mysqli->prepare("UPDATE clients SET name=?, slug=?, event_date=?, google_drive_folder_id=?, ftp_folder=?, ftp_password=? WHERE id=?");
            $stmt->bind_param("ssssssi", $name, $slug, $event_date, $gdrive_id, $ftp_folder, $ftp_password, $client_id);
        } else {
            $stmt = $mysqli->prepare("UPDATE clients SET name=?, slug=?, event_date=?, google_drive_folder_id=?, ftp_folder=? WHERE id=?");
            $stmt->bind_param("sssssi", $name, $slug, $event_date, $gdrive_id, $ftp_folder, $client_id);
        }
    } else { // Insert
        $stmt = $mysqli->prepare("INSERT INTO clients (name, slug, event_date, google_drive_folder_id, ftp_folder, ftp_password) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $name, $slug, $event_date, $gdrive_id, $ftp_folder, $ftp_password);
    }
    
    if($stmt->execute()) {
        $client_dir = __DIR__ . '/../uploads/' . $slug;
        if (!is_dir($client_dir)) {
            mkdir($client_dir, 0777, true);
            mkdir($client_dir . '/thumbs', 0777, true);
        }
    }
    header('Location: /alwafahub/admin/clients.php');
    exit;
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id_to_delete = (int)$_GET['delete'];
    $stmt = $mysqli->prepare("DELETE FROM clients WHERE id = ?");
    $stmt->bind_param("i", $id_to_delete);
    $stmt->execute();
    header('Location: /alwafahub/admin/clients.php');
    exit;
}

$clients = $mysqli->query("SELECT * FROM clients ORDER BY event_date DESC");
$page_title = "Kelola Klien";
include 'includes/admin_header.php';
?>

<h1>Kelola Klien</h1>
<p>Tambahkan, edit, atau hapus data klien untuk setiap acara.</p>

<div class="card mb-4">
    <div class="card-header">
        <?php echo isset($_GET['edit']) ? 'Edit Klien' : 'Tambah Klien Baru'; ?>
    </div>
    <div class="card-body">
        <?php
        $edit_client = null;
        if (isset($_GET['edit'])) {
            $stmt = $mysqli->prepare("SELECT * FROM clients WHERE id = ?");
            $stmt->bind_param("i", $_GET['edit']);
            $stmt->execute();
            $edit_client = $stmt->get_result()->fetch_assoc();
        }
        ?>
        <form method="POST">
            <input type="hidden" name="client_id" value="<?php echo $edit_client['id'] ?? ''; ?>">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="name" class="form-label">Nama Acara / Klien</label>
                    <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($edit_client['name'] ?? ''); ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="slug" class="form-label">Slug (URL)</label>
                    <input type="text" class="form-control" id="slug" name="slug" value="<?php echo htmlspecialchars($edit_client['slug'] ?? ''); ?>" placeholder="otomatis jika kosong">
                </div>
            </div>
             <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="event_date" class="form-label">Tanggal Acara</label>
                    <input type="date" class="form-control" id="event_date" name="event_date" value="<?php echo $edit_client['event_date'] ?? ''; ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="google_drive_folder_id" class="form-label">ID Folder Google Drive</label>
                    <input type="text" class="form-control" id="google_drive_folder_id" name="google_drive_folder_id" value="<?php echo htmlspecialchars($edit_client['google_drive_folder_id'] ?? ''); ?>">
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="ftp_folder" class="form-label">Folder FTP (contoh: /acara_nikah)</label>
                    <input type="text" class="form-control" id="ftp_folder" name="ftp_folder" value="<?php echo htmlspecialchars($edit_client['ftp_folder'] ?? ''); ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="ftp_password" class="form-label">Password FTP (kosongkan jika tidak ganti)</label>
                    <input type="password" class="form-control" id="ftp_password" name="ftp_password">
                </div>
            </div>
            <button type="submit" class="btn btn-primary"><?php echo $edit_client ? 'Update Klien' : 'Simpan Klien'; ?></button>
            <?php if ($edit_client): ?>
            <a href="/alwafahub/admin/clients.php" class="btn btn-secondary">Batal</a>
            <?php endif; ?>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">Daftar Klien</div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Nama Klien</th>
                        <th>Folder FTP</th>
                        <th>URL Galeri</th>
                        <th>Status Sink.</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $clients->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['name']); ?><br><small class="text-muted"><?php echo $row['event_date']; ?></small></td>
                        <td><?php echo htmlspecialchars($row['ftp_folder'] ?? 'Tidak diatur'); ?></td>
                        <td><a href="/alwafahub/client/<?php echo $row['slug']; ?>" target="_blank">/client/<?php echo $row['slug']; ?></a></td>
                        <td><?php echo htmlspecialchars($row['sync_status']); ?></td>
                        <td>
                            <a href="?edit=<?php echo $row['id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                            <a href="?delete=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin hapus klien ini?')">Hapus</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'includes/admin_footer.php'; ?>
