<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

require 'db.php';

// --- 1) CREAR O ACTUALIZAR OFICIAL (AJAX) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax'])) {
    $id = $_POST['id'] ?: null;
    $nombre = trim($_POST['nombre'] ?? '');
    $rango = trim($_POST['rango'] ?? '');
    $anio = intval($_POST['anios_asignado'] ?? 0);
    $notas = trim($_POST['notas'] ?? '');

    // --- DATOS PERSONALES ---
    $numero_identificacion = trim($_POST['numero_identificacion'] ?? '') ?: null;
    $fecha_nacimiento = trim($_POST['fecha_nacimiento'] ?? '') ?: null;
    $numero_telefono = trim($_POST['numero_telefono'] ?? '') ?: null;
    $direccion = trim($_POST['direccion'] ?? '') ?: null;
    $estado_civil = trim($_POST['estado_civil'] ?? '') ?: null;
    $departamento = trim($_POST['departamento'] ?? '') ?: null;
    $genero = trim($_POST['genero'] ?? '') ?: null;

    // --- DATOS MÉDICOS ---
    $alergias = trim($_POST['alergias'] ?? '') ?: null;
    $enfermedades_cronicas = trim($_POST['enfermedades_cronicas'] ?? '') ?: null;
    $tipo_sangre = trim($_POST['tipo_sangre'] ?? '') ?: null;
    $accidentes_laborales = trim($_POST['accidentes_laborales'] ?? '') ?: null;

    // --- Cargar datos existentes ---
    $existing = [
        'foto' => null, 'documento' => null, 'estado' => null,
        'numero_identificacion' => null, 'fecha_nacimiento' => null, 'numero_telefono' => null,
        'direccion' => null, 'estado_civil' => null, 'departamento' => null, 'genero' => null,
        'alergias' => null, 'enfermedades_cronicas' => null, 'tipo_sangre' => null,
        'accidentes_laborales' => null
    ];

    if ($id) {
        $stmtEx = $pdo->prepare("SELECT * FROM oficiales WHERE id = ?");
        $stmtEx->execute([$id]);
        $f = $stmtEx->fetch(PDO::FETCH_ASSOC);
        if ($f) $existing = array_merge($existing, $f);
    }

    // --- Validar estado ---
    $estadoInput = strtolower(trim($_POST['estado'] ?? ''));
    $allowedStatuses = ['traslado', 'permiso', 'activo', 'faltista', 'retirado', 'licencia'];
    $final_estado = in_array($estadoInput, $allowedStatuses) ? $estadoInput : ($existing['estado'] ?? 'activo');

    // --- Manejo de foto ---
    $foto = null;
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        if (!in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'gif'])) exit('ERROR: Solo se permiten imágenes');
        if (!file_exists('img')) mkdir('img', 0777, true);
        $nombre_archivo = uniqid('oficial_') . '.' . $ext;
        $ruta_destino = 'img/' . $nombre_archivo;
        if (move_uploaded_file($_FILES['foto']['tmp_name'], $ruta_destino)) $foto = $ruta_destino;
    }

    // --- Manejo de documento PDF ---
    $documento = null;
    if (isset($_FILES['documento']) && $_FILES['documento']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['documento']['name'], PATHINFO_EXTENSION);
        if (strtolower($ext) !== 'pdf') exit('ERROR: Solo se permiten archivos PDF');
        if (!file_exists('documentos')) mkdir('documentos', 0777, true);
        $nombre_doc = uniqid('doc_cv_') . '.pdf';
        $ruta_doc = 'documentos/' . $nombre_doc;
        if (move_uploaded_file($_FILES['documento']['tmp_name'], $ruta_doc)) $documento = $ruta_doc;
    }

    // --- Conservar valores existentes ---
    $final_foto = $foto ?? $existing['foto'];
    $final_doc1 = $documento ?? $existing['documento'];

    $final_numero_identificacion = $numero_identificacion ?? $existing['numero_identificacion'];
    $final_fecha_nacimiento = $fecha_nacimiento ?: $existing['fecha_nacimiento'];
    $final_numero_telefono = $numero_telefono ?? $existing['numero_telefono'];
    $final_direccion = $direccion ?? $existing['direccion'];
    $final_estado_civil = $estado_civil ?? $existing['estado_civil'];
    $final_departamento = $departamento ?? $existing['departamento'];
    $final_genero = $genero ?? $existing['genero'];

    $final_alergias = $alergias ?? $existing['alergias'];
    $final_enfermedades_cronicas = $enfermedades_cronicas ?? $existing['enfermedades_cronicas'];
    $final_tipo_sangre = $tipo_sangre ?? $existing['tipo_sangre'];
    $final_accidentes_laborales = $accidentes_laborales ?? $existing['accidentes_laborales'];

    // --- Guardar en base de datos ---
    if ($id) {
        $stmt = $pdo->prepare("UPDATE oficiales SET 
            nombre = ?, rango = ?, años_asignado = ?, notas = ?, documento = ?, foto = ?, estado = ?,
            numero_identificacion = ?, fecha_nacimiento = ?, numero_telefono = ?, direccion = ?, estado_civil = ?, departamento = ?, genero = ?,
            alergias = ?, enfermedades_cronicas = ?, tipo_sangre = ?, accidentes_laborales = ?
            WHERE id = ?");
        $stmt->execute([
            $nombre, $rango, $anio, $notas, $final_doc1, $final_foto, $final_estado,
            $final_numero_identificacion, $final_fecha_nacimiento, $final_numero_telefono,
            $final_direccion, $final_estado_civil, $final_departamento, $final_genero,
            $final_alergias, $final_enfermedades_cronicas, $final_tipo_sangre,  $final_accidentes_laborales,
            $id
        ]);
    } else {
        // ✅ CORREGIDO: Agregados los campos faltantes (usa_epp, ultima_evaluacion)
        $stmt = $pdo->prepare("INSERT INTO oficiales (
            nombre, rango, años_asignado, notas, documento, foto, estado,
            numero_identificacion, fecha_nacimiento, numero_telefono, direccion, estado_civil, departamento, genero,
            alergias, enfermedades_cronicas, tipo_sangre, accidentes_laborales
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ? )");
        $stmt->execute([
            $nombre, $rango, $anio, $notas, $final_doc1, $final_foto, $final_estado,
            $final_numero_identificacion, $final_fecha_nacimiento, $final_numero_telefono,
            $final_direccion, $final_estado_civil, $final_departamento, $final_genero,
            $final_alergias, $final_enfermedades_cronicas, $final_tipo_sangre, $final_accidentes_laborales
        ]);
    }

    exit('OK');
}

