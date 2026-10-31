<?php
$pageTitle = 'Transaksi'; $active = 'transactions';
require_once __DIR__ . '/../includes/functions.php';
require_login(); $u = uid();
$wallets = wallet_balances($pdo, $u);
$wids = array_map('intval', array_column($wallets, 'id'));
$cs = $pdo->prepare('SELECT * FROM categories WHERE user_id=? ORDER BY name'); $cs->execute([$u]); $cats = $cs->fetchAll();
$f = ['id' => 0, 'type' => 'expense', 'trx_date' => date('Y-m-d'), 'category_id' => '', 'amount' => '', 'wallet_id' => '', 'to_wallet_id' => '', 'note' => ''];
$err = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $act = $_POST['action'] ?? 'save';
    if ($act === 'delete') {
        $pdo->prepare('DELETE FROM transactions WHERE id=? AND user_id=?')->execute([(int)$_POST['id'], $u]);
        flash('Transaksi dihapus. Saldo dompet otomatis menyesuaikan.');
        redirect('pages/transactions.php');
    }
    foreach ($f as $k => $v) $f[$k] = trim($_POST[$k] ?? $v);
    $f['id'] = (int)$f['id']; $f['amount'] = (int)$f['amount'];
    $d = DateTime::createFromFormat('Y-m-d', $f['trx_date']);
    if (!in_array($f['type'], ['income', 'expense', 'transfer'], true)) $err[] = 'Jenis transaksi tidak valid.';
    if (!$d || $d->format('Y-m-d') !== $f['trx_date']) $err[] = 'Tanggal tidak valid.';
    if ($f['amount'] <= 0) $err[] = 'Nominal harus lebih dari 0.';
    if (mb_strlen($f['note']) > 255) $err[] = 'Keterangan maksimal 255 karakter.';
    if (!in_array((int)$f['wallet_id'], $wids, true)) $err[] = 'Pilih dompet yang valid.';
    $cat = null; $to = null;
    if ($f['type'] === 'transfer') {
        $to = (int)$f['to_wallet_id'];
        if (!in_array($to, $wids, true)) $err[] = 'Pilih dompet tujuan.';
        elseif ($to === (int)$f['wallet_id']) $err[] = 'Dompet asal dan tujuan tidak boleh sama.';
    } else {
        foreach ($cats as $c) if ((int)$c['id'] === (int)$f['category_id'] && $c['type'] === $f['type']) $cat = (int)$c['id'];
        if (!$cat) $err[] = 'Pilih kategori yang sesuai.';
    }
    if (!$err) {
        $p = [$f['type'], $f['amount'], $f['trx_date'], (int)$f['wallet_id'], $to, $cat, $f['note'] ?: null];
        if ($f['id']) {
            $pdo->prepare('UPDATE transactions SET type=?,amount=?,trx_date=?,wallet_id=?,to_wallet_id=?,category_id=?,note=? WHERE id=? AND user_id=?')
                ->execute(array_merge($p, [$f['id'], $u]));
            flash('Transaksi diperbarui.');
        } else {
            $pdo->prepare('INSERT INTO transactions (type,amount,trx_date,wallet_id,to_wallet_id,category_id,note,user_id) VALUES (?,?,?,?,?,?,?,?)')
                ->execute(array_merge($p, [$u]));
            flash('Transaksi disimpan.');
        }
        redirect('pages/transactions.php');
    }
} elseif (isset($_GET['edit'])) {
    $st = $pdo->prepare('SELECT * FROM transactions WHERE id=? AND user_id=?'); $st->execute([(int)$_GET['edit'], $u]);
    if ($r = $st->fetch()) { $f = array_merge($f, $r); $f['amount'] = (int)$r['amount']; }
}

