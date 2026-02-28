<?= $this->extend('layouts/main') ?>

<?= $this->section('sidebar') ?>
<?= $this->include('partials/admin_sidebar') ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="<?= site_url('admin/payments') ?>">Pagos</a></li>
                <li class="breadcrumb-item active">Registrar Pago</li>
            </ol>
        </nav>
        <h4 class="mb-0">Registrar Pago</h4>
    </div>
    <a href="<?= site_url('admin/payments') ?>" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Volver
    </a>
</div>

<?php if (session()->getFlashdata('error')): ?>
<div class="alert alert-danger alert-dismissible fade show">
    <i class="bi bi-exclamation-circle me-2"></i>
    <?= session()->getFlashdata('error') ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<form action="<?= site_url('admin/payments') ?>" method="post" enctype="multipart/form-data">
    <?= csrf_field() ?>

    <div class="row">
        <div class="col-lg-8">
            <!-- Payment Info -->
            <div class="table-card mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-cash-stack me-2"></i>Informaci&oacute;n del Pago</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Acudiente <span class="text-danger">*</span></label>
                            <select class="form-select" name="acudiente_id" id="acudienteSelect" required>
                                <option value="">Seleccionar acudiente...</option>
                                <?php foreach ($acudientes as $a): ?>
                                <option value="<?= $a['id'] ?>">
                                    <?= esc($a['nombres'] . ' ' . $a['apellidos']) ?> (<?= esc($a['email']) ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Fecha de Pago <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="fecha_pago" value="<?= date('Y-m-d') ?>" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">M&eacute;todo de Pago <span class="text-danger">*</span></label>
                            <select class="form-select" name="metodo_pago" required>
                                <option value="transferencia">Transferencia</option>
                                <option value="efectivo">Efectivo</option>
                                <option value="nequi">Nequi</option>
                                <option value="daviplata">Daviplata</option>
                                <option value="otro">Otro</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Banco</label>
                            <input type="text" class="form-control" name="banco" placeholder="Nombre del banco">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Referencia</label>
                            <input type="text" class="form-control" name="referencia_banco" placeholder="N&uacute;mero de referencia">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Observaciones</label>
                        <textarea class="form-control" name="observaciones" rows="2" placeholder="Notas adicionales..."></textarea>
                    </div>
                    <div class="mb-0">
                        <label class="form-label">Comprobante</label>
                        <input type="file" class="form-control" name="comprobante" accept="image/*,.pdf">
                        <small class="text-muted">Imagen o PDF del comprobante de pago</small>
                    </div>
                </div>
            </div>

            <!-- Cargos pendientes -->
            <div class="table-card mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-list-check me-2"></i>Cargos Pendientes</h5>
                </div>
                <div class="card-body p-0" id="cargosContainer">
                    <div class="p-4 text-center text-muted" id="cargosPlaceholder">
                        <i class="bi bi-person-plus fs-1 d-block mb-2"></i>
                        Seleccione un acudiente para ver los cargos pendientes
                    </div>
                    <div class="table-responsive d-none" id="cargosTable">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:40px"></th>
                                    <th>Estudiante</th>
                                    <th>Concepto</th>
                                    <th class="text-end">Saldo</th>
                                    <th style="width:150px">Valor a Pagar</th>
                                </tr>
                            </thead>
                            <tbody id="cargosBody">
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <td colspan="4" class="text-end fw-bold">Total a Pagar:</td>
                                    <td class="fw-bold text-success" id="totalPagar">$0</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-success btn-lg" id="btnSubmit" disabled>
                    <i class="bi bi-check-lg me-2"></i>Registrar Pago
                </button>
                <a href="<?= site_url('admin/payments') ?>" class="btn btn-outline-secondary">
                    Cancelar
                </a>
            </div>
        </div>
    </div>
</form>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
const acudienteSelect = document.getElementById('acudienteSelect');
const cargosPlaceholder = document.getElementById('cargosPlaceholder');
const cargosTable = document.getElementById('cargosTable');
const cargosBody = document.getElementById('cargosBody');
const totalPagar = document.getElementById('totalPagar');
const btnSubmit = document.getElementById('btnSubmit');

acudienteSelect.addEventListener('change', function() {
    const acudienteId = this.value;
    if (!acudienteId) {
        cargosPlaceholder.classList.remove('d-none');
        cargosTable.classList.add('d-none');
        return;
    }

    fetch('<?= site_url('admin/payments/cargos-pendientes') ?>?acudiente_id=' + acudienteId)
        .then(r => r.json())
        .then(cargos => {
            cargosBody.innerHTML = '';

            if (cargos.length === 0) {
                cargosPlaceholder.innerHTML = '<i class="bi bi-check-circle fs-1 d-block mb-2 text-success"></i>No hay cargos pendientes para este acudiente';
                cargosPlaceholder.classList.remove('d-none');
                cargosTable.classList.add('d-none');
                return;
            }

            cargosPlaceholder.classList.add('d-none');
            cargosTable.classList.remove('d-none');

            cargos.forEach(cargo => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>
                        <input class="form-check-input cargo-check" type="checkbox"
                               name="cargos[]" value="${cargo.id}" data-saldo="${cargo.saldo_pendiente}">
                    </td>
                    <td>${cargo.estudiante_nombre}</td>
                    <td><span class="badge bg-secondary">${cargo.concepto_codigo}</span> ${cargo.concepto_nombre}</td>
                    <td class="text-end">$${parseInt(cargo.saldo_pendiente).toLocaleString('es-CO')}</td>
                    <td>
                        <input type="number" class="form-control form-control-sm valor-pago"
                               name="valores[${cargo.id}]" value="${cargo.saldo_pendiente}"
                               min="0" max="${cargo.saldo_pendiente}" step="100" disabled>
                    </td>
                `;
                cargosBody.appendChild(tr);
            });

            // Event listeners
            document.querySelectorAll('.cargo-check').forEach(cb => {
                cb.addEventListener('change', function() {
                    const input = this.closest('tr').querySelector('.valor-pago');
                    input.disabled = !this.checked;
                    if (!this.checked) input.value = this.dataset.saldo;
                    updateTotal();
                });
            });

            document.querySelectorAll('.valor-pago').forEach(input => {
                input.addEventListener('input', updateTotal);
            });
        });
});

function updateTotal() {
    let total = 0;
    document.querySelectorAll('.cargo-check:checked').forEach(cb => {
        const input = cb.closest('tr').querySelector('.valor-pago');
        total += parseFloat(input.value) || 0;
    });
    totalPagar.textContent = '$' + parseInt(total).toLocaleString('es-CO');
    btnSubmit.disabled = total <= 0;
}
</script>
<?= $this->endSection() ?>