// --- 2) ELIMINAR OFICIAL (AJAX) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    $stmt = $pdo->prepare("SELECT foto, documento FROM oficiales WHERE id = ?");
    $stmt->execute([$_POST['delete']]);
    $oficial = $stmt->fetch();

    if ($oficial && $oficial['foto'] && file_exists($oficial['foto'])) @unlink($oficial['foto']);
    if ($oficial && $oficial['documento'] && file_exists($oficial['documento'])) @unlink($oficial['documento']);

    $stmt = $pdo->prepare("DELETE FROM oficiales WHERE id = ?");
    $stmt->execute([$_POST['delete']]);
    exit('OK');
}

// --- 3) LEER TODOS LOS OFICIALES ---
$stmt = $pdo->query("
    SELECT *, YEAR(CURDATE()) - años_asignado AS años_servicio 
    FROM oficiales
    ORDER BY años_asignado DESC
");
$oficiales = $stmt->fetchAll();
?>

<!-- TÍTULO Y BOTÓN NUEVO -->
<div class="titulo-con-boton">
  <div class="titulo-izq">
    <h2 class="titulo-centrado">Historial de Oficiales</h2>
  </div>
  <div class="controls-der">
    <button id="btnNuevoOficial" type="button" onclick="mostrarFormOficial()" class="btn btn-primario btn-nuevo-oficial">+ Nuevo Oficial</button>
  </div>
</div>

<div id="statusResult" style="text-align:center;margin:0.8rem 0 1.2rem;color:var(--primary);font-weight:600;"></div>

<!-- FORMULARIO MODAL -->
<div id="formDivOficial" class="modal-overlay" aria-hidden="true" style="display:none;">
  <div class="modal-form modal-form-amplio" role="dialog" aria-modal="true" aria-labelledby="modalTituloOficial" tabindex="-1">
    <h3 id="modalTituloOficial">📋 Agregar / Editar Oficial</h3>
    <form id="oficialForm" enctype="multipart/form-data" autocomplete="off">
      <input type="hidden" name="id" id="ofId">

      <!-- DATOS PERSONALES -->
      <div class="form-grid">
        <div class="form-group">
          <label for="ofNombre">Nombre completo</label>
          <input type="text" name="nombre" id="ofNombre" placeholder="Ej: Juan Carlos Gómez" required>
        </div>
        <div class="form-group">
          <label for="ofRango">Rango militar</label>
          <select name="rango" id="ofRango" required>
            <option value="">Seleccionar rango</option>
            <option value="Coronel">Coronel</option>
            <option value="Teniente Coronel">Teniente Coronel</option>
            <option value="Mayor">Mayor</option>
            <option value="Capitán">Capitán</option>
            <option value="Teniente">Teniente</option>
            <option value="Subteniente">Subteniente</option>
            <option value="Teniente Navio">Teniente Navio</option>
            <option value="Alferez De Fragata">Alferez De Fragata</option>
            <option value="Jefe Primero">Jefe Primero</option>
          </select>
        </div>
        <div class="form-group">
          <label for="ofAniosAsignado">Año de asignación</label>
          <input type="number" name="anios_asignado" id="ofAniosAsignado" placeholder="Ej: 2020" required min="1980" max="2030">
        </div>
        <div class="form-group">
          <label for="ofNumeroIdentificacion">Número de identificación</label>
          <input type="text" name="numero_identificacion" id="ofNumeroIdentificacion" placeholder="Ej: 0801-1990-12345">
        </div>
        <div class="form-group">
          <label for="ofFechaNacimiento">Fecha de Nacimiento</label>
          <input type="date" name="fecha_nacimiento" id="ofFechaNacimiento">
        </div>
        <div class="form-group">
          <label for="ofNumeroTelefono">Número de Teléfono</label>
          <input type="text" name="numero_telefono" id="ofNumeroTelefono" placeholder="Ej: +504 1234-5678">
        </div>
        <div class="form-group">
          <label for="ofDireccion">Dirección</label>
          <input type="text" name="direccion" id="ofDireccion" placeholder="Ej: Barrio La Reforma, Tegucigalpa">
        </div>
        <div class="form-group">
          <label for="ofEstadoCivil">Estado Civil</label>
          <select name="estado_civil" id="ofEstadoCivil">
            <option value="">Seleccionar</option>
            <option value="soltero">Soltero</option>
            <option value="casado">Casado</option>
            <option value="divorciado">Divorciado</option>
            <option value="viudo">Viudo</option>
          </select>
        </div>
        <div class="form-group">
          <label for="ofDepartamento">Departamento Asignado</label>
          <select name="departamento" id="ofDepartamento">
            <option value="">Seleccionar</option>
            <option value="Rectoria">Rectoría</option>
            <option value="contabilidad">Contabilidad</option>
            <option value="recursos_humanos">Recursos Humanos</option>
            <option value="comandancia">Comandancia</option>
            <option value="logistica">Logística</option>
          </select>
        </div>
        <div class="form-group">
          <label for="ofGenero">Género</label>
          <select name="genero" id="ofGenero">
            <option value="">Seleccionar</option>
            <option value="masculino">Masculino</option>
            <option value="femenino">Femenino</option>
            <option value="otro">Otro</option>
          </select>
        </div>
        <div class="form-group">
          <label for="ofNotas">Notas adicionales</label>
          <textarea name="notas" id="ofNotas" rows="3" placeholder="Observaciones, reconocimientos, historial, etc."></textarea>
        </div>
      </div>

      <!-- DATOS MÉDICOS -->
      <h4 style="margin-top:1.5rem; border-top:1px solid #eee; padding-top:1rem;">✚ Información Médica</h4>
      <div class="form-grid">
        <div class="form-group">
          <label for="ofTipoSangre">Tipo de sangre</label>
          <select name="tipo_sangre" id="ofTipoSangre">
            <option value="">Seleccionar</option>
            <option value="A+">A+</option>
            <option value="A-">A-</option>
            <option value="B+">B+</option>
            <option value="B-">B-</option>
            <option value="AB+">AB+</option>
            <option value="AB-">AB-</option>
            <option value="O+">O+</option>
            <option value="O-">O-</option>
          </select>
        </div>
        <div class="form-group">
          <label for="ofEnfermedades">Enfermedades crónicas</label>
          <textarea name="enfermedades_cronicas" id="ofEnfermedades" rows="2" placeholder="Ej: Diabetes, hipertensión"></textarea>
        </div>
        <div class="form-group">
          <label>Estado de salud actual:</label>
          <select name="estado_salud" id="ofEstadoSalud">
            <option value="">-- Seleccione --</option>
            <option value="estable">Estable</option>
            <option value="tratamiento">Con tratamiento crónico</option>
            <option value="recuperacion">En recuperación</option>
            <option value="otro">Otro</option>
          </select>
        </div>

        <!-- Checkbox para alergias -->
        <div class="form-group" style="grid-column: span 2;">
          <label style="display:flex;align-items:center;gap:0.5rem;">
            <input type="checkbox" name="tiene_alergias" id="ofTieneAlergias" value="1">
            ¿Tiene alergias?
          </label>
          <div id="ofCampoAlergias" style="display:none; margin-top:8px;">
            <textarea name="alergias" id="ofAlergias" rows="2" placeholder="Ej: Penicilina..."></textarea>
          </div>
        </div>

        <!-- Checkbox para accidentes -->
        <div class="form-group" style="grid-column: span 2;">
          <label style="display:flex;align-items:center;gap:0.5rem;">
            <input type="checkbox" name="tiene_accidente" id="ofTieneAccidente" value="1">
            ¿Ha tenido accidentes?
          </label>
          <div id="ofCampoAccidente" style="display:none; margin-top:8px;">
            <div class="form-grid">
              <div class="form-group">
                <label>Fecha:</label>
                <input type="date" name="fecha_accidente" id="ofFechaAccidente">
              </div>
              <div class="form-group">
                <label>Descripción:</label>
                <textarea name="accidentes_laborales" id="ofAccidentes" rows="2"></textarea>
              </div>
            </div>
          </div>
        </div>

        <!-- ✅ Checkbox EPP (agregado) -->
        <div class="form-group" style="display:flex;align-items:center;gap:0.5rem;grid-column: span 2;">
          <input type="checkbox" name="usa_epp" id="ofUsaEPP" value="1">
          <label for="ofUsaEPP">¿Utiliza EPP (Equipo de Protección Personal)?</label>
        </div>
      </div> <!-- cierra form-grid de datos médicos -->

      <!-- ARCHIVOS -->
      <h4 style="margin-top:1.5rem; border-top:1px solid #eee; padding-top:1rem;">📎 Archivos</h4>
      <div class="form-grid">
        <div class="form-group">
          <label>Fotografía:</label>
          <input type="file" name="foto" accept="image/*">
          <small>Formatos: JPG, PNG, GIF</small>
        </div>
        <div class="form-group">
          <label>Documento (PDF):</label>
          <input type="file" name="documento" accept=".pdf">
          <small>Contratos, certificados, etc.</small>
        </div>
        <div class="form-group">
          <label for="ofEstado">Estado del Oficial</label>
          <select name="estado" id="ofEstado" required>
            <option value="activo">Activo</option>
            <option value="traslado">En traslado</option>
            <option value="permiso">En permiso</option>
            <option value="faltista">Faltista</option>
            <option value="retirado">Retirado</option>
            <option value="licencia">Licencia</option>
          </select>
        </div>
      </div>

      <!-- BOTONES -->
      <div class="form-buttons">
        <button type="submit" class="btn btn-primario">Guardar</button>
        <button type="button" onclick="cerrarFormOficial()" class="btn btn-secundario">Cancelar</button>
      </div>
    </form>
  </div>
</div>

<!-- LISTADO DE OFICIALES -->
<div id="listaOficiales" class="perfil-lista moderna">
  <?php foreach ($oficiales as $o): 
    $estado = $o['estado'] ?? 'activo';
  ?>
    <article class="perfil-card perfil-card-expandido" role="article" aria-labelledby="of-<?php echo $o['id']; ?>" data-status="<?= htmlspecialchars($estado) ?>">
      <div class="perfil-header">
        <img
          src="<?= $o['foto'] ? htmlspecialchars($o['foto']) : 'img/default_user.png' ?>"
          alt="Foto de <?= htmlspecialchars($o['nombre']) ?>"
          class="perfil-foto"
          onerror="this.src='img/default_user.png'"
        >
        <div class="perfil-meta">
          <h3 id="of-<?= $o['id'] ?>"><?= htmlspecialchars($o['nombre']) ?></h3>
          <span class="chip"><?= htmlspecialchars($o['rango']) ?></span>
          <div class="meta-sub">
            <span title="Año asignado">Asignado: <?= $o['años_asignado'] ?></span>
            <span title="Años de servicio"> • Servicio: <?= $o['años_servicio'] ?> años</span>
          </div>
        </div>
      </div>

      <?php if ($o['notas']): ?>
        <div class="perfil-notas">
          <?= nl2br(htmlspecialchars($o['notas'])) ?>
        </div>
      <?php endif; ?>

      <div class="perfil-footer">
        <div class="document-links" style="display:flex;gap:0.5rem;flex-wrap:wrap;align-items:center;">
          <?php if (!empty($o['documento'])): ?>
            <a href="<?= htmlspecialchars($o['documento']) ?>" target="_blank" class="btn btn-outline btn-documento">Ver HS</a>
          <?php endif; ?>
          
          <a href="generar_perfil_oficial.php?id=<?= $o['id'] ?>" 
             class="btn btn-outline btn-documento" target="_blank">Ver Perfil</a>
        </div>

        <div class="accion-grupo">
          <button type="button"
            onclick='editarOficial(<?= json_encode([
              "id" => $o["id"],
              "nombre" => $o["nombre"],
              "rango" => $o["rango"],
              "anio" => $o["años_asignado"],
              "notas" => $o["notas"],
              "foto" => $o["foto"],
              "documento" => $o["documento"],
              "estado" => $o["estado"],
              "numero_identificacion" => $o["numero_identificacion"],
              "fecha_nacimiento" => $o["fecha_nacimiento"],
              "numero_telefono" => $o["numero_telefono"],
              "direccion" => $o["direccion"],
              "estado_civil" => $o["estado_civil"],
              "departamento" => $o["departamento"],
              "genero" => $o["genero"],
              "alergias" => $o["alergias"],
              "enfermedades_cronicas" => $o["enfermedades_cronicas"],
              "tipo_sangre" => $o["tipo_sangre"],
              "accidentes_laborales" => $o["accidentes_laborales"]
            ]) ?>)'
            class="btn btn-primario btn-pequeno">Editar</button>

          <button type="button" onclick="eliminarOficial(<?= $o['id'] ?>)" class="btn btn-secundario btn-pequeno">Eliminar</button>
        </div>
      </div>
    </article>
  <?php endforeach; ?>
