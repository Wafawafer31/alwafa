<?php
// (File ini sudah menjadi bagian dari router, jadi config sudah di-load)
$client_slug = $_GET['client'] ?? '';
if (!$client_slug) {
    die("Client not specified.");
}

$stmt = $mysqli->prepare("SELECT * FROM clients WHERE slug = ?");
$stmt->bind_param("s", $client_slug);
$stmt->execute();
$result = $stmt->get_result();
$client = $result->fetch_assoc();

if (!$client) {
    http_response_code(404);
    die("Client not found.");
}

// Lokasi foto, idealnya dibuat saat klien dibuat
$photo_dir = __DIR__ . '/uploads/' . $client['slug'];
$thumb_dir = $photo_dir . '/thumbs';

// Pastikan direktori ada
if (!is_dir($photo_dir)) mkdir($photo_dir, 0755, true);
if (!is_dir($thumb_dir)) mkdir($thumb_dir, 0755, true);

// Grupkan foto berdasarkan waktu dari nama file (misal: NAMA_JAMMENITDETIK.JPG)
$photos_by_time = [];
$files = glob($photo_dir . '/*.{JPG,jpg,jpeg,png}', GLOB_BRACE);

foreach ($files as $file) {
    // Format: @DETRANIUM_DTRM3843_215205.JPG
    if (preg_match('/_(\d{6})\.(jpg|jpeg|png)$/i', basename($file), $matches)) {
        $time_str = $matches[1];
        $hour = (int)substr($time_str, 0, 2);
        $minute = (int)substr($time_str, 2, 2);

        // Grup per 30 menit
        $slot_minute = floor($minute / 30) * 30;
        $next_slot_minute = $slot_minute + 30;
        $end_hour = $hour;
        if ($next_slot_minute >= 60) {
            $end_hour = $hour + 1;
            $next_slot_minute = 0;
        }
        
        $time_group_key = sprintf('%02d-%02d-%02d-%02d', $hour, $slot_minute, $end_hour, $next_slot_minute);
        $time_group_label = sprintf('%02d:%02d - %02d:%02d', $hour, $slot_minute, $end_hour, $next_slot_minute);
        
        if (!isset($photos_by_time[$time_group_key])) {
            $photos_by_time[$time_group_key] = ['label' => $time_group_label, 'files' => []];
        }
        $photos_by_time[$time_group_key]['files'][] = basename($file);
    }
}

