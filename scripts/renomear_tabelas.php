<?php
declare(strict_types=1);

// Migração única do esquema inicial em inglês para nomes de tabelas em português.
$config = require dirname(__DIR__) . '/config.php';
$dsn = "pgsql:host={$config['host']};port={$config['port']};dbname={$config['name']};sslmode={$config['sslmode']}";
$db = new PDO($dsn, $config['user'], $config['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$names = [
    'users' => 'usuarios',
    'suppliers' => 'fornecedores',
    'products' => 'produtos',
    'baskets' => 'cestas',
    'basket_items' => 'itens_cesta',
];

$db->beginTransaction();
try {
    foreach ($names as $old => $new) {
        $exists = $db->prepare("SELECT to_regclass('public.' || ?) IS NOT NULL");
        $exists->execute([$old]);
        $oldExists = in_array($exists->fetchColumn(), [true, 't', '1', 1], true);
        $exists->execute([$new]);
        $newExists = in_array($exists->fetchColumn(), [true, 't', '1', 1], true);
        if (!$oldExists && $newExists) continue;
        if (!$oldExists || $newExists) throw new RuntimeException("Estado inesperado para {$old}/{$new}; migração cancelada.");
        $before = (int)$db->query("SELECT COUNT(*) FROM public.\"{$old}\"")->fetchColumn();
        $db->exec("ALTER TABLE public.\"{$old}\" RENAME TO \"{$new}\"");
        $after = (int)$db->query("SELECT COUNT(*) FROM public.\"{$new}\"")->fetchColumn();
        if ($before !== $after) throw new RuntimeException("Contagem mudou em {$new}; migração cancelada.");
        echo "{$old} → {$new}: {$after} registro(s) preservado(s)." . PHP_EOL;
    }
    $db->commit();
} catch (Throwable $error) {
    $db->rollBack();
    throw $error;
}
