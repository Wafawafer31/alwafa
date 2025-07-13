<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$client_slug = $_GET['client'] ?? '';
if (empty($client_slug)) {
    header("Location: /alwafahub/index.php");
    exit();
}

// Fetch client details from DB
// For demonstration, we'll use static data.
$client_name = ucwords(str_replace('-', ' ', $client_slug));

// Photos would be fetched from a directory like /uploads/{$client_slug}/
$photos = [
    ["group" => "08:30-09:00", "files" => ["photo1.jpg", "photo2.jpg"]],
    ["group" => "09:30-10:00", "files" => ["photo3.jpg", "photo4.jpg", "photo5.jpg"]]
];
?>
<?php require_once 'includes/layout_start.php'; ?>
<?php require_once 'includes/header.php'; ?>

<div class="site-section" id="portfolio-section">
    <div class="container">
        <h2 class="text-center mb-4"><?php echo htmlspecialchars($client_name); ?>'s Gallery</h2>
        <div class="filters">
            <ul>
                <li class="active" data-filter="*">All</li>
                <li data-filter=".time-08-30-09-00">08:30 - 09:00</li>
                <li data-filter=".time-09-30-10-00">09:30 - 10:00</li>
            </ul>
        </div>
        <div class="d-flex flex-wrap justify-content-center">
            </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
<?php require_once 'includes/layout_end.php'; ?>
