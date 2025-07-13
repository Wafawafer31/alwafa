<?php
require_once '../includes/config.php';

// Hancurkan semua data sesi
session_destroy();

// Arahkan ke halaman login
header("Location: /alwafahub/admin/login.php");
exit;
