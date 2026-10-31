<?php
require_once __DIR__ . '/../includes/functions.php';
if (uid()) redirect('pages/dashboard.php');
$err = []; $name = $email = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $name = trim($_POST['name'] ?? ''); $email = strtolower(trim($_POST['email'] ?? ''));
    $pw = $_POST['password'] ?? ''; $pw2 = $_POST['password2'] ?? '';
    if ($name === '' || mb_strlen($name) > 100) $err[] = 'Nama wajib diisi (maksimal 100 karakter).';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $err[] = 'Format email tidak valid.';
    if (strlen($pw) < 8) $err[] = 'Password minimal 8 karakter.';
    if ($pw !== $pw2) $err[] = 'Konfirmasi password tidak sama.';
    if (!$err) {
        $c = $pdo->prepare('SELECT id FROM users WHERE email=?'); $c->execute([$email]);
        if ($c->fetch()) $err[] = 'Email sudah terdaftar.';
    }
    if (!$err) {
        $pdo->beginTransaction();
        try {
            $pdo->prepare('INSERT INTO users (name,email,password_hash) VALUES (?,?,?)')
                ->execute([$name, $email, password_hash($pw, PASSWORD_DEFAULT)]);
            $id = (int)$pdo->lastInsertId();
            $pdo->prepare("INSERT INTO wallets (user_id,name,initial_balance) VALUES (?, 'Cash', 0)")->execute([$id]);
            $ins = $pdo->prepare('INSERT INTO categories (user_id,name,type) VALUES (?,?,?)');
            foreach (['Uang saku', 'Gaji', 'Kerja sampingan', 'Pemasukan lainnya'] as $n) $ins->execute([$id, $n, 'income']);
            foreach (['Makanan', 'Transportasi', 'Kebutuhan kuliah', 'Hiburan', 'Kebutuhan lainnya'] as $n) $ins->execute([$id, $n, 'expense']);
            $b = $pdo->prepare('INSERT INTO budgets (user_id,pos,amount) VALUES (?,?,0)');
            foreach (['kebutuhan', 'tabungan', 'darurat'] as $p) $b->execute([$id, $p]);
            $pdo->commit();
            flash('Akun dibuat. Silakan masuk.');
            redirect('auth/login.php');
        } catch (Exception $ex) { $pdo->rollBack(); $err[] = 'Gagal membuat akun. Coba lagi.'; }
    }
}
?>
<!DOCTYPE html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Daftar - SFI</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="<?= BASE_URL ?>assets/css/style.css" rel="stylesheet"></head><body>
<div class="auth"><div class="panel">
<h1 class="fs-4 mb-3" style="color:var(--navy)">Buat akun SFI</h1>
<?php foreach ($err as $x): ?><div class="alert alert-danger py-2"><?= e($x) ?></div><?php endforeach; ?>
<form method="post"><?= csrf_field() ?>
<div class="mb-3"><label class="form-label">Nama</label><input name="name" class="form-control" value="<?= e($name) ?>" required></div>
<div class="mb-3"><label class="form-label">Email</label><input type="email" name="email" class="form-control" value="<?= e($email) ?>" required></div>
<div class="mb-3"><label class="form-label">Password</label><input type="password" name="password" class="form-control" required></div>
<div class="mb-3"><label class="form-label">Ulangi password</label><input type="password" name="password2" class="form-control" required></div>
<button class="btn btn-navy w-100">Daftar</button></form>
<p class="small mt-3 mb-0">Sudah punya akun? <a href="<?= BASE_URL ?>auth/login.php">Masuk</a></p>
</div></div></body></html>
