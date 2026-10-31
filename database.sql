-- Student Finance Intelligence (SFI) - import lewat phpMyAdmin
CREATE DATABASE IF NOT EXISTS sfi_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sfi_db;

CREATE TABLE users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE wallets (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  name VARCHAR(60) NOT NULL,
  initial_balance DECIMAL(15,2) NOT NULL DEFAULT 0,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE categories (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  name VARCHAR(60) NOT NULL,
  type ENUM('income','expense') NOT NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Saldo dompet TIDAK disimpan; dihitung dari saldo awal + transaksi,
-- sehingga edit/hapus transaksi otomatis benar dan tidak ada hitungan ganda.
CREATE TABLE transactions (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  type ENUM('income','expense','transfer') NOT NULL,
  amount DECIMAL(15,2) NOT NULL,
  trx_date DATE NOT NULL,
  wallet_id INT UNSIGNED NOT NULL,
  to_wallet_id INT UNSIGNED NULL,
  category_id INT UNSIGNED NULL,
  note VARCHAR(255) NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (wallet_id) REFERENCES wallets(id) ON DELETE RESTRICT,
  FOREIGN KEY (to_wallet_id) REFERENCES wallets(id) ON DELETE RESTRICT,
  FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
  INDEX (user_id, trx_date)
) ENGINE=InnoDB;

-- Pembagian uang: satu baris per pos per pengguna
CREATE TABLE budgets (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  pos ENUM('kebutuhan','tabungan','darurat') NOT NULL,
  amount DECIMAL(15,2) NOT NULL DEFAULT 0,
  UNIQUE (user_id, pos),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;
