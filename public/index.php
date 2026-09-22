<?php
declare(strict_types=1);
session_start();
require dirname(__DIR__) . '/src/Database.php';
require dirname(__DIR__) . '/src/Models.php';

function e(mixed $value): string { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
function redirect(string $page): never { header('Location: ?page=' . urlencode($page)); exit; }
function fail(string $message, int $status = 400): never {
    http_response_code($status);
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => false, 'message' => $message]);
    } else {
        $_SESSION['flash'] = $message;
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '?page=dashboard'));
    }
    exit;
}
function csrf(): string { return $_SESSION['csrf'] ??= bin2hex(random_bytes(32)); }
function field(string $name): string { return trim((string)($_POST[$name] ?? '')); }
function positiveId(string $value): int { $id = filter_var($value, FILTER_VALIDATE_INT); return $id && $id > 0 ? (int)$id : 0; }

try { $db = Database::connect(); } catch (Throwable $error) {
    http_response_code(500);
    exit('Não foi possível conectar ao PostgreSQL. Confira config.php e as instruções no README.');
}
$page = (string)($_GET['page'] ?? (isset($_SESSION['user_id']) ? 'dashboard' : 'login'));
$action = (string)($_POST['action'] ?? '');

if ($action !== '') {
    if (!hash_equals(csrf(), (string)($_POST['csrf'] ?? ''))) fail('Sessão expirada. Recarregue a página.', 419);
    if ($action === 'register') {
        $name = field('name'); $email = strtolower(field('email')); $password = (string)($_POST['password'] ?? '');
        if (mb_strlen($name) < 2 || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 8) fail('Informe nome, e-mail válido e senha de ao menos 8 caracteres.');
        try { User::create($db, $name, $email, $password); } catch (PDOException $error) { fail('E-mail já cadastrado.'); }
        $_SESSION['flash'] = 'Conta criada. Faça login.'; redirect('login');
    }
    if ($action === 'login') {
        $user = User::authenticate($db, strtolower(field('email')), (string)($_POST['password'] ?? ''));
        if (!$user) fail('E-mail ou senha incorretos.', 401);
        session_regenerate_id(true); $_SESSION['user_id'] = (int)$user['id']; $_SESSION['user_name'] = $user['name']; redirect('dashboard');
    }
    if ($action === 'logout') { $_SESSION = []; session_destroy(); redirect('login'); }
    if (!isset($_SESSION['user_id'])) fail('Faça login para continuar.', 401);
    try {
        if ($action === 'save_supplier') {
            $id = positiveId(field('id')); $name = field('name'); $email = field('email'); $phone = field('phone');
            if ($name === '' || ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL))) fail('Informe um nome e um e-mail válido.');
            if ($id) { $stmt = $db->prepare('UPDATE suppliers SET name=?,email=?,phone=? WHERE id=?'); $stmt->execute([$name,$email ?: null,$phone ?: null,$id]); }
            else { $stmt = $db->prepare('INSERT INTO suppliers (name,email,phone) VALUES (?,?,?)'); $stmt->execute([$name,$email ?: null,$phone ?: null]); }
        } elseif ($action === 'delete_supplier') {
            $stmt = $db->prepare('DELETE FROM suppliers WHERE id=?'); $stmt->execute([positiveId(field('id'))]);
        } elseif ($action === 'save_product') {
            $id = positiveId(field('id')); $supplier = positiveId(field('supplier_id')); $name = field('name'); $description = field('description'); $price = field('price');
            if (!$supplier || $name === '' || !preg_match('/^\d{1,8}(\.\d{1,2})?$/', $price)) fail('Informe fornecedor, nome e preço válido.');
            if ($id) { $stmt = $db->prepare('UPDATE products SET supplier_id=?,name=?,description=?,price=? WHERE id=?'); $stmt->execute([$supplier,$name,$description ?: null,$price,$id]); }
            else { $stmt = $db->prepare('INSERT INTO products (supplier_id,name,description,price) VALUES (?,?,?,?)'); $stmt->execute([$supplier,$name,$description ?: null,$price]); }
        } elseif ($action === 'delete_product') {
            $stmt = $db->prepare('DELETE FROM products WHERE id=?'); $stmt->execute([positiveId(field('id'))]);
        } elseif ($action === 'add_items') {
            $ids = $_POST['product_ids'] ?? [];
            if (!is_array($ids) || count($ids) === 0) fail('Selecione pelo menos um produto.');
            $basketId = Basket::current($db, (int)$_SESSION['user_id']);
            $stmt = $db->prepare('INSERT INTO basket_items (basket_id,product_id) SELECT ?,id FROM products WHERE id=? ON CONFLICT DO NOTHING');
            foreach (array_unique($ids) as $raw) { $id = positiveId((string)$raw); if ($id) $stmt->execute([$basketId,$id]); }
            redirect('basket');
        } elseif ($action === 'remove_item') {
            $basketId = Basket::current($db, (int)$_SESSION['user_id']);
            $stmt = $db->prepare('DELETE FROM basket_items WHERE basket_id=? AND product_id=?');
            $stmt->execute([$basketId,positiveId(field('product_id'))]);
            if (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') !== 'XMLHttpRequest') redirect('basket');
        } else fail('Ação desconhecida.');
    } catch (PDOException $error) { fail('Operação não concluída. Verifique os dados e os relacionamentos.'); }
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
        header('Content-Type: application/json; charset=utf-8'); echo json_encode(['ok'=>true]); exit;
    }
    $_SESSION['flash'] = 'Dados salvos.'; redirect('manage');
}

