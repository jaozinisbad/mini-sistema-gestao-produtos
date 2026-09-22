<?php
declare(strict_types=1);

require dirname(__DIR__) . '/src/Database.php';

$suppliers = [
    ['name' => 'Aurora Distribuidora', 'email' => 'contato@aurora.example', 'phone' => '(11) 4000-1001'],
    ['name' => 'Verde Vale Alimentos', 'email' => 'vendas@verdevale.example', 'phone' => '(41) 4000-2002'],
    ['name' => 'Nova Era Tecnologia', 'email' => 'comercial@novaera.example', 'phone' => '(21) 4000-3003'],
];

$products = [
    ['Aurora Distribuidora', 'Caderno universitário', 'Caderno espiral com 10 matérias e 200 folhas.', '24.90'],
    ['Aurora Distribuidora', 'Caneta esferográfica azul', 'Pacote com 10 canetas de ponta média.', '18.50'],
    ['Aurora Distribuidora', 'Mochila básica', 'Mochila preta com dois compartimentos.', '89.90'],
    ['Verde Vale Alimentos', 'Café torrado 500 g', 'Café torrado e moído em embalagem de 500 g.', '21.90'],
    ['Verde Vale Alimentos', 'Chá de camomila', 'Caixa com 20 sachês de chá.', '12.75'],
    ['Verde Vale Alimentos', 'Mel orgânico 300 g', 'Pote de mel orgânico de 300 g.', '32.00'],
    ['Nova Era Tecnologia', 'Mouse sem fio', 'Mouse óptico com conexão USB e pilha inclusa.', '59.90'],
    ['Nova Era Tecnologia', 'Teclado compacto', 'Teclado USB com layout ABNT2.', '79.00'],
    ['Nova Era Tecnologia', 'Fone de ouvido', 'Fone com microfone e conector P2.', '69.90'],
];

$db = Database::connect();
$db->beginTransaction();
try {
    $findSupplier = $db->prepare('SELECT id FROM fornecedores WHERE name = ? ORDER BY id LIMIT 1');
    $insertSupplier = $db->prepare('INSERT INTO fornecedores (name, email, phone) VALUES (?, ?, ?) RETURNING id');
    $findProduct = $db->prepare('SELECT id FROM produtos WHERE supplier_id = ? AND name = ? LIMIT 1');
    $insertProduct = $db->prepare('INSERT INTO produtos (supplier_id, name, description, price) VALUES (?, ?, ?, ?)');
    $supplierIds = [];
    $addedSuppliers = 0;
    $addedProducts = 0;

    foreach ($suppliers as $supplier) {
        $findSupplier->execute([$supplier['name']]);
        $id = $findSupplier->fetchColumn();
        if (!$id) {
            $insertSupplier->execute([$supplier['name'], $supplier['email'], $supplier['phone']]);
            $id = $insertSupplier->fetchColumn();
            $addedSuppliers++;
        }
        $supplierIds[$supplier['name']] = (int)$id;
    }

    foreach ($products as [$supplierName, $name, $description, $price]) {
        $supplierId = $supplierIds[$supplierName];
        $findProduct->execute([$supplierId, $name]);
        if ($findProduct->fetchColumn()) continue;
        $insertProduct->execute([$supplierId, $name, $description, $price]);
        $addedProducts++;
    }

    $db->commit();
    echo "Adicionados: {$addedSuppliers} fornecedores e {$addedProducts} produtos." . PHP_EOL;
} catch (Throwable $error) {
    $db->rollBack();
    throw $error;
}