$month = preg_match('/^\d{4}-\d{2}$/', $_GET['m'] ?? '') ? $_GET['m'] : date('Y-m');
$st = $pdo->prepare("SELECT t.*, c.name cat, w.name wname, w2.name w2name FROM transactions t
  JOIN wallets w ON w.id=t.wallet_id LEFT JOIN wallets w2 ON w2.id=t.to_wallet_id
  LEFT JOIN categories c ON c.id=t.category_id
  WHERE t.user_id=? AND DATE_FORMAT(t.trx_date,'%Y-%m')=? ORDER BY t.trx_date DESC, t.id DESC");
$st->execute([$u, $month]); $rows = $st->fetchAll();
include __DIR__ . '/../includes/header.php';
?>
<div class="row g-4">
<div class="col-lg-4"><div class="panel">
  <h2><?= $f['id'] ? 'Edit transaksi' : 'Catat transaksi' ?></h2>
  <?php foreach ($err as $x): ?><div class="alert alert-danger py-2"><?= e($x) ?></div><?php endforeach; ?>
  <?php if (!$wallets): ?><div class="alert alert-warning">Buat dompet dulu di menu Dompet.</div><?php else: ?>
  <form method="post"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $f['id'] ?>">
    <div class="mb-3"><label class="form-label">Jenis</label>
      <select name="type" id="type" class="form-select">
        <?php foreach (['expense' => 'Pengeluaran', 'income' => 'Pemasukan', 'transfer' => 'Transfer antar dompet'] as $k => $v): ?>
        <option value="<?= $k ?>" <?= $f['type'] === $k ? 'selected' : '' ?>><?= $v ?></option><?php endforeach; ?>
      </select></div>
    <div class="mb-3"><label class="form-label">Tanggal</label><input type="date" name="trx_date" class="form-control" value="<?= e($f['trx_date']) ?>" required></div>
    <div class="mb-3 cat-only"><label class="form-label">Kategori</label>
      <select name="category_id" id="category_id" class="form-select"><option value="">- pilih -</option>
        <?php foreach ($cats as $c): ?><option value="<?= $c['id'] ?>" data-type="<?= $c['type'] ?>" <?= (int)$f['category_id'] === (int)$c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option><?php endforeach; ?>
      </select></div>
    <div class="mb-3"><label class="form-label">Nominal (Rp)</label><input type="number" name="amount" min="1" step="1" class="form-control" value="<?= e($f['amount']) ?>" required></div>
    <div class="mb-3"><label class="form-label"><span class="tr-only">Dompet asal</span><span class="cat-only">Dompet</span></label>
      <select name="wallet_id" class="form-select"><?php foreach ($wallets as $w): ?><option value="<?= $w['id'] ?>" <?= (int)$f['wallet_id'] === (int)$w['id'] ? 'selected' : '' ?>><?= e($w['name']) ?></option><?php endforeach; ?></select></div>
    <div class="mb-3 tr-only"><label class="form-label">Dompet tujuan</label>
      <select name="to_wallet_id" class="form-select"><?php foreach ($wallets as $w): ?><option value="<?= $w['id'] ?>" <?= (int)$f['to_wallet_id'] === (int)$w['id'] ? 'selected' : '' ?>><?= e($w['name']) ?></option><?php endforeach; ?></select></div>
    <div class="mb-3"><label class="form-label">Keterangan</label><input name="note" class="form-control" maxlength="255" value="<?= e($f['note']) ?>"></div>
    <button class="btn btn-navy">Simpan</button>
    <?php if ($f['id']): ?><a href="<?= BASE_URL ?>pages/transactions.php" class="btn btn-outline-secondary">Batal</a><?php endif; ?>
  </form><?php endif; ?>
</div></div>
<div class="col-lg-8"><div class="panel">
  <form class="d-flex align-items-center gap-2 mb-3" method="get"><h2 class="mb-0 flex-grow-1">Riwayat</h2>
    <input type="month" name="m" value="<?= e($month) ?>" class="form-control form-control-sm w-auto"><button class="btn btn-sm btn-outline-secondary">Tampilkan</button></form>
  <div class="table-responsive"><table class="table align-middle mb-0"><thead><tr><th>Tanggal</th><th>Keterangan</th><th>Dompet</th><th class="text-end">Nominal</th><th></th></tr></thead><tbody>
  <?php foreach ($rows as $r): $cl = $r['type'] === 'income' ? 'in' : ($r['type'] === 'expense' ? 'out' : 'tf'); ?>
  <tr><td class="text-nowrap"><?= date('d/m/Y', strtotime($r['trx_date'])) ?></td>
    <td><?= $r['type'] === 'transfer' ? 'Transfer' : e($r['cat']) ?><div class="lbl"><?= e($r['note']) ?></div></td>
    <td><?= e($r['wname']) ?><?= $r['w2name'] ? ' <i class="bi bi-arrow-right"></i> ' . e($r['w2name']) : '' ?></td>
    <td class="text-end fw-semibold <?= $cl ?>"><?= ($r['type'] === 'income' ? '+' : ($r['type'] === 'expense' ? '-' : '')) . rp($r['amount']) ?></td>
    <td class="text-end text-nowrap"><a class="btn btn-sm btn-outline-secondary" href="?edit=<?= $r['id'] ?>&m=<?= e($month) ?>"><i class="bi bi-pencil"></i></a>
      <form method="post" class="d-inline" onsubmit="return confirm('Hapus transaksi ini?')"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $r['id'] ?>"><button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></form></td></tr>
  <?php endforeach; if (!$rows): ?><tr><td colspan="5" class="text-secondary">Belum ada transaksi di bulan ini.</td></tr><?php endif; ?>
  </tbody></table></div>
</div></div></div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
