<?php
session_start();
require_once __DIR__ . '/../config/database.php';

function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function rp($n) { return ($n < 0 ? '-' : '') . 'Rp' . number_format(abs((float)$n), 0, ',', '.'); }
function redirect($p) { header('Location: ' . BASE_URL . $p); exit; }
function flash($m, $t = 'success') { $_SESSION['flash'] = [$m, $t]; }
function uid() { return (int)($_SESSION['uid'] ?? 0); }
function require_login() { if (!uid()) redirect('auth/login.php'); }
function csrf_field() {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return '<input type="hidden" name="csrf" value="' . $_SESSION['csrf'] . '">';
}
function csrf_check() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
        http_response_code(419);
        die('Token keamanan tidak valid. Kembali dan muat ulang halaman.');
    }
}

// Saldo dompet = saldo awal + pemasukan - pengeluaran - transfer keluar + transfer masuk
function wallet_balances(PDO $pdo, int $u): array {
    $st = $pdo->prepare("SELECT w.*, w.initial_balance
      + COALESCE((SELECT SUM(CASE WHEN t.type='income' THEN t.amount ELSE -t.amount END)
                  FROM transactions t WHERE t.wallet_id = w.id), 0)
      + COALESCE((SELECT SUM(t.amount) FROM transactions t
                  WHERE t.type='transfer' AND t.to_wallet_id = w.id), 0) AS balance
      FROM wallets w WHERE w.user_id = ? ORDER BY w.name");
    $st->execute([$u]);
    return $st->fetchAll();
}
function sum_type(PDO $pdo, int $u, string $type, ?string $from = null): float {
    $sql = "SELECT COALESCE(SUM(amount),0) FROM transactions WHERE user_id=? AND type=?";
    $p = [$u, $type];
    if ($from) { $sql .= " AND trx_date >= ?"; $p[] = $from; }
    $st = $pdo->prepare($sql); $st->execute($p);
    return (float)$st->fetchColumn();
}
function budgets(PDO $pdo, int $u): array {
    $st = $pdo->prepare("SELECT pos, amount FROM budgets WHERE user_id=?");
    $st->execute([$u]);
    $r = ['kebutuhan' => 0, 'tabungan' => 0, 'darurat' => 0];
    foreach ($st->fetchAll() as $row) $r[$row['pos']] = (float)$row['amount'];
    return $r;
}
