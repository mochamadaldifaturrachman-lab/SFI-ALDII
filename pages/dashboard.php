<?php
$pageTitle = 'Dashboard'; $active = 'dashboard';
require_once __DIR__ . '/../includes/functions.php';
require_login(); $u = uid();
$wallets = wallet_balances($pdo, $u);
$total = array_sum(array_column($wallets, 'balance'));
$ms = date('Y-m-01');
$inM = sum_type($pdo, $u, 'income', $ms); $outM = sum_type($pdo, $u, 'expense', $ms);
$b = budgets($pdo, $u);
$st = $pdo->prepare("SELECT t.*, c.name cat, w.name wname FROM transactions t JOIN wallets w ON w.id=t.wallet_id
  LEFT JOIN categories c ON c.id=t.category_id WHERE t.user_id=? ORDER BY t.trx_date DESC, t.id DESC LIMIT 6");
$st->execute([$u]); $recent = $st->fetchAll();
$from = date('Y-m-01', strtotime('-5 months'));
$st = $pdo->prepare("SELECT DATE_FORMAT(trx_date,'%Y-%m') ym, type, SUM(amount) s FROM transactions
  WHERE user_id=? AND type IN ('income','expense') AND trx_date>=? GROUP BY ym, type");
$st->execute([$u, $from]);
$data = [];
for ($i = 5; $i >= 0; $i--) $data[date('Y-m', strtotime("-$i months", strtotime($ms)))] = ['income' => 0, 'expense' => 0];
foreach ($st->fetchAll() as $r) if (isset($data[$r['ym']])) $data[$r['ym']][$r['type']] = (float)$r['s'];
include __DIR__ . '/../includes/header.php';
?>
<div class="row g-4">
<div class="col-lg-5"><div class="panel">
  <div class="lbl">Total seluruh saldo</div><div class="total"><?= rp($total) ?></div>
  <div class="lbl mt-1">dari <?= count($wallets) ?> dompet</div>
  <hr><div class="d-flex justify-content-between"><div><div class="lbl">Pemasukan bulan ini</div><div class="fw-semibold in"><?= rp($inM) ?></div></div>
  <div><div class="lbl">Pengeluaran bulan ini</div><div class="fw-semibold out"><?= rp($outM) ?></div></div>
  <div><div class="lbl">Selisih</div><div class="fw-semibold"><?= rp($inM - $outM) ?></div></div></div>
</div>
<div class="panel"><h2>Pembagian uang</h2>
  <?php $sisa = $b['kebutuhan'] - $outM; ?>
  <div class="d-flex justify-content-between"><span>Kebutuhan bulanan</span><span><?= rp($b['kebutuhan']) ?></span></div>
  <div class="d-flex justify-content-between lbl"><span>Terpakai</span><span><?= rp($outM) ?></span></div>
  <div class="d-flex justify-content-between lbl mb-2"><span>Sisa jatah</span><span class="<?= $sisa < 0 ? 'out' : '' ?>"><?= rp($sisa) ?></span></div>
  <div class="d-flex justify-content-between"><span>Tabungan</span><span><?= rp($b['tabungan']) ?></span></div>
  <div class="d-flex justify-content-between"><span>Dana darurat</span><span><?= rp($b['darurat']) ?></span></div>
  <a href="<?= BASE_URL ?>pages/allocation.php" class="small">Atur pembagian</a>
</div></div>
<div class="col-lg-7"><div class="panel"><h2>Pemasukan dan pengeluaran 6 bulan</h2><canvas id="chart" height="150"></canvas></div>
<div class="panel"><h2>Transaksi terbaru</h2>
<table class="table align-middle mb-0"><tbody>
<?php foreach ($recent as $r): ?><tr><td class="text-nowrap"><?= date('d/m', strtotime($r['trx_date'])) ?></td>
  <td><?= $r['type'] === 'transfer' ? 'Transfer' : e($r['cat']) ?><div class="lbl"><?= e($r['wname']) ?></div></td>
  <td class="text-end fw-semibold <?= $r['type'] === 'income' ? 'in' : ($r['type'] === 'expense' ? 'out' : 'tf') ?>"><?= ($r['type'] === 'income' ? '+' : ($r['type'] === 'expense' ? '-' : '')) . rp($r['amount']) ?></td></tr>
<?php endforeach; if (!$recent): ?><tr><td class="text-secondary">Belum ada transaksi. <a href="<?= BASE_URL ?>pages/transactions.php">Catat yang pertama</a>.</td></tr><?php endif; ?>
</tbody></table></div></div></div>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
new Chart(document.getElementById('chart'), {type:'bar',data:{labels:<?= json_encode(array_keys($data)) ?>,
datasets:[{label:'Pemasukan',data:<?= json_encode(array_column($data, 'income')) ?>,backgroundColor:'#8fb0ff'},
{label:'Pengeluaran',data:<?= json_encode(array_column($data, 'expense')) ?>,backgroundColor:'#14213d'}]},
options:{plugins:{legend:{position:'bottom'}},scales:{y:{beginAtZero:true}}}});
</script>
<?php include __DIR__ . '/../includes/footer.php'; ?>