// Buat thumbnail jika belum ada
foreach($files as $file) {
    $thumb_path = $thumb_dir . '/' . basename($file);
    if (!file_exists($thumb_path)) {
        // Simple thumbnail creation (requires GD library)
        list($width, $height) = getimagesize($file);
        $new_width = 300;
        $new_height = floor($height * ($new_width / $width));
        $source = imagecreatefromjpeg($file);
        $thumb = imagecreatetruecolor($new_width, $new_height);
        imagecopyresampled($thumb, $source, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
        imagejpeg($thumb, $thumb_path);
    }
}
?>

<?php require_once 'includes/layout_start.php'; ?>
<link rel="stylesheet" href="/alwafahub/assets/css/rtd.css">
<?php require_once 'includes/header.php'; ?>

<div class="hero-slant overlay" data-stellar-background-ratio="0.5" style="background-image: url('/alwafahub/assets/images/hero-min5.jpg')">
    <div class="container">
        <div class="row align-items-center justify-content-center">
            <div class="col-lg-7 intro text-center">
                <img src="/alwafahub/assets/images/logo_DTRNM.png" alt="Logo" width="150">
                <h1 class="text-white font-weight-bold mb-4" data-aos="fade-up" data-aos-delay="0"><br><br>Realtime Photo Download</h1>
                <h2 class="text-white font-weight-bold mb-4" data-aos="fade-up" data-aos-delay="0"><?php echo htmlspecialchars($client['name']); ?></h2>
                [span_0](start_span)<p class="text-white mb-4" data-aos="fade-up" data-aos-delay="100">Silakan download foto anda di sini</p>[span_0](end_span)
            </div>
        </div>
    </div>
    <div class="slant" style="background-image: url('/alwafahub/assets/images/slant.svg');"></div>
</div>

<div class="site-section" id="portfolio-section">
    <div class="container">
        <?php if (!empty($photos_by_time)): ?>
        <div class="filters" data-aos="fade-up" data-aos-delay="100">
            <ul>
                <li class="active" data-filter="*">All</li>
                <?php foreach ($photos_by_time as $key => $group): ?>
                    <li data-filter=".time-<?php echo str_replace(':', '-', $key); ?>"><?php echo htmlspecialchars($group['label']); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="d-flex flex-wrap justify-content-center" id="photo-grid">
            <?php foreach ($photos_by_time as $key => $group): ?>
                <?php
                $files_json = json_encode($group['files']);
                $first_thumb = '/alwafahub/uploads/' . $client['slug'] . '/thumbs/' . $group['files'][0];
                ?>
                <div class="group-thumb time-<?php echo str_replace(':', '-', $key); ?>" onclick='showPopup(<?php echo $files_json; ?>)'>
                    <img src="<?php echo $first_thumb; ?>" alt="Group Thumbnail">
                    <div class="badge bg-primary"><?php echo htmlspecialchars($group['label']); ?></div>
                    <div class="badge bg-dark "><?php echo count($group['files']); ?> Photos</div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
            <div class="text-center">
                <h3>Belum ada foto yang diunggah untuk acara ini.</h3>
                <p>Silakan kembali lagi nanti.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<div id="popup-container">
    <div id="popup-content">
        <button class="close-btn" onclick="closePopup()">Tutup</button>
        [span_1](start_span)<div style="position: relative; display: inline-block;">[span_1](end_span)
            <img id="popup-image" src="" alt="Preview" style="max-width:100%; border-radius:8px;">
            [span_2](start_span)<div id="popup-number" style="position: absolute; top: 10px; left: 10px; background: rgba(0,0,0,0.6); color: #fff; padding: 4px 8px; border-radius: 4px;"></div>[span_2](end_span)
        </div>
        <div class="mt-3">
            <button class="nav-btn" onclick="prevImage()"><span class="icon-chevron-left"></span> Prev</button>
            <button class="nav-btn" onclick="nextImage()">Next <span class="icon-chevron-right"></span></button>
        </div>
        <div class="mt-3 text-center">
            [span_3](start_span)<a style="text-decoration: none;" id="download-link" class="nav-btn" download><span class="icon-download"> </span> Download Foto Ini</a>[span_3](end_span)
            [span_4](start_span)<br><br><a style="text-decoration: none;" id="zip-link" class="nav-btn"><span class="icon-file-archive-o"> </span> Download Semua (ZIP)</a>[span_4](end_span)
        </div>
        <div class="mt-3 text-center">
            <div class="border rounded p-3 bg-light">
                <p class="mb-2 fw-bold">Pilih 2 foto untuk digabungkan menjadi kolase (Photostrip):</p>
                <div id="thumbnail-list" class="d-flex flex-wrap justify-content-center gap-2"></div>
                <button class="nav-btn mt-3" id="make-collage-btn" style="display:none;" onclick="makeCollage()">Kolase 2 Foto Ini</button>
            </div>
        </div>
    </div>
</div>

<div class="site-footer">
    <div class="container">
        <div class="row justify-content-between">
            <div class="col-lg-4">
                <div class="widget">
                    <h3>Apa itu Realtime Photo Download?</h3>
                    [span_5](start_span)<p>Realtime Photo Download adalah layanan dari AlwafaHub yang bikin hasil foto bisa langsung diunduh ke HP.[span_5](end_span) [span_6](start_span)Setiap tamu cukup scan QR Code yang disediakan, dan fotonya bisa langsung disimpan di smartphone.[span_6](end_span)</p>
                </div>
            </div>
            <div class="col-lg-4">
                 <div class="widget">
                    <h3>Connect with us</h3>
                    <ul class="social list-unstyled">
                        <li><a href="#"><span class="icon-facebook"></span></a></li>
                        <li><a href="#"><span class="icon-twitter"></span></a></li>
                        <li><a href="#"><span class="icon-instagram"></span></a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/isotope-layout@3/dist/isotope.pkgd.min.js"></script>
<script>
    const CLIENT_SLUG = '<?php echo $client['slug']; ?>';
</script>
<script src="/alwafahub/assets/js/script.js"></script>

<?php require_once 'includes/footer.php'; ?>
<?php require_once 'includes/layout_end.php'; ?>
