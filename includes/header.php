<?php
require_once __DIR__ . '/functions.php';
require_login();
$u = uid();
$pageTitle = $pageTitle ?? 'SFI';
$active = $active ?? '';
$menu = ['dashboard' => ['Dashboard', 'speedometer2'], 'wallets' => ['Dompet', 'wallet2'],
  'transactions' => ['Transaksi', 'arrow-left-right'], 'allocation' => ['Pembagian Uang', 'pie-chart'],
  'predict' => ['Prediksi', 'graph-up-arrow'], 'account' => ['Akun', 'person']];
?>
<!DOCTYPE html>
<html lang="id"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($pageTitle) ?> - SFI</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="<?= BASE_URL ?>assets/css/style.css" rel="stylesheet">
</head><body>
<aside class="sidebar" id="sidebar">
  <div class="brand">SFI<small>Student Finance Intelligence</small></div>
  <nav>
  <?php foreach ($menu as $k => $item): ?>
    <a href="<?= BASE_URL ?>pages/<?= $k ?>.php" class="<?= $active === $k ? 'on' : '' ?>"><i class="bi bi-<?= $item[1] ?>"></i><?= $item[0] ?></a>
  <?php endforeach; ?>
  </nav>
  <a href="<?= BASE_URL ?>auth/logout.php"><i class="bi bi-box-arrow-left"></i>Keluar</a>
</aside>
<main class="content">
  <div class="topbar">
    <button class="btn btn-sm btn-outline-secondary d-md-none" onclick="document.getElementById('sidebar').classList.toggle('open')"><i class="bi bi-list"></i></button>
    <h1><?= e($pageTitle) ?></h1>
    <span class="who"><?= e($_SESSION['name'] ?? '') ?></span>
  </div>
  <?php if (!empty($_SESSION['flash'])): $fl = $_SESSION['flash']; unset($_SESSION['flash']); ?>
    <div class="alert alert-<?= $fl[1] ?> py-2"><?= e($fl[0]) ?></div>
  <?php endif; ?>
