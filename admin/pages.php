<?php
define('IS_ADMIN_PAGE', true);
require_once '../includes/config.php';
checkAdmin();

// Handle form submissions (Add/Edit Page)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $slug = createSlug($_POST['slug'] ?: $title);
    $content = $_POST['content'];
    $page_id = $_POST['page_id'];

    if ($page_id) { // Update
        $stmt = $mysqli->prepare("UPDATE pages SET title=?, slug=?, content=? WHERE id=?");
        $stmt->bind_param("sssi", $title, $slug, $content, $page_id);
    } else { // Insert
        $stmt = $mysqli->prepare("INSERT INTO pages (title, slug, content) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $title, $slug, $content);
    }
    $stmt->execute();
    header('Location: /alwafahub/admin/pages.php');
    exit;
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id_to_delete = (int)$_GET['delete'];
    $stmt = $mysqli->prepare("DELETE FROM pages WHERE id = ?");
    $stmt->bind_param("i", $id_to_delete);
    $stmt->execute();
    header('Location: /alwafahub/admin/pages.php');
    exit;
}

$pages = $mysqli->query("SELECT id, title, slug FROM pages ORDER BY title ASC");
$page_title = "Kelola Halaman";
include 'includes/admin_header.php';
?>

<h1>Kelola Halaman (CMS)</h1>
<p>Buat dan edit halaman konten seperti "Tentang Kami", "Layanan", dll.</p>

<div class="card mb-4">
    <div class="card-header">
        <?php echo isset($_GET['edit']) ? 'Edit Halaman' : 'Tambah Halaman Baru'; ?>
    </div>
    <div class="card-body">
        <?php
        $edit_page = null;
        if (isset($_GET['edit'])) {
            $stmt = $mysqli->prepare("SELECT * FROM pages WHERE id = ?");
            $stmt->bind_param("i", $_GET['edit']);
            $stmt->execute();
            $edit_page = $stmt->get_result()->fetch_assoc();
        }
        ?>
        <form method="POST">
            <input type="hidden" name="page_id" value="<?php echo $edit_page['id'] ?? ''; ?>">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="title" class="form-label">Judul Halaman</label>
                    <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($edit_page['title'] ?? ''); ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="slug" class="form-label">Slug (URL)</label>
                    <input type="text" class="form-control" id="slug" name="slug" value="<?php echo htmlspecialchars($edit_page['slug'] ?? ''); ?>" placeholder="otomatis jika kosong">
                </div>
            </div>
             <div class="mb-3">
                <label for="content" class="form-label">Konten</label>
                <textarea class="form-control" id="content" name="content" rows="10"><?php echo htmlspecialchars($edit_page['content'] ?? ''); ?></textarea>
                <small class="form-text">Anda bisa menggunakan tag HTML di sini.</small>
            </div>
            <button type="submit" class="btn btn-primary"><?php echo $edit_page ? 'Update Halaman' : 'Simpan Halaman'; ?></button>
            <?php if ($edit_page): ?>
            <a href="/alwafahub/admin/pages.php" class="btn btn-secondary">Batal</a>
            <?php endif; ?>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">Daftar Halaman</div>
    <div class="card-body">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Judul</th>
                    <th>Slug</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $pages->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['title']); ?></td>
                    <td>/page/<?php echo $row['slug']; ?></td>
                    <td>
                        <a href="?edit=<?php echo $row['id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                        <a href="?delete=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin hapus halaman ini?')">Hapus</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/admin_footer.php'; ?>
