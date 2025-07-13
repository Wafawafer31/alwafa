<?php
require_once __DIR__ . '/includes/config.php';

// Simple Router
$path = $_GET['path'] ?? '';
$parts = explode('/', rtrim($path, '/'));

switch ($parts[0]) {
    case '':
    case 'home':
        include 'public/home.php';
        break;
    
    case 'client':
        if (isset($parts[1])) {
            $_GET['client'] = $parts[1];
            include 'alwafa.php';
        } else {
            http_response_code(404);
            include 'public/404.php';
        }
        break;

    case 'page':
        if (isset($parts[1])) {
            $_GET['slug'] = $parts[1];
            include 'public/page.php';
        } else {
            http_response_code(404);
            include 'public/404.php';
        }
        break;

    case 'admin':
        $admin_page = $parts[1] ?? 'index';
        $admin_file = __DIR__ . '/admin/' . $admin_page . '.php';
        if (file_exists($admin_file)) {
            include $admin_file;
        } else {
            http_response_code(404);
            include 'public/404.php';
        }
        break;

    case 'ajax':
        $ajax_action = $parts[1] ?? '';
        $ajax_file = __DIR__ . '/ajax/' . $ajax_action . '.php';
        if (file_exists($ajax_file)) {
            include $ajax_file;
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Action not found']);
        }
        break;

    default:
        http_response_code(404);
        include 'public/404.php';
        break;
}
