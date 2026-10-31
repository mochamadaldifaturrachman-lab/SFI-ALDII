<?php
$pageTitle = 'Prediksi'; $active = 'predict';
require_once __DIR__ . '/../includes/functions.php';
require_login(); $u = uid();
$total = array_sum(array_column(wallet_balances($pdo, $u), 'balance'));
$day = (int)date('j'); $dim = (int)date('t'); $left = $dim - $day;
$spent = sum_type($pdo, $u, 'expense', date('Y-m-01'));
$avg = $spent / $day;
$est = $avg * $left;
$final = $total - $est;
$jatah = budgets($pdo, $u)['kebutuhan'];
$projMonth = $avg * $dim;
$custom = isset($_GET['daily']) && $_GET['daily'] !== '' ? max(0, (float)$_GET['daily']) : null;
$sc = ['Pola sekarang' => $avg, 'Hemat 10%' => $avg * .9, 'Hemat 20%' => $avg * .8, 'Boros 20%' => $avg * 1.2];
if ($custom !== null) $sc['Skenario Anda'] = $custom;
include __DIR__ . '/../includes/header.php';
?>
<?php if ($spent == 0): ?><div class="alert alert-info">Belum ada pengeluaran bulan ini, jadi rata-rata masih Rp0. Catat pengeluaran agar prediksi bermakna.</div>
<?php elseif ($final < 0): ?><div class="alert alert-danger"><strong>Uang diperkirakan tidak cukup.</strong> Dengan pola sekarang, saldo akhir bulan sekitar <?= rp($final) ?>. Kurangi pengeluaran harian atau tambah pemasukan.</div>
<?php elseif ($jatah > 0 && $projMonth > $jatah): ?><div class="alert alert-warning"><strong>Waspada.</strong> Perkiraan pengeluaran bulan ini <?= rp($projMonth) ?> melebihi jatah kebutuhan <?= rp($jatah) ?>.</div>
<?php else: ?><div class="alert alert-success">Pengeluaran masih aman sampai akhir bulan jika pola sekarang berlanjut.</div><?php endif; ?>
<div class="row g-4">
<div class="col-lg-5"><div class="panel"><h2>Perhitungan</h2>
<table class="table mb-0"><tbody>
<tr><td>Pengeluaran bulan ini (hari ke-<?= $day ?>)</td><td class="text-end"><?= rp($spent) ?></td></tr>
<tr><td>Rata-rata per hari</td><td class="text-end"><?= rp($avg) ?></td></tr>
<tr><td>Sisa hari bulan ini</td><td class="text-end"><?= $left ?> hari</td></tr>
<tr><td>Perkiraan pengeluaran sisa hari</td><td class="text-end"><?= rp($est) ?></td></tr>
<tr><td>Saldo saat ini</td><td class="text-end"><?= rp($total) ?></td></tr>
<tr><td class="fw-semibold">Perkiraan saldo akhir bulan</td><td class="text-end fw-semibold <?= $final < 0 ? 'out' : '' ?>"><?= rp($final) ?></td></tr>
</tbody></table>
<p class="lbl mt-3 mb-0">Rumus: rata-rata harian = pengeluaran bulan ini / tanggal hari ini. Saldo akhir = saldo sekarang - (rata-rata harian x sisa hari). Ini perhitungan statistik sederhana, bukan machine learning.</p></div></div>
<div class="col-lg-7"><div class="panel"><h2>Bandingkan skenario</h2>
<form class="row g-2 mb-3" method="get"><div class="col"><input type="number" min="0" step="1000" name="daily" class="form-control" placeholder="Pengeluaran harian sendiri (Rp)" value="<?= $custom !== null ? (int)$custom : '' ?>"></div><div class="col-auto"><button class="btn btn-navy">Hitung</button></div></form>
<table class="table align-middle mb-0"><thead><tr><th>Skenario</th><th class="text-end">Per hari</th><th class="text-end">Saldo akhir bulan</th><th>Status</th></tr></thead><tbody>
<?php foreach ($sc as $n => $d): $fin = $total - $d * $left; ?>
<tr><td><?= e($n) ?></td><td class="text-end"><?= rp($d) ?></td><td class="text-end fw-semibold <?= $fin < 0 ? 'out' : '' ?>"><?= rp($fin) ?></td><td><?= $fin < 0 ? 'Tidak cukup' : 'Cukup' ?></td></tr>
<?php endforeach; ?></tbody></table></div></div></div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
