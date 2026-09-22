<h1 class="h3 mb-3">Cadastros</h1>
<p class="text-muted">Inclua fornecedores e produtos novos no banco de dados.</p>

<div class="row g-4">
  <div class="col-lg-5">
    <div class="card p-4 h-100">
      <h2 class="h5">Novo fornecedor</h2>
      <form method="post">
        <input type="hidden" name="csrf" value="<?= e(csrf()) ?>">
        <input type="hidden" name="action" value="save_supplier">
        <label class="form-label" for="supplier-name">Nome</label>
        <input class="form-control mb-2" id="supplier-name" name="name" required>
        <label class="form-label" for="supplier-email">E-mail</label>
        <input class="form-control mb-2" id="supplier-email" name="email" type="email">
        <label class="form-label" for="supplier-phone">Telefone</label>
        <input class="form-control mb-3" id="supplier-phone" name="phone">
        <button class="btn btn-primary">Cadastrar fornecedor</button>
      </form>
    </div>
  </div>
  <div class="col-lg-7">
    <div class="card p-4 h-100">
      <h2 class="h5">Novo produto</h2>
      <form method="post">
        <input type="hidden" name="csrf" value="<?= e(csrf()) ?>">
        <input type="hidden" name="action" value="save_product">
        <label class="form-label" for="product-supplier">Fornecedor</label>
        <select class="form-select mb-2" id="product-supplier" name="supplier_id" required>
          <option value="">Selecione</option>
          <?php foreach ($suppliers as $supplier): ?>
            <option value="<?= (int)$supplier['id'] ?>"><?= e($supplier['name']) ?></option>
          <?php endforeach; ?>
        </select>
        <label class="form-label" for="product-name">Nome</label>
        <input class="form-control mb-2" id="product-name" name="name" required>
        <label class="form-label" for="product-description">Descrição</label>
        <textarea class="form-control mb-2" id="product-description" name="description"></textarea>
        <label class="form-label" for="product-price">Preço (R$)</label>
        <input class="form-control mb-3" id="product-price" name="price" type="number" min="0" step="0.01" required>
        <button class="btn btn-primary" <?= !$suppliers ? 'disabled' : '' ?>>Cadastrar produto</button>
        <?php if (!$suppliers): ?><p class="small text-muted mt-2">Cadastre um fornecedor primeiro.</p><?php endif; ?>
      </form>
    </div>
  </div>
</div>

<div class="row g-4 mt-1">
  <div class="col-lg-5"><div class="card p-4 h-100"><h2 class="h5">Fornecedores cadastrados</h2><p class="mb-0"><?= count($suppliers) ?> fornecedor(es)</p></div></div>
  <div class="col-lg-7"><div class="card p-4 h-100"><h2 class="h5">Produtos cadastrados</h2><p class="mb-0"><?= count($products) ?> produto(s) · <a href="?page=update">Editar registros</a></p></div></div>
</div>
