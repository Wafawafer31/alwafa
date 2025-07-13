<?php
// Izinkan skrip berjalan untuk waktu yang lama
set_time_limit(0);

require_once __DIR__ . '/includes/config.php';

echo "<pre>"; // Untuk tampilan output yang lebih rapi
echo "Memulai proses sinkronisasi Google Drive...\n";

// Inisialisasi Google Client
$client = new Google\Client();
try {
    // Path ke credentials.json dari file .env
    $credentials_path = $_ENV['GOOGLE_APPLICATION_CREDENTIALS'];
    if (!file_exists($credentials_path)) {
        throw new Exception("File credentials.json tidak ditemukan di path: " . $credentials_path);
    }
    $client->setAuthConfig($credentials_path);
} catch (Exception $e) {
    die("Error saat loading file kredensial: " . $e->getMessage());
}
$client->setApplicationName("AlwafaHub Photo Sync");
$client->setScopes(Google\Service\Drive::DRIVE_READONLY);
$client->setAccessType('offline');

$service = new Google\Service\Drive($client);

// Ambil semua klien yang memiliki folder Google Drive ID
$clients_result = $mysqli->query("SELECT id, slug, google_drive_folder_id FROM clients WHERE google_drive_folder_id IS NOT NULL AND google_drive_folder_id != ''");

if ($clients_result->num_rows === 0) {
    echo "Tidak ada klien yang terhubung dengan Google Drive.\n";
    exit;
}

while ($client_row = $clients_result->fetch_assoc()) {
    $client_slug = $client_row['slug'];
    $folder_id = $client_row['google_drive_folder_id'];
    $local_dir = __DIR__ . '/uploads/' . $client_slug;

    echo "\nMemproses klien: " . $client_slug . "\n";
    echo "Folder ID: " . $folder_id . "\n";
    echo "Direktori Lokal: " . $local_dir . "\n";

    if (!is_dir($local_dir)) {
        echo "Direktori lokal tidak ditemukan, membuat... ";
        mkdir($local_dir, 0777, true);
        mkdir($local_dir . '/thumbs', 0777, true);
        echo "OK\n";
    }

    // Dapatkan daftar file yang sudah ada di lokal
    $local_files = array_map('basename', glob($local_dir . '/*.{JPG,jpg,jpeg,png}', GLOB_BRACE));

    try {
        // Ambil daftar file dari Google Drive
        $response = $service->files->listFiles([
            'q' => "'{$folder_id}' in parents and trashed=false and (mimeType='image/jpeg' or mimeType='image/png')",
            'pageSize' => 200, // Ambil hingga 200 file
            'fields' => 'files(id, name)'
        ]);
        
        $drive_files = $response->getFiles();
        if (empty($drive_files)) {
            echo "Tidak ada file gambar ditemukan di folder Google Drive.\n";
            continue;
        }

        $new_files_downloaded = 0;
        foreach ($drive_files as $file) {
            $file_name = $file->getName();
            [span_0](start_span)// Filter file agar tidak download yang sudah ada[span_0](end_span)
            if (!in_array($file_name, $local_files)) {
                echo "  - File baru ditemukan: " . $file_name . ". Mengunduh... ";
                $file_id = $file->getId();
                $content = $service->files->get($file_id, ['alt' => 'media']);
                
                // Simpan file ke direktori lokal
                file_put_contents($local_dir . '/' . $file_name, $content->getBody()->getContents());
                
                // Buat thumbnail
                create_thumbnail($local_dir . '/' . $file_name, $local_dir . '/thumbs/' . $file_name, 300);

                echo "Selesai.\n";
                $new_files_downloaded++;
            }
        }

        $status_message = "Sinkronisasi terakhir pada " . date('Y-m-d H:i:s') . ". " . $new_files_downloaded . " file baru diunduh.";
        $stmt = $mysqli->prepare("UPDATE clients SET sync_status = ? WHERE id = ?");
        $stmt->bind_param("si", $status_message, $client_row['id']);
        $stmt->execute();

    } catch (Exception $e) {
        echo "ERROR: Terjadi kesalahan saat mengakses Google Drive untuk klien " . $client_slug . ": " . $e->getMessage() . "\n";
    }
}

echo "\nSinkronisasi selesai.\n";
echo "</pre>";

function create_thumbnail($source_path, $dest_path, $thumb_width) {
    if (!file_exists($source_path)) return false;
    list($width, $height, $type) = getimagesize($source_path);
    if ($width == 0) return false;

    $thumb_height = floor($height * ($thumb_width / $width));
    $thumb = imagecreatetruecolor($thumb_width, $thumb_height);

    switch (image_type_to_mime_type($type)) {
        case 'image/jpeg':
            $source = imagecreatefromjpeg($source_path);
            break;
        case 'image/png':
            $source = imagecreatefrompng($source_path);
            break;
        default:
            return false;
    }

    imagecopyresampled($thumb, $source, 0, 0, 0, 0, $thumb_width, $thumb_height, $width, $height);
    imagejpeg($thumb, $dest_path, 85); // Kualitas 85%
    imagedestroy($thumb);
    imagedestroy($source);
    return true;
}
