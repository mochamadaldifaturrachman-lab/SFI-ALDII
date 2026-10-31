<?php
// Tidak ada landing page: langsung ke dashboard (jika sudah login) atau halaman login.
require_once __DIR__ . '/includes/functions.php';
redirect(uid() ? 'pages/dashboard.php' : 'auth/login.php');
