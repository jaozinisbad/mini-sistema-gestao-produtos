<h1 class="h3 mb-3">Atualização com AJAX</h1>
<p class="text-muted">Escolha um registro para editar. As alterações são enviadas sem recarregar a página; a lista é atualizada após a confirmação.</p>
<div id="ajax-message" role="status" aria-live="polite"></div>

<div class="row g-4">
  <div class="col-lg-5">
    <div class="card p-4 h-100">
      <h2 class="h5">Fornecedores</h2>
      <div class="table-responsive"><table class="table">
        <thead><tr><th>Nome</th><th>Ações</th></tr></thead><tbody>
        <?php foreach ($suppliers as $supplier): ?>
          <tr><td><?= e($supplier['name']) ?></td><td class="text-nowrap">
            <button class="btn btn-sm btn-outline-primary edit-supplier" data-record="<?= e(json_encode($supplier)) ?>">Editar</button>
            <form class="ajax-form d-inline" method="post">
              <input type="hidden" name="csrf" value="<?= e(csrf()) ?>"><input type="hidden" name="action" value="delete_supplier"><input type="hidden" name="id" value="<?= (int)$supplier['id'] ?>">
              <button class="btn btn-sm btn-outline-danger">Excluir</button>
            </form>
          </td></tr>
        <?php endforeach; ?>
        </tbody></table></div>
      <form class="ajax-form d-none border-top pt-3" id="edit-supplier-form" method="post">
        <h3 class="h6">Editar fornecedor</h3>
        <input type="hidden" name="csrf" value="<?= e(csrf()) ?>"><input type="hidden" name="action" value="save_supplier"><input type="hidden" name="id" id="supplier-id">
        <label class="form-label" for="supplier-name">Nome</label><input class="form-control mb-2" name="name" id="supplier-name" required>
        <label class="form-label" for="supplier-email">E-mail</label><input class="form-control mb-2" name="email" id="supplier-email" type="email">
        <label class="form-label" for="supplier-phone">Telefone</label><input class="form-control mb-3" name="phone" id="supplier-phone">
        <button class="btn btn-primary">Salvar alterações</button><button type="button" class="btn btn-outline-secondary cancel-edit">Cancelar</button>
      </form>
    </div>
  </div>

  <div class="col-lg-7">
    <div class="card p-4 h-100">
      <h2 class="h5">Produtos</h2>
      <div class="table-responsive"><table class="table">
        <thead><tr><th>Nome</th><th>Fornecedor</th><th>Preço</th><th>Ações</th></tr></thead><tbody>
        <?php foreach ($products as $product): ?>
          <tr><td><?= e($product['name']) ?></td><td><?= e($product['supplier_name']) ?></td><td>R$ <?= number_format((float)$product['price'], 2, ',', '.') ?></td><td class="text-nowrap">
            <button class="btn btn-sm btn-outline-primary edit-product" data-record="<?= e(json_encode($product)) ?>">Editar</button>
            <form class="ajax-form d-inline" method="post">
              <input type="hidden" name="csrf" value="<?= e(csrf()) ?>"><input type="hidden" name="action" value="delete_product"><input type="hidden" name="id" value="<?= (int)$product['id'] ?>">
              <button class="btn btn-sm btn-outline-danger">Excluir</button>
            </form>
          </td></tr>
        <?php endforeach; ?>
        </tbody></table></div>
      <form class="ajax-form d-none border-top pt-3" id="edit-product-form" method="post">
        <h3 class="h6">Editar produto</h3>
        <input type="hidden" name="csrf" value="<?= e(csrf()) ?>"><input type="hidden" name="action" value="save_product"><input type="hidden" name="id" id="product-id">
        <label class="form-label" for="product-supplier">Fornecedor</label><select class="form-select mb-2" name="supplier_id" id="product-supplier" required>
          <option value="">Selecione</option><?php foreach ($suppliers as $supplier): ?><option value="<?= (int)$supplier['id'] ?>"><?= e($supplier['name']) ?></option><?php endforeach; ?>
        </select>
        <label class="form-label" for="product-name">Nome</label><input class="form-control mb-2" name="name" id="product-name" required>
        <label class="form-label" for="product-description">Descrição</label><textarea class="form-control mb-2" name="description" id="product-description"></textarea>
        <label class="form-label" for="product-price">Preço (R$)</label><input class="form-control mb-3" name="price" id="product-price" type="number" min="0" step="0.01" required>
        <button class="btn btn-primary">Salvar alterações</button><button type="button" class="btn btn-outline-secondary cancel-edit">Cancelar</button>
      </form>
    </div>
  </div>
</div>

<div class="card p-4 mt-4">
  <h2 class="h5">Cesta</h2>
  <p>Remova itens da cesta sem recarregar a página.</p>
  <?php if (!$items): ?><p class="text-muted">A cesta está vazia.</p><?php endif; ?>
  <?php foreach ($items as $item): ?>
    <div class="d-flex justify-content-between align-items-center border-bottom py-2">
      <span><?= e($item['name']) ?> · R$ <?= number_format((float)$item['price'], 2, ',', '.') ?></span>
      <form class="ajax-form" method="post">
        <input type="hidden" name="csrf" value="<?= e(csrf()) ?>"><input type="hidden" name="action" value="remove_item"><input type="hidden" name="product_id" value="<?= (int)$item['id'] ?>">
        <button class="btn btn-sm btn-outline-danger">Remover</button>
      </form>
    </div>
  <?php endforeach; ?>
  <a href="?page=catalog" class="mt-3">Adicionar produtos à cesta</a>
</div>
