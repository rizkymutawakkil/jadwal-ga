<?php
session_start();

// Cek apakah user sudah login
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}
?> 