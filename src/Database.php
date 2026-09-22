<?php
declare(strict_types=1);

final class Database
{
    private static ?PDO $connection = null;

    public static function connect(): PDO
    {
        if (self::$connection !== null) return self::$connection;
        $c = require dirname(__DIR__) . '/config.php';
        foreach (['host', 'port', 'name', 'user'] as $key) {
            if (!preg_match('/^[a-zA-Z0-9_.-]+$/', (string)$c[$key])) {
                throw new RuntimeException('Configuração inválida: ' . $key);
            }
        }
        $base = "mysql:host={$c['host']};port={$c['port']};charset=utf8mb4";
        $options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::ATTR_EMULATE_PREPARES => false];
        $pdo = new PDO($base, $c['user'], $c['password'], $options);
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$c['name']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE `{$c['name']}`");
        $pdo->exec('CREATE TABLE IF NOT EXISTS users (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, name VARCHAR(120) NOT NULL, email VARCHAR(190) NOT NULL UNIQUE, password_salt CHAR(32) NOT NULL, password_hash CHAR(64) NOT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB');
        $pdo->exec('CREATE TABLE IF NOT EXISTS suppliers (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, name VARCHAR(160) NOT NULL, email VARCHAR(190) DEFAULT NULL, phone VARCHAR(30) DEFAULT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB');
        $pdo->exec('CREATE TABLE IF NOT EXISTS products (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, supplier_id INT UNSIGNED NOT NULL, name VARCHAR(160) NOT NULL, description TEXT DEFAULT NULL, price DECIMAL(10,2) NOT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, CONSTRAINT fk_product_supplier FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE RESTRICT, CONSTRAINT ck_price CHECK (price >= 0)) ENGINE=InnoDB');
        $pdo->exec('CREATE TABLE IF NOT EXISTS baskets (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, user_id INT UNSIGNED NOT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, CONSTRAINT fk_basket_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE) ENGINE=InnoDB');
        $pdo->exec('CREATE TABLE IF NOT EXISTS basket_items (basket_id INT UNSIGNED NOT NULL, product_id INT UNSIGNED NOT NULL, PRIMARY KEY (basket_id, product_id), CONSTRAINT fk_item_basket FOREIGN KEY (basket_id) REFERENCES baskets(id) ON DELETE CASCADE, CONSTRAINT fk_item_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE) ENGINE=InnoDB');
        return self::$connection = $pdo;
    }
}
