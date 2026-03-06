<?= $this->extend('layouts/main') ?>

<?= $this->section('sidebar') ?>
<?= $this->include('acudiente/partials/sidebar') ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<a href="<?= site_url('acudiente/pagos') ?>" class="btn btn-sm btn-outline-secondary mb-3">
    <i class="bi bi-arrow-left me-1"></i>Volver a Estado de Cuenta
</a>

<?php if (empty($cargos_pendientes)): ?>
    <div class="table-card">
        <div class="card-body p-5 text-center text-muted">
            <i class="bi bi-check-circle fs-1 d-block mb-3 text-success"></i>
            <h5>No tiene cargos pendientes</h5>
            <p>No hay nada que pagar en este momento.</p>
        </div>
    </div>
<?php else: ?>
    <form action="<?= site_url('acudiente/pagos/guardar') ?>" method="post" enctype="multipart/form-data">
        <?= csrf_field() ?>

        <div class="row g-4">
            <!-- Select Cargos -->
            <div class="col-lg-7">
                <div class="table-card">
                    <div class="card-header">
                        <i class="bi bi-check2-square me-2"></i>Seleccione los cargos a pagar
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width:40px">
                                            <input type="checkbox" class="form-check-input" id="selectAll">
                                        </th>
                                        <th>Estudiante / Concepto</th>
                                        <th>Saldo</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($cargos_pendientes as $c): ?>
                                    <tr>
                                        <td>
                                            <input type="checkbox" class="form-check-input cargo-check"
                                                   name="cargos[]" value="<?= $c['id'] ?>"
                                                   data-saldo="<?= $c['saldo_pendiente'] ?>">
                                        </td>
                                        <td>
                                            <strong><?= esc($c['estudiante_nombre']) ?></strong>
                                            <div class="small text-muted"><?= esc($c['concepto_nombre']) ?></div>
                                        </td>
                                        <td class="text-danger fw-semibold">
                                            $<?= number_format($c['saldo_pendiente'], 0, ',', '.') ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer bg-light">
                        <div class="d-flex justify-content-between align-items-center">
                            <span>Total seleccionado:</span>
                            <strong class="fs-5 text-primary" id="totalSeleccionado">$0</strong>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Form -->
            <div class="col-lg-5">
                <div class="table-card">
                    <div class="card-header"><i class="bi bi-credit-card me-2"></i>Datos del Pago</div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Valor total pagado <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" name="valor_total" id="valorTotal" class="form-control"
                                       required min="1" step="1" placeholder="0"
                                       value="<?= old('valor_total') ?>">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Metodo de pago <span class="text-danger">*</span></label>
                            <select name="metodo_pago" class="form-select" required>
                                <option value="">Seleccione...</option>
                                <option value="transferencia" <?= old('metodo_pago') === 'transferencia' ? 'selected' : '' ?>>Transferencia</option>
                                <option value="efectivo" <?= old('metodo_pago') === 'efectivo' ? 'selected' : '' ?>>Efectivo</option>
                                <option value="nequi" <?= old('metodo_pago') === 'nequi' ? 'selected' : '' ?>>Nequi</option>
                                <option value="daviplata" <?= old('metodo_pago') === 'daviplata' ? 'selected' : '' ?>>Daviplata</option>
                                <option value="otro" <?= old('metodo_pago') === 'otro' ? 'selected' : '' ?>>Otro</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Fecha del pago <span class="text-danger">*</span></label>
                            <input type="date" name="fecha_pago" class="form-control" required
                                   value="<?= old('fecha_pago') ?: date('Y-m-d') ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Banco</label>
                            <input type="text" name="banco" class="form-control" placeholder="Ej: Bancolombia"
                                   value="<?= old('banco') ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Referencia / No. Transaccion</label>
                            <input type="text" name="referencia_banco" class="form-control"
                                   value="<?= old('referencia_banco') ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Comprobante de pago <span class="text-danger">*</span></label>
                            <input type="file" name="comprobante" class="form-control" required
                                   accept="image/*" capture="environment">
                            <div class="form-text">Foto o imagen del comprobante (max 5MB)</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Observaciones</label>
                            <textarea name="observaciones" class="form-control" rows="2"><?= old('observaciones') ?></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary w-100" id="btnSubmit">
                            <i class="bi bi-send me-1"></i>Enviar Pago
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
<?php endif; ?>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
(function() {
    var checkAll = document.getElementById('selectAll');
    var checks = document.querySelectorAll('.cargo-check');
    var totalEl = document.getElementById('totalSeleccionado');
    var valorInput = document.getElementById('valorTotal');

    function updateTotal() {
        var total = 0;
        checks.forEach(function(cb) {
            if (cb.checked) total += parseFloat(cb.dataset.saldo || 0);
        });
        totalEl.textContent = '$' + total.toLocaleString('es-CO', {maximumFractionDigits: 0});
        if (valorInput && !valorInput.dataset.manual) {
            valorInput.value = total > 0 ? Math.round(total) : '';
        }
    }

    if (checkAll) {
        checkAll.addEventListener('change', function() {
            checks.forEach(function(cb) { cb.checked = checkAll.checked; });
            updateTotal();
        });
    }

    checks.forEach(function(cb) {
        cb.addEventListener('change', updateTotal);
    });

    if (valorInput) {
        valorInput.addEventListener('input', function() {
            valorInput.dataset.manual = '1';
        });
    }
})();
</script>
<?= $this->endSection() ?>
