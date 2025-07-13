<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
if (!isAdmin()) {
    header("Location: login.php");
    exit();
}
// Dashboard content
?>
