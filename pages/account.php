<?php
$pageTitle = 'Akun'; $active = 'account';
require_once __DIR__ . '/../includes/functions.php';
require_login(); $u = uid();
$st = $pdo->prepare('SELECT * FROM users WHERE id=?'); $st->execute([$u]); $me = $st->fetch();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    if (!password_verify($_POST['password'] ?? '', $me['password_hash'])) {
        flash('Password salah. Akun tidak dihapus.', 'danger'); redirect('pages/account.php');
    }
    $pdo->beginTransaction();
    try {
        $pdo->prepare('DELETE FROM transactions WHERE user_id=?')->execute([$u]);
        $pdo->prepare('DELETE FROM users WHERE id=?')->execute([$u]);
        $pdo->commit();
    } catch (Exception $e) { $pdo->rollBack(); flash('Gagal menghapus akun.', 'danger'); redirect('pages/account.php'); }
    $_SESSION = []; session_destroy();
    header('Location: ' . BASE_URL . 'auth/login.php'); exit;
}
include __DIR__ . '/../includes/header.php';
?>
<div class="panel" style="max-width:520px"><h2>Profil</h2>
<div class="lbl">Nama</div><div class="mb-2"><?= e($me['name']) ?></div>
<div class="lbl">Email</div><div><?= e($me['email']) ?></div></div>
<div class="panel" style="max-width:520px;border-color:#e6b3ae"><h2 class="out">Hapus akun</h2>
<p class="small">Semua dompet, transaksi, dan pembagian uang akan dihapus permanen.</p>
<form method="post" onsubmit="return confirm('Hapus akun dan semua data secara permanen?')"><?= csrf_field() ?>
<div class="mb-3"><label class="form-label">Masukkan password untuk konfirmasi</label><input type="password" name="password" class="form-control" required></div>
<button class="btn btn-danger">Hapus akun saya</button></form></div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