</div>

<script>
// === FUNCIONES PARA OFICIALES ===
function mostrarFormOficial() {
  document.getElementById('formDivOficial').style.display = 'flex';
}

function cerrarFormOficial() {
  document.getElementById('formDivOficial').style.display = 'none';
  document.getElementById('oficialForm').reset();
  document.getElementById('ofId').value = '';
}

function editarOficial(data) {
  mostrarFormOficial();
  const fields = [
    'id', 'nombre', 'rango', 'anio', 'notas', 'numero_identificacion',
    'fecha_nacimiento', 'numero_telefono', 'direccion', 'estado_civil',
    'departamento', 'genero', 'alergias', 'enfermedades_cronicas',
    'tipo_sangre',  'accidentes_laborales'
  ];
  fields.forEach(field => {
    const el = document.getElementById('of' + field.charAt(0).toUpperCase() + field.slice(1));
    if (el) el.value = data[field] || '';
  });

  // Checkbox EPP
  const epp = document.getElementById('ofUsaEPP');
  if (epp) epp.checked = data.usa_epp == 1;

  // Checkboxes condicionales
  const alergiasCheck = document.getElementById('ofTieneAlergias');
  if (alergiasCheck) {
    const tiene = !!data.alergias;
    alergiasCheck.checked = tiene;
    document.getElementById('ofCampoAlergias').style.display = tiene ? 'block' : 'none';
  }

  const accidenteCheck = document.getElementById('ofTieneAccidente');
  if (accidenteCheck) {
    const tiene = !!data.accidentes_laborales;
    accidenteCheck.checked = tiene;
    document.getElementById('ofCampoAccidente').style.display = tiene ? 'block' : 'none';
    if (tiene && data.fecha_accidente) {
      document.getElementById('ofFechaAccidente').value = data.fecha_accidente;
    }
  }

  document.getElementById('ofEstado').value = data.estado || 'activo';
}

