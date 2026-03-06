<?= $this->extend('layouts/auth') ?>

<?= $this->section('content') ?>

<style>
    .auth-card { max-width: 700px !important; }
    .student-block { border: 2px solid #e9ecef; border-radius: 12px; padding: 1.25rem; margin-bottom: 1rem; position: relative; }
    .student-block .remove-student { position: absolute; top: 8px; right: 8px; }
    .section-title { font-weight: 600; color: var(--heroicos-primary); border-bottom: 2px solid var(--heroicos-primary); padding-bottom: 0.5rem; margin-bottom: 1rem; }
</style>

<h5 class="mb-1">Registro de Inscripcion</h5>
<p class="text-muted small mb-3">Complete los datos para inscribirse en la Academia Heroicos</p>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger py-2">
        <i class="bi bi-exclamation-circle me-1"></i><?= session()->getFlashdata('error') ?>
    </div>
<?php endif; ?>

<form action="<?= site_url('registro/' . $token['token']) ?>" method="POST" id="registroForm" enctype="multipart/form-data">
    <?= csrf_field() ?>

    <!-- Datos del Acudiente -->
    <h6 class="section-title"><i class="bi bi-person me-2"></i>Datos del Acudiente</h6>

    <?php
        // Split nombre_acudiente into nombres and apellidos (professor enters them separately, stored as "Nombres Apellidos")
        $nombreParts = explode(' ', trim($token['nombre_acudiente'] ?? ''), 2);
        $defaultNombres = $nombreParts[0] ?? '';
        $defaultApellidos = $nombreParts[1] ?? '';
    ?>
    <div class="row g-2 mb-2">
        <div class="col-6">
            <label class="form-label small">Nombres *</label>
            <input type="text" class="form-control form-control-sm" name="acud_nombres"
                   value="<?= old('acud_nombres', $defaultNombres) ?>" required>
        </div>
        <div class="col-6">
            <label class="form-label small">Apellidos *</label>
            <input type="text" class="form-control form-control-sm" name="acud_apellidos"
                   value="<?= old('acud_apellidos', $defaultApellidos) ?>" required>
        </div>
    </div>

    <div class="mb-2">
        <label class="form-label small">Email</label>
        <input type="email" class="form-control form-control-sm bg-light" value="<?= esc($token['email']) ?>" readonly>
    </div>

    <div class="row g-2 mb-2">
        <div class="col-5">
            <label class="form-label small">Tipo Doc. *</label>
            <select class="form-select form-select-sm" name="acud_tipo_documento" required>
                <option value="CC" <?= old('acud_tipo_documento') === 'CC' ? 'selected' : '' ?>>C.C.</option>
                <option value="TI" <?= old('acud_tipo_documento') === 'TI' ? 'selected' : '' ?>>T.I.</option>
                <option value="CE" <?= old('acud_tipo_documento') === 'CE' ? 'selected' : '' ?>>C.E.</option>
                <option value="PP" <?= old('acud_tipo_documento') === 'PP' ? 'selected' : '' ?>>Pasaporte</option>
            </select>
        </div>
        <div class="col-7">
            <label class="form-label small">Numero Doc. *</label>
            <input type="text" class="form-control form-control-sm" name="acud_numero_documento"
                   value="<?= old('acud_numero_documento') ?>" inputmode="numeric" pattern="[0-9]*" required>
        </div>
    </div>

    <div class="row g-2 mb-3">
        <div class="col-6">
            <label class="form-label small">Telefono *</label>
            <input type="text" class="form-control form-control-sm" name="acud_telefono"
                   value="<?= old('acud_telefono') ?>" required>
        </div>
        <div class="col-6">
            <label class="form-label small">Direccion *</label>
            <input type="text" class="form-control form-control-sm" name="acud_direccion"
                   value="<?= old('acud_direccion') ?>" required>
        </div>
    </div>

    <!-- Datos de Estudiantes -->
    <h6 class="section-title"><i class="bi bi-mortarboard me-2"></i>Datos del Estudiante</h6>

    <div id="studentsContainer">
        <div class="student-block" data-index="0">
            <strong class="d-block mb-2 small">Estudiante 1</strong>
            <div class="mb-2">
                <label class="form-label small">Fotografia del estudiante <span class="text-danger">*</span></label>
                <input type="file" class="form-control form-control-sm" name="est_foto[]" accept="image/*" required>
                <div class="form-text" style="font-size: 0.75rem;">Foto reciente tipo documento. Max 2MB. JPG o PNG.</div>
            </div>
            <div class="row g-2 mb-2">
                <div class="col-6">
                    <label class="form-label small">Nombres *</label>
                    <input type="text" class="form-control form-control-sm" name="est_nombres[]" required>
                </div>
                <div class="col-6">
                    <label class="form-label small">Apellidos *</label>
                    <input type="text" class="form-control form-control-sm" name="est_apellidos[]" required>
                </div>
            </div>
            <div class="row g-2 mb-2">
                <div class="col-5">
                    <label class="form-label small">Tipo Doc.</label>
                    <select class="form-select form-select-sm" name="est_tipo_documento[]">
                        <option value="TI">T.I.</option>
                        <option value="RC">R.C.</option>
                        <option value="pasaporte">Pasaporte</option>
                        <option value="otro">Otro</option>
                    </select>
                </div>
                <div class="col-7">
                    <label class="form-label small">Numero Doc.</label>
                    <input type="text" class="form-control form-control-sm" name="est_numero_documento[]"
                           inputmode="numeric" pattern="[0-9]*">
                </div>
            </div>
            <div class="row g-2 mb-2">
                <div class="col-6">
                    <label class="form-label small">Fecha Nac. *</label>
                    <input type="date" class="form-control form-control-sm" name="est_fecha_nacimiento[]" required>
                </div>
                <div class="col-6">
                    <label class="form-label small">Sexo *</label>
                    <select class="form-select form-select-sm" name="est_sexo[]" required>
                        <option value="M">Masculino</option>
                        <option value="F">Femenino</option>
                    </select>
                </div>
            </div>
            <div class="row g-2 mb-2">
                <div class="col-4">
                    <label class="form-label small">Talla Camiseta</label>
                    <input type="text" class="form-control form-control-sm" name="est_talla_camiseta[]" placeholder="S, M, L...">
                </div>
                <div class="col-4">
                    <label class="form-label small">Talla Pantaloneta</label>
                    <input type="text" class="form-control form-control-sm" name="est_talla_pantaloneta[]" placeholder="S, M, L...">
                </div>
                <div class="col-4">
                    <label class="form-label small">Talla Medias</label>
                    <input type="text" class="form-control form-control-sm" name="est_talla_medias[]" placeholder="S, M, L...">
                </div>
            </div>
            <div class="row g-2 mb-2">
                <div class="col-4">
                    <label class="form-label small">Posicion</label>
                    <input type="text" class="form-control form-control-sm" name="est_posicion[]" placeholder="Delantero...">
                </div>
                <div class="col-4">
                    <label class="form-label small">Pie Dominante</label>
                    <select class="form-select form-select-sm" name="est_pie_dominante[]">
                        <option value="derecho">Derecho</option>
                        <option value="izquierdo">Izquierdo</option>
                        <option value="ambidiestro">Ambidiestro</option>
                    </select>
                </div>
                <div class="col-4">
                    <label class="form-label small">RH</label>
                    <select class="form-select form-select-sm" name="est_rh[]">
                        <option value="">Seleccione...</option>
                        <option value="O+">O+</option>
                        <option value="O-">O-</option>
                        <option value="A+">A+</option>
                        <option value="A-">A-</option>
                        <option value="B+">B+</option>
                        <option value="B-">B-</option>
                        <option value="AB+">AB+</option>
                        <option value="AB-">AB-</option>
                    </select>
                </div>
            </div>
            <div class="mb-0">
                <label class="form-label small">EPS</label>
                <input type="text" class="form-control form-control-sm" name="est_eps[]" placeholder="Nombre de la EPS">
            </div>
        </div>
    </div>

    <button type="button" class="btn btn-outline-primary btn-sm w-100 mb-3" id="addStudentBtn">
        <i class="bi bi-plus-circle me-1"></i>Agregar otro estudiante
    </button>

    <!-- Autorizacion de Datos Personales -->
    <h6 class="section-title"><i class="bi bi-shield-check me-2"></i>Autorizacion de Tratamiento de Datos</h6>
    <?= $this->include('partials/consentimiento_datos') ?>

    <button type="submit" class="btn btn-primary w-100 py-2" id="submitBtn" disabled>
        <i class="bi bi-check-circle me-2"></i>Completar Registro
    </button>
</form>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
(function() {
    var MAX_FILE_SIZE = 2 * 1024 * 1024; // 2MB

    function validateFileSize(input) {
        var feedback = input.parentElement.querySelector('.file-size-error');
        if (!feedback) {
            feedback = document.createElement('div');
            feedback.className = 'file-size-error text-danger small mt-1';
            input.parentElement.appendChild(feedback);
        }
        if (input.files.length > 0 && input.files[0].size > MAX_FILE_SIZE) {
            feedback.textContent = 'La foto supera el tamaño maximo de 2MB. Por favor seleccione una imagen mas pequeña.';
            input.classList.add('is-invalid');
            return false;
        }
        feedback.textContent = '';
        input.classList.remove('is-invalid');
        return true;
    }

    // Validate all file inputs on form submit
    document.getElementById('registroForm').addEventListener('submit', function(e) {
        var fileInputs = this.querySelectorAll('input[type="file"]');
        var valid = true;
        fileInputs.forEach(function(input) {
            if (!validateFileSize(input)) valid = false;
        });
        if (!valid) {
            e.preventDefault();
            alert('Una o mas fotos superan el tamaño maximo de 2MB.');
        }
    });

    // Live validation on file change
    document.addEventListener('change', function(e) {
        if (e.target.matches('input[type="file"]')) validateFileSize(e.target);
    });

    let studentIndex = 1;
    const container = document.getElementById('studentsContainer');
    const addBtn = document.getElementById('addStudentBtn');

    addBtn.addEventListener('click', function() {
        const firstBlock = container.querySelector('.student-block');
        const newBlock = firstBlock.cloneNode(true);

        studentIndex++;
        newBlock.setAttribute('data-index', studentIndex - 1);
        newBlock.querySelector('strong').textContent = 'Estudiante ' + studentIndex;

        // Clear all input values
        newBlock.querySelectorAll('input').forEach(function(input) {
            input.value = '';
        });
        newBlock.querySelectorAll('select').forEach(function(sel) {
            sel.selectedIndex = 0;
        });

        // Add remove button
        if (!newBlock.querySelector('.remove-student')) {
            var removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'btn btn-sm btn-outline-danger remove-student';
            removeBtn.innerHTML = '<i class="bi bi-x-lg"></i>';
            removeBtn.addEventListener('click', function() {
                newBlock.remove();
                renumberStudents();
            });
            newBlock.appendChild(removeBtn);
        }

        container.appendChild(newBlock);
    });

    function renumberStudents() {
        var blocks = container.querySelectorAll('.student-block');
        blocks.forEach(function(block, i) {
            block.querySelector('strong').textContent = 'Estudiante ' + (i + 1);
        });
        studentIndex = blocks.length;
    }

    // Enable submit only when all consent checkboxes are checked
    var consentBoxes = document.querySelectorAll('.consent-checks input[type="checkbox"]');
    var submitBtn = document.getElementById('submitBtn');

    function checkConsent() {
        var allChecked = true;
        consentBoxes.forEach(function(cb) {
            if (!cb.checked) allChecked = false;
        });
        submitBtn.disabled = !allChecked;
    }

    consentBoxes.forEach(function(cb) {
        cb.addEventListener('change', checkConsent);
    });
})();
</script>
<?= $this->endSection() ?>
