<?php
function createSlug($string) {
    return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $string), '-'));
}

function isAdmin() {
    return isset($_SESSION['admin_id']);
}

function checkAdmin() {
    if (!isAdmin()) {
        header('Location: /alwafahub/admin/login');
        exit;
    }
}
