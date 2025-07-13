<?php
// Function to generate a slug from a string
function createSlug($string) {
    return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $string)));
}

// Function to check if admin is logged in
function isAdmin() {
    return isset($_SESSION['admin_id']);
}
?>
