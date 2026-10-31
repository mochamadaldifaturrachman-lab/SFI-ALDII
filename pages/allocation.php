<?php
$pageTitle = 'Pembagian Uang'; $active = 'allocation';
require_once __DIR__ . '/../includes/functions.php';
require_login(); $u = uid();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $up = $pdo->prepare('INSERT INTO budgets (user_id,pos,amount) VALUES (?,?,?) ON DUPLICATE KEY UPDATE amount=VALUES(amount)');
    foreach (['kebutuhan', 'tabungan', 'darurat'] as $p) $up->execute([$u, $p, max(0, (int)($_POST[$p] ?? 0))]);
    flash('Pembagian uang disimpan.');
    redirect('pages/allocation.php');
}
$b = budgets($pdo, $u);
$total = array_sum(array_column(wallet_balances($pdo, $u), 'balance'));
$used = sum_type($pdo, $u, 'expense', date('Y-m-01'));
$alloc = array_sum($b);
$pct = $b['kebutuhan'] > 0 ? min(100, round($used / $b['kebutuhan'] * 100)) : 0;
include __DIR__ . '/../includes/header.php';
?>
<div class="row g-4">
<div class="col-lg-5"><div class="panel"><h2>Atur pembagian</h2>
<form method="post"><?= csrf_field() ?>
<div class="mb-3"><label class="form-label">Uang kebutuhan bulanan (Rp)</label><input type="number" min="0" step="1" name="kebutuhan" class="form-control" value="<?= (int)$b['kebutuhan'] ?>"></div>
<div class="mb-3"><label class="form-label">Tabungan (Rp)</label><input type="number" min="0" step="1" name="tabungan" class="form-control" value="<?= (int)$b['tabungan'] ?>"></div>
<div class="mb-3"><label class="form-label">Dana darurat (Rp)</label><input type="number" min="0" step="1" name="darurat" class="form-control" value="<?= (int)$b['darurat'] ?>"></div>
<button class="btn btn-navy">Simpan</button></form></div></div>
<div class="col-lg-7"><div class="panel"><h2>Kondisi bulan ini</h2>
<div class="d-flex justify-content-between"><span>Total saldo semua dompet</span><strong><?= rp($total) ?></strong></div>
<div class="d-flex justify-content-between"><span>Total yang dialokasikan</span><strong><?= rp($alloc) ?></strong></div>
<div class="d-flex justify-content-between mb-3"><span>Belum dialokasikan</span><strong class="<?= $total - $alloc < 0 ? 'out' : '' ?>"><?= rp($total - $alloc) ?></strong></div>
<?php if ($alloc > $total): ?><div class="alert alert-warning py-2">Total alokasi melebihi saldo yang ada.</div><?php endif; ?>
<hr><div class="d-flex justify-content-between"><span>Kebutuhan bulanan terpakai</span><span><?= rp($used) ?> dari <?= rp($b['kebutuhan']) ?></span></div>
<div class="progress my-2" style="height:8px"><div class="progress-bar <?= $used > $b['kebutuhan'] ? 'bg-danger' : '' ?>" style="width:<?= $pct ?>%;background-color:#14213d"></div></div>
<div class="lbl">Sisa jatah: <?= rp($b['kebutuhan'] - $used) ?>. Semua pengeluaran bulan ini dihitung sebagai pemakaian uang kebutuhan. Saldo dompet tidak dikurangi dua kali, karena pembagian ini hanya pembatas, bukan pemotong saldo.</div>
</div></div></div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
