<?= $this->extend('layouts/main') ?>

<?= $this->section('sidebar') ?>
<?= $this->include('acudiente/partials/sidebar') ?>
<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
    .section-title { font-weight: 600; color: var(--heroicos-primary); border-bottom: 2px solid var(--heroicos-primary); padding-bottom: 0.5rem; margin-bottom: 1rem; }
    .foto-preview { width: 100px; height: 100px; object-fit: cover; border-radius: 50%; border: 3px solid var(--heroicos-primary); }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<a href="<?= site_url('acudiente/estudiantes/' . $estudiante['id']) ?>" class="btn btn-sm btn-outline-secondary mb-3">
    <i class="bi bi-arrow-left me-1"></i>Volver
</a>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="table-card">
            <div class="card-header"><i class="bi bi-pencil-square me-2"></i>Editar Perfil del Estudiante</div>
            <div class="card-body">
                <form action="<?= site_url('acudiente/estudiantes/' . $estudiante['id'] . '/actualizar') ?>" method="post" enctype="multipart/form-data" id="editForm">
                    <?= csrf_field() ?>
                    <!-- Hidden math fields populated by JS before submit -->
                    <input type="hidden" name="math_a" id="mathA">
                    <input type="hidden" name="math_b" id="mathB">
                    <input type="hidden" name="math_answer" id="mathAnswer">

                    <!-- Fotografia -->
                    <h6 class="section-title"><i class="bi bi-camera me-2"></i>Fotografia</h6>
                    <?php if (!empty($estudiante['foto'])): ?>
                    <div class="mb-2 text-center">
                        <img src="<?= base_url($estudiante['foto']) ?>" alt="Foto actual" class="foto-preview mb-1" id="fotoPreview">
                        <div class="small text-muted">Foto actual</div>
                    </div>
                    <?php endif; ?>
                    <div class="mb-3">
                        <label class="form-label small">Cambiar foto <span class="text-muted">(opcional)</span></label>
                        <input type="file" class="form-control form-control-sm" name="foto" accept="image/*" id="fotoInput">
                        <div class="form-text" style="font-size:0.75rem;">Max 2MB. JPG, PNG o WebP.</div>
                    </div>

                    <!-- Datos Personales -->
                    <h6 class="section-title"><i class="bi bi-person me-2"></i>Datos Personales</h6>

                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="form-label small">Nombres *</label>
                            <input type="text" class="form-control form-control-sm" name="nombres"
                                   value="<?= esc(old('nombres', $estudiante['nombres'])) ?>" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small">Apellidos *</label>
                            <input type="text" class="form-control form-control-sm" name="apellidos"
                                   value="<?= esc(old('apellidos', $estudiante['apellidos'])) ?>" required>
                        </div>
                    </div>

                    <div class="row g-2 mb-2">
                        <div class="col-5">
                            <label class="form-label small">Tipo Doc.</label>
                            <select class="form-select form-select-sm" name="tipo_documento">
                                <?php $tipoDoc = old('tipo_documento', $estudiante['tipo_documento'] ?? 'TI'); ?>
                                <option value="TI"        <?= $tipoDoc === 'TI'        ? 'selected' : '' ?>>T.I.</option>
                                <option value="RC"        <?= $tipoDoc === 'RC'        ? 'selected' : '' ?>>R.C.</option>
                                <option value="pasaporte" <?= $tipoDoc === 'pasaporte' ? 'selected' : '' ?>>Pasaporte</option>
                                <option value="otro"      <?= $tipoDoc === 'otro'      ? 'selected' : '' ?>>Otro</option>
                            </select>
                        </div>
                        <div class="col-7">
                            <label class="form-label small">Numero Doc.</label>
                            <input type="text" class="form-control form-control-sm" name="numero_documento"
                                   value="<?= esc(old('numero_documento', $estudiante['numero_documento'] ?? '')) ?>"
                                   inputmode="numeric" pattern="[0-9]*">
                        </div>
                    </div>

                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="form-label small">Fecha Nac. *</label>
                            <input type="date" class="form-control form-control-sm" name="fecha_nacimiento"
                                   value="<?= esc(old('fecha_nacimiento', $estudiante['fecha_nacimiento'])) ?>" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small">Sexo *</label>
                            <?php $sexo = old('sexo', $estudiante['sexo']); ?>
                            <select class="form-select form-select-sm" name="sexo" required>
                                <option value="M" <?= $sexo === 'M' ? 'selected' : '' ?>>Masculino</option>
                                <option value="F" <?= $sexo === 'F' ? 'selected' : '' ?>>Femenino</option>
                            </select>
                        </div>
                    </div>

                    <!-- Datos Deportivos -->
                    <h6 class="section-title mt-4"><i class="bi bi-dribbble me-2"></i>Datos Deportivos</h6>

                    <div class="row g-2 mb-2">
                        <div class="col-4">
                            <label class="form-label small">Talla Camiseta</label>
                            <input type="text" class="form-control form-control-sm" name="talla_camiseta"
                                   placeholder="S, M, L..." value="<?= esc(old('talla_camiseta', $estudiante['talla_camiseta'] ?? '')) ?>">
                        </div>
                        <div class="col-4">
                            <label class="form-label small">Talla Pantaloneta</label>
                            <input type="text" class="form-control form-control-sm" name="talla_pantaloneta"
                                   placeholder="S, M, L..." value="<?= esc(old('talla_pantaloneta', $estudiante['talla_pantaloneta'] ?? '')) ?>">
                        </div>
                        <div class="col-4">
                            <label class="form-label small">Talla Medias</label>
                            <input type="text" class="form-control form-control-sm" name="talla_medias"
                                   placeholder="S, M, L..." value="<?= esc(old('talla_medias', $estudiante['talla_medias'] ?? '')) ?>">
                        </div>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="form-label small">Posicion</label>
                            <input type="text" class="form-control form-control-sm" name="posicion"
                                   placeholder="Delantero..." value="<?= esc(old('posicion', $estudiante['posicion'] ?? '')) ?>">
                        </div>
                        <div class="col-6">
                            <label class="form-label small">Pie Dominante</label>
                            <?php $pie = old('pie_dominante', $estudiante['pie_dominante'] ?? 'derecho'); ?>
                            <select class="form-select form-select-sm" name="pie_dominante">
                                <option value="derecho"     <?= $pie === 'derecho'     ? 'selected' : '' ?>>Derecho</option>
                                <option value="izquierdo"   <?= $pie === 'izquierdo'   ? 'selected' : '' ?>>Izquierdo</option>
                                <option value="ambidiestro" <?= $pie === 'ambidiestro' ? 'selected' : '' ?>>Ambidiestro</option>
                            </select>
                        </div>
                    </div>

                    <!-- Informacion Medica -->
                    <h6 class="section-title mt-4"><i class="bi bi-heart-pulse me-2"></i>Informacion Medica</h6>

                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="form-label small">EPS</label>
                            <input type="text" class="form-control form-control-sm" name="eps"
                                   placeholder="Nombre de la EPS" value="<?= esc(old('eps', $estudiante['eps'] ?? '')) ?>">
                        </div>
                        <div class="col-6">
                            <label class="form-label small">RH</label>
                            <?php $rh = old('grupo_sanguineo', $estudiante['grupo_sanguineo'] ?? ''); ?>
                            <select class="form-select form-select-sm" name="grupo_sanguineo">
                                <option value="">Seleccione...</option>
                                <?php foreach (['O+','O-','A+','A-','B+','B-','AB+','AB-'] as $opt): ?>
                                    <option value="<?= $opt ?>" <?= $rh === $opt ? 'selected' : '' ?>><?= $opt ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Alergias</label>
                        <input type="text" class="form-control form-control-sm" name="alergias"
                               placeholder="Ninguna" value="<?= esc(old('alergias', $estudiante['alergias'] ?? '')) ?>">
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Condiciones medicas</label>
                        <input type="text" class="form-control form-control-sm" name="condiciones_medicas"
                               placeholder="Ninguna" value="<?= esc(old('condiciones_medicas', $estudiante['condiciones_medicas'] ?? '')) ?>">
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Medicamentos</label>
                        <input type="text" class="form-control form-control-sm" name="medicamentos"
                               placeholder="Ninguno" value="<?= esc(old('medicamentos', $estudiante['medicamentos'] ?? '')) ?>">
                    </div>

                    <!-- Contacto Emergencia -->
                    <h6 class="section-title mt-4"><i class="bi bi-telephone me-2"></i>Contacto de Emergencia</h6>

                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="form-label small">Nombre contacto</label>
                            <input type="text" class="form-control form-control-sm" name="contacto_emergencia"
                                   value="<?= esc(old('contacto_emergencia', $estudiante['contacto_emergencia'] ?? '')) ?>">
                        </div>
                        <div class="col-6">
                            <label class="form-label small">Telefono emergencia</label>
                            <input type="text" class="form-control form-control-sm" name="telefono_emergencia"
                                   value="<?= esc(old('telefono_emergencia', $estudiante['telefono_emergencia'] ?? '')) ?>">
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="button" class="btn btn-primary w-100 py-2" id="btnGuardar">
                            <i class="bi bi-check-circle me-2"></i>Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal de verificacion aritmetica -->
