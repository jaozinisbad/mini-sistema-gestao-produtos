<?php
declare(strict_types=1);

final class User
{
    public static function create(PDO $db, string $name, string $email, string $password): void
    {
        $salt = bin2hex(random_bytes(16));
        $hash = hash('sha256', $salt . $password);
        $stmt = $db->prepare('INSERT INTO users (name,email,password_salt,password_hash) VALUES (?,?,?,?)');
        $stmt->execute([$name, $email, $salt, $hash]);
    }

    public static function authenticate(PDO $db, string $email, string $password): ?array
    {
        $stmt = $db->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if (!$user || !hash_equals($user['password_hash'], hash('sha256', $user['password_salt'] . $password))) return null;
        return $user;
    }
}

final class Supplier
{
    public static function all(PDO $db): array { return $db->query('SELECT * FROM suppliers ORDER BY name')->fetchAll(); }
}

final class Product
{
    public static function all(PDO $db): array
    {
        return $db->query('SELECT p.*, s.name AS supplier_name FROM products p JOIN suppliers s ON s.id=p.supplier_id ORDER BY p.name')->fetchAll();
    }
}

final class Basket
{
    public static function current(PDO $db, int $userId): int
    {
        $stmt = $db->prepare('SELECT id FROM baskets WHERE user_id=? ORDER BY id DESC LIMIT 1');
        $stmt->execute([$userId]);
        $id = $stmt->fetchColumn();
        if ($id) return (int)$id;
        $stmt = $db->prepare('INSERT INTO baskets (user_id) VALUES (?) RETURNING id');
        $stmt->execute([$userId]);
        return (int)$stmt->fetchColumn();
    }

    public static function items(PDO $db, int $basketId): array
    {
        $stmt = $db->prepare('SELECT p.id,p.name,p.price,s.name AS supplier_name FROM basket_items bi JOIN products p ON p.id=bi.product_id JOIN suppliers s ON s.id=p.supplier_id WHERE bi.basket_id=? ORDER BY p.name');
        $stmt->execute([$basketId]);
        return $stmt->fetchAll();
    }
}
