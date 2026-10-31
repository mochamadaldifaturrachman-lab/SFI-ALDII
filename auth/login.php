<?php
require_once __DIR__ . '/../includes/functions.php';
if (uid()) redirect('pages/dashboard.php');
$err = ''; $email = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $email = strtolower(trim($_POST['email'] ?? ''));
    $st = $pdo->prepare('SELECT * FROM users WHERE email=?'); $st->execute([$email]);
    $u = $st->fetch();
    if ($u && password_verify($_POST['password'] ?? '', $u['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['uid'] = (int)$u['id']; $_SESSION['name'] = $u['name'];
        redirect('pages/dashboard.php');
    }
    $err = 'Email atau password salah.';
}
?>
<!DOCTYPE html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Masuk - SFI</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="<?= BASE_URL ?>assets/css/style.css" rel="stylesheet"></head><body>
<div class="auth"><div class="panel">
<h1 class="fs-4 mb-3" style="color:var(--navy)">Masuk ke SFI</h1>
<?php if (!empty($_SESSION['flash'])): $fl = $_SESSION['flash']; unset($_SESSION['flash']); ?><div class="alert alert-<?= $fl[1] ?> py-2"><?= e($fl[0]) ?></div><?php endif; ?>
<?php if ($err): ?><div class="alert alert-danger py-2"><?= e($err) ?></div><?php endif; ?>
<form method="post"><?= csrf_field() ?>
<div class="mb-3"><label class="form-label">Email</label><input type="email" name="email" class="form-control" value="<?= e($email) ?>" required></div>
<div class="mb-3"><label class="form-label">Password</label><input type="password" name="password" class="form-control" required></div>
<button class="btn btn-navy w-100">Masuk</button></form>
<p class="small mt-3 mb-0">Belum punya akun? <a href="<?= BASE_URL ?>auth/register.php">Daftar</a></p>
</div></div></body></html>
