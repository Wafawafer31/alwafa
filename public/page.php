<?php
$slug = $_GET['slug'] ?? '';
if (!$slug) die("Page not found.");

$stmt = $mysqli->prepare("SELECT * FROM pages WHERE slug = ?");
$stmt->bind_param("s", $slug);
$stmt->execute();
$result = $stmt->get_result();
$page = $result->fetch_assoc();

if (!$page) {
    http_response_code(404);
    die("Page not found.");
}

$page_title = $page['title'];
require_once __DIR__ . '/../includes/layout_start.php';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="site-section">
    <div class="container">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <?php echo $page['content']; ?>
            </div>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../includes/footer.php';
require_once __DIR__ . '/../includes/layout_end.php';
?>