function eliminarOficial(id) {
  if (!confirm('¿Eliminar oficial? Esta acción no se puede deshacer.')) return;
  fetch('', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'delete=' + id
  }).then(() => location.reload());
}

// Eventos del formulario
document.getElementById('oficialForm').addEventListener('submit', function(e) {
  e.preventDefault();
  const formData = new FormData(this);
  formData.append('ajax', '1');
  fetch('', { method: 'POST', body: formData })
    .then(res => res.text())
    .then(txt => {
      if (txt === 'OK') {
        cerrarFormOficial();
        location.reload();
      } else {
        alert('Error: ' + txt);
      }
    });
});

// Checkboxes médicos
document.getElementById('ofTieneAlergias')?.addEventListener('change', function() {
  document.getElementById('ofCampoAlergias').style.display = this.checked ? 'block' : 'none';
  if (!this.checked) document.getElementById('ofAlergias').value = '';
});

document.getElementById('ofTieneAccidente')?.addEventListener('change', function() {
  document.getElementById('ofCampoAccidente').style.display = this.checked ? 'block' : 'none';
  if (!this.checked) {
    document.getElementById('ofFechaAccidente').value = '';
    document.getElementById('ofAccidentes').value = '';
  }
});

// Filtro por estado
(function() {
  const lista = document.getElementById('listaOficiales');
  const resultContainer = document.getElementById('statusResult');
  if (!lista || !resultContainer) return;

  function aplicarFiltro() {
    const tarjetas = Array.from(lista.querySelectorAll('.perfil-card'));
    const matched = tarjetas.filter(card => {
      const status = (card.dataset.status || 'activo').toLowerCase();
      return !statusFilter || status === statusFilter;
    });
    tarjetas.forEach(card => card.style.display = matched.includes(card) ? 'block' : 'none');
    resultContainer.textContent = statusFilter 
      ? `Mostrando oficiales en estado "${statusFilter}" (${matched.length})`
      : `Total de oficiales: ${matched.length}`;
  }

  let statusFilter = '';
  aplicarFiltro();
})();
</script>