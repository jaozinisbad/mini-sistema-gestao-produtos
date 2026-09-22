document.getElementById('catalog-form')?.addEventListener('submit', event => {
  if (!document.querySelector('.product-check:checked')) {
    event.preventDefault();
    alert('Selecione pelo menos um produto.');
  }
});

for (const button of document.querySelectorAll('.edit-supplier')) {
  button.addEventListener('click', () => {
    const data = JSON.parse(button.dataset.record);
    const form = document.getElementById('edit-supplier-form');
    for (const key of ['id', 'name', 'email', 'phone']) {
      document.getElementById(`supplier-${key}`).value = data[key] ?? '';
    }
    form.classList.remove('d-none');
    form.scrollIntoView({ behavior: 'smooth', block: 'center' });
  });
}

for (const button of document.querySelectorAll('.edit-product')) {
  button.addEventListener('click', () => {
    const data = JSON.parse(button.dataset.record);
    const form = document.getElementById('edit-product-form');
    for (const key of ['id', 'name', 'description', 'price']) {
      document.getElementById(`product-${key}`).value = data[key] ?? '';
    }
    document.getElementById('product-supplier').value = data.supplier_id;
    form.classList.remove('d-none');
    form.scrollIntoView({ behavior: 'smooth', block: 'center' });
  });
}

for (const button of document.querySelectorAll('.cancel-edit')) {
  button.addEventListener('click', () => button.closest('form').classList.add('d-none'));
}

for (const form of document.querySelectorAll('.ajax-form')) {
  form.addEventListener('submit', async event => {
    event.preventDefault();
    const formData = new FormData(form);
    const action = formData.get('action');
    if (action === 'delete_supplier' && !confirm('Excluir fornecedor?')) return;
    if (action === 'delete_product' && !confirm('Excluir produto?')) return;
    if (!form.reportValidity()) return;
    const message = document.getElementById('ajax-message');
    try {
      const response = await fetch('?page=update', {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
      });
      const result = await response.json();
      message.className = `alert ${result.ok ? 'alert-success' : 'alert-danger'}`;
      message.textContent = result.ok ? 'Alteração salva.' : result.message;
      if (!result.ok) return;

      if (action === 'save_supplier') {
        const id = String(formData.get('id'));
        const button = [...document.querySelectorAll('.edit-supplier')].find(item => String(JSON.parse(item.dataset.record).id) === id);
        if (button) {
          const updated = { ...JSON.parse(button.dataset.record), name: formData.get('name'), email: formData.get('email'), phone: formData.get('phone') };
          button.dataset.record = JSON.stringify(updated);
          button.closest('tr').cells[0].textContent = updated.name;
          const option = document.querySelector(`#product-supplier option[value="${id}"]`);
          if (option) option.textContent = updated.name;
        }
        form.classList.add('d-none');
      } else if (action === 'save_product') {
        const id = String(formData.get('id'));
        const button = [...document.querySelectorAll('.edit-product')].find(item => String(JSON.parse(item.dataset.record).id) === id);
        if (button) {
          const supplier = document.getElementById('product-supplier');
          const updated = { ...JSON.parse(button.dataset.record), name: formData.get('name'), description: formData.get('description'), price: formData.get('price'), supplier_id: formData.get('supplier_id'), supplier_name: supplier.selectedOptions[0].textContent };
          button.dataset.record = JSON.stringify(updated);
          const cells = button.closest('tr').cells;
          cells[0].textContent = updated.name;
          cells[1].textContent = updated.supplier_name;
          cells[2].textContent = `R$ ${Number(updated.price).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
        }
        form.classList.add('d-none');
      } else if (action === 'delete_supplier' || action === 'delete_product') {
        form.closest('tr').remove();
      } else if (action === 'remove_item') {
        form.closest('.border-bottom').remove();
        const badge = document.getElementById('basket-count');
        if (badge) badge.textContent = String(Math.max(0, Number(badge.textContent) - 1));
      }
    } catch {
      message.className = 'alert alert-danger';
      message.textContent = 'Falha de conexão.';
    }
  });
}