<div class="modal fade" id="mathModal" tabindex="-1" aria-labelledby="mathModalLabel" aria-modal="true" role="dialog">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title" id="mathModalLabel"><i class="bi bi-shield-check me-2"></i>Confirmacion</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <p class="mb-1 text-muted small">Para confirmar los cambios, resuelve:</p>
                <p class="fs-4 fw-bold mb-3" id="mathQuestion"></p>
                <input type="number" class="form-control text-center" id="mathInput"
                       placeholder="Respuesta" inputmode="numeric" autocomplete="off">
                <div class="text-danger small mt-1 d-none" id="mathError">Respuesta incorrecta. Intenta de nuevo.</div>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary btn-sm" id="btnConfirmar">Confirmar</button>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
(function () {
    var MAX_FILE_SIZE = 2 * 1024 * 1024;
    var fotoInput = document.getElementById('fotoInput');
    var fotoPreview = document.getElementById('fotoPreview');

    // Live preview of selected photo
    if (fotoInput) {
        fotoInput.addEventListener('change', function () {
            var feedback = fotoInput.parentElement.querySelector('.file-size-error');
            if (!feedback) {
                feedback = document.createElement('div');
                feedback.className = 'file-size-error text-danger small mt-1';
                fotoInput.parentElement.appendChild(feedback);
            }
            if (this.files.length === 0) return;
            var file = this.files[0];
            if (file.size > MAX_FILE_SIZE) {
                feedback.textContent = 'La foto supera el tamaño maximo de 2MB.';
                fotoInput.classList.add('is-invalid');
                return;
            }
            feedback.textContent = '';
            fotoInput.classList.remove('is-invalid');
            if (fotoPreview) {
                var reader = new FileReader();
                reader.onload = function (e) { fotoPreview.src = e.target.result; };
                reader.readAsDataURL(file);
            }
        });
    }

    // Arithmetic validation modal
    var mathA, mathB;
    var mathModal = new bootstrap.Modal(document.getElementById('mathModal'));
    var mathInput = document.getElementById('mathInput');
    var mathError = document.getElementById('mathError');

    function generateMath() {
        mathA = Math.floor(Math.random() * 9) + 1;
        mathB = Math.floor(Math.random() * 9) + 1;
        document.getElementById('mathQuestion').textContent = mathA + ' + ' + mathB + ' = ?';
        mathInput.value = '';
        mathError.classList.add('d-none');
    }

    document.getElementById('btnGuardar').addEventListener('click', function () {
        // Validate file size first
        if (fotoInput && fotoInput.files.length > 0 && fotoInput.files[0].size > MAX_FILE_SIZE) {
            alert('La foto supera el tamaño maximo de 2MB. Por favor seleccione una imagen mas pequeña.');
            return;
        }
        generateMath();
        mathModal.show();
        setTimeout(function () { mathInput.focus(); }, 400);
    });

    document.getElementById('btnConfirmar').addEventListener('click', function () {
        var answer = parseInt(mathInput.value, 10);
        if (isNaN(answer) || answer !== (mathA + mathB)) {
            mathError.classList.remove('d-none');
            mathInput.value = '';
            mathInput.focus();
            return;
        }
        // Populate hidden fields and submit
        document.getElementById('mathA').value = mathA;
        document.getElementById('mathB').value = mathB;
        document.getElementById('mathAnswer').value = answer;
        mathModal.hide();
        document.getElementById('editForm').submit();
    });

    // Allow pressing Enter in the math input
    mathInput.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            document.getElementById('btnConfirmar').click();
        }
    });
})();
</script>
<?= $this->endSection() ?>
