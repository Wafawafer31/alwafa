<?php
// Pastikan file ini hanya bisa diakses dari file admin lain
if (!defined('IS_ADMIN_PAGE')) {
    die('Cannot access this file directly.');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Admin Panel'; ?> - AlwafaHub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/alwafahub/assets/css/style.css">
</head>
<body class="admin-body">
    <aside class="admin-sidebar">
        <h3 class="text-white text-center mb-4">AlwafaHub</h3>
        <a href="/alwafahub/admin/" class="<?php echo basename($_SERVER['SCRIPT_NAME']) == 'index.php' ? 'active' : ''; ?>">Dashboard</a>
        <a href="/alwafahub/admin/clients.php" class="<?php echo basename($_SERVER['SCRIPT_NAME']) == 'clients.php' ? 'active' : ''; ?>">Kelola Klien</a>
        <a href="/alwafahub/admin/pages.php" class="<?php echo basename($_SERVER['SCRIPT_NAME']) == 'pages.php' ? 'active' : ''; ?>">Kelola Halaman</a>
        <a href="/alwafahub/admin/collage_editor.php" class="<?php echo basename($_SERVER['SCRIPT_NAME']) == 'collage_editor.php' ? 'active' : ''; ?>">Editor Kolase</a>
        <hr class="text-white-50">
        <a href="/alwafahub/admin/logout.php">Logout</a>
    </aside>
    <main class="admin-main">
