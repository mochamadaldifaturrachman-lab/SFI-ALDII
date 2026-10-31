<?php
// Sesuaikan jika perlu. WAMP default: user root, password kosong.
define('BASE_URL', '/SFI/');
$DB_HOST = 'localhost';
$DB_NAME = 'sfi_db';
$DB_USER = 'root';
$DB_PASS = '';

try {
    $pdo = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4", $DB_USER, $DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    die('Koneksi database gagal. Pastikan WAMP aktif dan database sfi_db sudah diimport.');
}