if (!isset($_SESSION['user_id']) && !in_array($page, ['login','register'], true)) redirect('login');
if (isset($_SESSION['user_id']) && in_array($page, ['login','register'], true)) redirect('dashboard');
$suppliers = isset($_SESSION['user_id']) ? Supplier::all($db) : [];
$products = isset($_SESSION['user_id']) ? Product::all($db) : [];
$items = isset($_SESSION['user_id']) ? Basket::items($db, Basket::current($db, (int)$_SESSION['user_id'])) : [];
$flash = $_SESSION['flash'] ?? null; unset($_SESSION['flash']);
?><!doctype html>
<html lang="pt-BR"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Gestão de Produtos</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>body{background:#f4f6fa}.brand{font-weight:800;letter-spacing:-.04em}.card{border:0;box-shadow:0 8px 24px #19304e12}.table td{vertical-align:middle}.hero{background:linear-gradient(120deg,#0b2545,#1767ae);color:#fff;border-radius:1rem}.price{font-variant-numeric:tabular-nums}</style></head><body>
<nav class="navbar navbar-expand-lg bg-white border-bottom"><div class="container"><a class="navbar-brand brand" href="?page=dashboard">Gestão de Produtos</a><?php if (isset($_SESSION['user_id'])): ?><div class="d-flex gap-2 align-items-center flex-wrap"><a class="btn btn-sm btn-outline-primary" href="?page=dashboard">Início</a><a class="btn btn-sm btn-outline-primary" href="?page=manage">Cadastros</a><a class="btn btn-sm btn-outline-primary" href="?page=update">Atualização AJAX</a><a class="btn btn-sm btn-outline-primary" href="?page=catalog">Produtos</a><a class="btn btn-sm btn-primary" href="?page=basket">Cesta (<span id="basket-count"><?= count($items) ?></span>)</a><form method="post"><input type="hidden" name="csrf" value="<?= e(csrf()) ?>"><input type="hidden" name="action" value="logout"><button class="btn btn-sm btn-outline-danger">Sair</button></form></div><?php endif; ?></div></nav>
<main class="container py-4"><?php if ($flash): ?><div class="alert alert-info"><?= e($flash) ?></div><?php endif; ?>
<?php if ($page === 'login' || $page === 'register'): ?><div class="row justify-content-center mt-5"><div class="col-md-5"><div class="card p-4"><h1 class="h3 mb-3"><?= $page === 'login' ? 'Entrar' : 'Criar conta' ?></h1><form method="post"><input type="hidden" name="csrf" value="<?= e(csrf()) ?>"><input type="hidden" name="action" value="<?= $page === 'login' ? 'login' : 'register' ?>"><?php if ($page === 'register'): ?><label class="form-label">Nome</label><input class="form-control mb-3" name="name" required minlength="2"><?php endif; ?><label class="form-label">E-mail</label><input class="form-control mb-3" name="email" type="email" required><label class="form-label">Senha</label><input class="form-control mb-3" name="password" type="password" required <?= $page === 'register' ? 'minlength="8"' : '' ?>><button class="btn btn-primary w-100"><?= $page === 'login' ? 'Entrar' : 'Cadastrar' ?></button></form><a class="mt-3" href="?page=<?= $page === 'login' ? 'register' : 'login' ?>"><?= $page === 'login' ? 'Criar uma conta' : 'Já tenho conta' ?></a></div></div></div>
<?php elseif ($page === 'dashboard'): ?><div class="hero p-5 mb-4"><h1>Olá, <?= e($_SESSION['user_name']) ?>!</h1><p class="mb-0">Gerencie fornecedores, produtos e sua cesta em um só lugar.</p></div><div class="row g-3"><div class="col-md-4"><div class="card p-4"><h2 class="h5">Fornecedores</h2><p><?= count($suppliers) ?> cadastrados</p><a href="?page=manage">Gerenciar</a></div></div><div class="col-md-4"><div class="card p-4"><h2 class="h5">Produtos</h2><p><?= count($products) ?> cadastrados</p><a href="?page=catalog">Escolher produtos</a></div></div><div class="col-md-4"><div class="card p-4"><h2 class="h5">Cesta</h2><p><?= count($items) ?> selecionados</p><a href="?page=basket">Ver cesta</a></div></div></div>
<?php elseif ($page === 'manage'): ?><?php require dirname(__DIR__) . '/views/cadastros.php'; ?>
<?php elseif ($page === 'update'): ?><?php require dirname(__DIR__) . '/views/atualizacao.php'; ?>
<?php elseif ($page === 'catalog'): ?><h1 class="h3">Selecionar produtos</h1><p>Escolha um ou mais produtos. Cada produto entra uma vez na cesta.</p><form method="post" id="catalog-form"><input type="hidden" name="csrf" value="<?= e(csrf()) ?>"><input type="hidden" name="action" value="add_items"><div class="row g-3"><?php foreach ($products as $p): ?><div class="col-md-4"><label class="card p-4 h-100"><span class="d-flex justify-content-between"><strong><?= e($p['name']) ?></strong><input class="form-check-input product-check" type="checkbox" name="product_ids[]" value="<?= (int)$p['id'] ?>"></span><span class="text-muted small mt-2"><?= e($p['supplier_name']) ?></span><span class="mt-2"><?= e($p['description']) ?></span><strong class="mt-auto pt-3">R$ <?= number_format((float)$p['price'],2,',','.') ?></strong></label></div><?php endforeach; ?></div><?php if (!$products): ?><div class="alert alert-info">Nenhum produto cadastrado ainda.</div><?php endif; ?><button class="btn btn-primary mt-4" <?= !$products ? 'disabled' : '' ?>>Adicionar à cesta</button></form>
<?php elseif ($page === 'basket'): ?><h1 class="h3 mb-3">Minha cesta</h1><div class="card p-4"><div class="table-responsive"><table class="table"><thead><tr><th>Produto</th><th>Fornecedor</th><th>Preço</th><th></th></tr></thead><tbody><?php foreach ($items as $item): ?><tr><td><?= e($item['name']) ?></td><td><?= e($item['supplier_name']) ?></td><td>R$ <?= number_format((float)$item['price'],2,',','.') ?></td><td><form method="post"><input type="hidden" name="csrf" value="<?= e(csrf()) ?>"><input type="hidden" name="action" value="remove_item"><input type="hidden" name="product_id" value="<?= (int)$item['id'] ?>"><button class="btn btn-sm btn-outline-danger">Remover</button></form></td></tr><?php endforeach; ?></tbody></table></div><?php if (!$items): ?><p>Sua cesta está vazia. <a href="?page=catalog">Escolher produtos</a></p><?php endif; ?><div class="border-top pt-3 d-flex justify-content-between"><strong><?= count($items) ?> produto(s)</strong><strong>Total: R$ <?= number_format(array_sum(array_map(fn($i) => (float)$i['price'], $items)),2,',','.') ?></strong></div></div>
<?php else: http_response_code(404); ?><h1>Página não encontrada</h1><?php endif; ?></main>
<script src="app.js"></script></body></html>

