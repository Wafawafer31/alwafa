<?php
require_once '../includes/config.php';

header('Content-Type: image/jpeg');

$client_slug = $_GET['client'] ?? '';
$photo1_name = $_GET['photo1'] ?? '';
$photo2_name = $_GET['photo2'] ?? '';
$border_path = __DIR__ . '/../templates/collage_borders/default_border.png'; // Template border

if (!$client_slug || !$photo1_name || !$photo2_name) {
    die('Missing parameters.');
}

$photo1_path = realpath(__DIR__ . "/../uploads/{$client_slug}/{$photo1_name}");
$photo2_path = realpath(__DIR__ . "/../uploads/{$client_slug}/{$photo2_name}");

if (!file_exists($photo1_path) || !file_exists($photo2_path) || !file_exists($border_path)) {
    die('File not found.');
}

// Buat gambar sumber
$src1 = imagecreatefromjpeg($photo1_path);
$src2 = imagecreatefromjpeg($photo2_path);
$border = imagecreatefrompng($border_path);

list($width1, $height1) = getimagesize($photo1_path);
list($width2, $height2) = getimagesize($photo2_path);
list($border_w, $border_h) = getimagesize($border_path);

// Buat canvas kolase
$collage_width = $border_w;
$collage_height = $border_h;
$collage = imagecreatetruecolor($collage_width, $collage_height);
$white = imagecolorallocate($collage, 255, 255, 255);
imagefill($collage, 0, 0, $white);

// Asumsikan border memiliki 2 area transparan untuk foto
// (Ini perlu disesuaikan dengan template border Anda)
$photo_height = ($collage_height / 2) - 15; // Beri sedikit padding
$photo_width = $collage_width - 20;

// Salin dan resize foto ke canvas
imagecopyresampled($collage, $src1, 10, 10, 0, 0, $photo_width, $photo_height, $width1, $height1);
imagecopyresampled($collage, $src2, 10, $collage_height / 2 + 5, 0, 0, $photo_width, $photo_height, $width2, $height2);

// Tambahkan border di atasnya
imagecopy($collage, $border, 0, 0, 0, 0, $border_w, $border_h);

// Output gambar
imagejpeg($collage);

// Hapus resource dari memori
imagedestroy($src1);
imagedestroy($src2);
imagedestroy($border);
imagedestroy($collage);
