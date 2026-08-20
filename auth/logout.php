<?php
require_once __DIR__ . '/../config/koneksi.php';

if (!empty($_SESSION['id_user'])) {
    catatAktivitas('Logout dari sistem');
}

$_SESSION = [];
session_destroy();

header('Location: ' . BASE_URL . 'auth/login.php');
exit;
