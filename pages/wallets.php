<?php
$pageTitle = 'Dompet'; $active = 'wallets';
require_once __DIR__ . '/../includes/functions.php';
require_login(); $u = uid();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $act = $_POST['action'] ?? ''; $id = (int)($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? ''); $init = (float)($_POST['initial_balance'] ?? 0);
    if ($act === 'delete') {
        $c = $pdo->prepare('SELECT COUNT(*) FROM transactions WHERE user_id=? AND (wallet_id=? OR to_wallet_id=?)');
        $c->execute([$u, $id, $id]);
        if ($c->fetchColumn() > 0) flash('Dompet masih punya transaksi, tidak bisa dihapus.', 'danger');
        else { $pdo->prepare('DELETE FROM wallets WHERE id=? AND user_id=?')->execute([$id, $u]); flash('Dompet dihapus.'); }
    } elseif ($name === '' || mb_strlen($name) > 60) {
        flash('Nama dompet wajib diisi (maksimal 60 karakter).', 'danger');
    } elseif ($act === 'add') {
        $pdo->prepare('INSERT INTO wallets (user_id,name,initial_balance) VALUES (?,?,?)')->execute([$u, $name, $init]);
        flash('Dompet ditambahkan.');
    } elseif ($act === 'edit') {
        $pdo->prepare('UPDATE wallets SET name=?, initial_balance=? WHERE id=? AND user_id=?')->execute([$name, $init, $id, $u]);
        flash('Dompet diperbarui.');
    }
    redirect('pages/wallets.php');
}
$wallets = wallet_balances($pdo, $u);
$total = array_sum(array_column($wallets, 'balance'));
include __DIR__ . '/../includes/header.php';
?>
<div class="row g-4">
<div class="col-lg-7"><div class="panel">
  <h2>Daftar dompet <span class="lbl">- total <?= rp($total) ?></span></h2>
  <table class="table align-middle mb-0"><thead><tr><th>Nama</th><th class="text-end">Saldo awal</th><th class="text-end">Saldo sekarang</th><th></th></tr></thead><tbody>
  <?php foreach ($wallets as $w): ?>
  <tr><form method="post"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $w['id'] ?>">
    <td><input name="name" class="form-control form-control-sm" value="<?= e($w['name']) ?>" maxlength="60"></td>
    <td><input name="initial_balance" type="number" step="1" class="form-control form-control-sm text-end" value="<?= (int)$w['initial_balance'] ?>"></td>
    <td class="text-end fw-semibold"><?= rp($w['balance']) ?></td>
    <td class="text-end text-nowrap">
      <button name="action" value="edit" class="btn btn-sm btn-outline-secondary" title="Simpan"><i class="bi bi-check2"></i></button>
      <button name="action" value="delete" class="btn btn-sm btn-outline-danger" title="Hapus" onclick="return confirm('Hapus dompet ini?')"><i class="bi bi-trash"></i></button>
    </td></form></tr>
  <?php endforeach; if (!$wallets): ?><tr><td colspan="4" class="text-secondary">Belum ada dompet. Tambahkan di samping.</td></tr><?php endif; ?>
  </tbody></table>
  <p class="lbl mt-3 mb-0">Mengubah saldo awal ikut mengubah saldo sekarang. Dompet yang sudah punya transaksi tidak bisa dihapus.</p>
</div></div>
<div class="col-lg-5"><div class="panel"><h2>Tambah dompet</h2>
<form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="add">
  <div class="mb-3"><label class="form-label">Nama dompet</label><input name="name" class="form-control" placeholder="mis. BCA, DANA" maxlength="60" required></div>
  <div class="mb-3"><label class="form-label">Saldo awal (Rp)</label><input name="initial_balance" type="number" step="1" value="0" class="form-control"></div>
  <button class="btn btn-navy">Simpan dompet</button></form></div></div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
