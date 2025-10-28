<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require 'db.php';

// === 1) GUARDAR PERSONAL (incluyendo datos médicos) ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax'])) {
    $id = $_POST['id'] ?: null;
    $nombre = trim($_POST['nombre']);
    $cargo = trim($_POST['cargo']);
    $anio = intval($_POST['anio_ingreso']);
    $notas = trim($_POST['notas']);
    $estado_laboral = trim($_POST['estado_laboral']);
    $area_asignada = trim($_POST['area_asignada']);
    $supervisor = trim($_POST['supervisor']);
    $turno = trim($_POST['turno']);
    $horario = trim($_POST['horario']);
    $telefono = trim($_POST['telefono']);
    $correo = trim($_POST['correo']);

    // === DATOS MÉDICOS ===
    $estado_salud = $_POST['estado_salud'] ?? null;
    $tiene_alergias = isset($_POST['tiene_alergias']) ? 1 : 0;
    $detalle_alergias = trim($_POST['detalle_alergias'] ?? '');
    $usa_epp = isset($_POST['usa_epp']) ? 1 : 0;
    $tipo_epp = trim($_POST['tipo_epp'] ?? '');
    $ultima_evaluacion = !empty($_POST['ultima_evaluacion']) ? $_POST['ultima_evaluacion'] : null;
    $proxima_evaluacion = !empty($_POST['proxima_evaluacion']) ? $_POST['proxima_evaluacion'] : null;
    $tiene_accidente = isset($_POST['tiene_accidente']) ? 1 : 0;
    $fecha_accidente = !empty($_POST['fecha_accidente']) ? $_POST['fecha_accidente'] : null;
    $detalle_accidente = trim($_POST['detalle_accidente'] ?? '');

    // === MANEJO DE IMAGEN ===
    $foto = null;
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array(strtolower($ext), $allowed)) exit('ERROR: Solo se permiten imágenes');
        if (!file_exists('img')) mkdir('img', 0777, true);
        $nombre_archivo = uniqid('mantenimiento_') . '.' . $ext;
        $ruta_destino = 'img/' . $nombre_archivo;
        if (move_uploaded_file($_FILES['foto']['tmp_name'], $ruta_destino)) {
            $foto = $ruta_destino;
        }
    }

    // === MANEJO DE DOCUMENTO PDF ===
    $documento = null;
    if (isset($_FILES['documento']) && $_FILES['documento']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['documento']['name'], PATHINFO_EXTENSION);
        if (strtolower($ext) !== 'pdf') exit('ERROR: Solo se permiten archivos PDF');
        if (!file_exists('documentos')) mkdir('documentos', 0777, true);
        $nombre_doc = uniqid('doc_mant_') . '.pdf';
        $ruta_doc = 'documentos/' . $nombre_doc;
        if (move_uploaded_file($_FILES['documento']['tmp_name'], $ruta_doc)) {
            $documento = $ruta_doc;
        }
    }

    // === ACTUALIZAR O INSERTAR ===
    if ($id) {
        // Actualizar
        $sql = "UPDATE mantenimiento SET 
            nombre = ?, cargo = ?, año_ingreso = ?, notas = ?, 
            estado_laboral = ?, area_asignada = ?, supervisor = ?, turno = ?, horario = ?, telefono = ?, correo = ?,
            estado_salud = ?, tiene_alergias = ?, detalle_alergias = ?, usa_epp = ?, tipo_epp = ?, 
            ultima_evaluacion = ?, proxima_evaluacion = ?, tiene_accidente = ?, fecha_accidente = ?, detalle_accidente = ?
            WHERE id = ?";
        $params = [
            $nombre, $cargo, $anio, $notas,
            $estado_laboral, $area_asignada, $supervisor, $turno, $horario, $telefono, $correo,
            $estado_salud, $tiene_alergias, $detalle_alergias, $usa_epp, $tipo_epp,
            $ultima_evaluacion, $proxima_evaluacion, $tiene_accidente, $fecha_accidente, $detalle_accidente,
            $id
        ];

        // Si hay nueva foto o documento, actualizar también
        if ($foto || $documento) {
            $set_parts = "nombre = ?, cargo = ?, año_ingreso = ?, notas = ?, 
                estado_laboral = ?, area_asignada = ?, supervisor = ?, turno = ?, horario = ?, telefono = ?, correo = ?,
                estado_salud = ?, tiene_alergias = ?, detalle_alergias = ?, usa_epp = ?, tipo_epp = ?, 
                ultima_evaluacion = ?, proxima_evaluacion = ?, tiene_accidente = ?, fecha_accidente = ?, detalle_accidente = ?";
            if ($foto) { $set_parts .= ", foto = ?"; $params[] = $foto; }
            if ($documento) { $set_parts .= ", documento = ?"; $params[] = $documento; }
            $set_parts .= " WHERE id = ?";
            $params[] = $id;
            $sql = "UPDATE mantenimiento SET $set_parts";
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
    } else {
        // Insertar nuevo
        $stmt = $pdo->prepare(
            "INSERT INTO mantenimiento (
                nombre, cargo, año_ingreso, foto, notas, documento,
                estado_laboral, area_asignada, supervisor, turno, horario, telefono, correo,
                estado_salud, tiene_alergias, detalle_alergias, usa_epp, tipo_epp,
                ultima_evaluacion, proxima_evaluacion, tiene_accidente, fecha_accidente, detalle_accidente
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $nombre, $cargo, $anio, $foto, $notas, $documento,
            $estado_laboral, $area_asignada, $supervisor, $turno, $horario, $telefono, $correo,
            $estado_salud, $tiene_alergias, $detalle_alergias, $usa_epp, $tipo_epp,
            $ultima_evaluacion, $proxima_evaluacion, $tiene_accidente, $fecha_accidente, $detalle_accidente
        ]);
    }
    exit('OK');
}

// === 2) ELIMINAR ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    $stmt = $pdo->prepare("SELECT foto, documento FROM mantenimiento WHERE id = ?");
    $stmt->execute([$_POST['delete']]);
    $personal = $stmt->fetch();
    if ($personal['foto'] && file_exists($personal['foto'])) unlink($personal['foto']);
    if ($personal['documento'] && file_exists($personal['documento'])) unlink($personal['documento']);
    $stmt = $pdo->prepare("DELETE FROM mantenimiento WHERE id = ?");
    $stmt->execute([$_POST['delete']]);
    exit('OK');
}

// === 3) LEER TODOS ===
$stmt = $pdo->query(
    "SELECT *, YEAR(CURDATE()) - año_ingreso AS años_servicio 
     FROM mantenimiento
     ORDER BY año_ingreso DESC"
);
$personal = $stmt->fetchAll();
?>

<!-- HTML -->
<div class="titulo-con-boton">
  <h2 class="titulo-centrado">Personal de Mantenimiento</h2>
  <button type="button" onclick="mostrarFormMantenimiento()" class="btn-primario btn-nuevo-oficial">+ Nuevo Personal</button>
</div>

<!-- ✅ FORMULARIO PRINCIPAL (CORREGIDO) -->
<div id="formDivMantenimiento" class="modal-overlay" style="display:none;">
  <div class="modal-form modal-form-amplio">
    <h3>Agregar/Editar Personal de Mantenimiento</h3>
    <form id="mantenimientoForm" enctype="multipart/form-data">
      <input type="hidden" name="id" id="pmId">
      
      <!-- DATOS GENERALES -->
      <div class="form-grid">
        <div class="form-group">
          <label>Nombre completo:</label>
          <input type="text" name="nombre" id="pmNombre" required>
        </div>
        <div class="form-group">
          <label>Cargo:</label>
              <select name="cargo" id="pmCargo" required>
          <option value="Eléctrica">Eléctrica</option>
          <option value="Plomería">Plomería</option>
          <option value="Jardinería">Jardinería</option>
          <option value="Limpieza">Limpieza</option>
        </select>
        </div>
        <div class="form-group">
          <label>Año de ingreso:</label>
          <input type="number" name="anio_ingreso" id="pmAnioIngreso" min="1980" max="2030" required>
        </div>
      </div>

      <div class="form-group">
        <label>Notas adicionales:</label>
        <textarea name="notas" id="pmNotas" rows="3"></textarea>
      </div>

      <!-- NUEVOS CAMPOS -->
      <div class="form-group">
        <label>Estado Laboral:</label>
        <label for="pmEstadoLaboral"></label>
          <select name="estado_laboral" id="pmEstadoLaboral">
            <option value="Activo">Activo</option>
            <option value="Vacaciones">En Vacaciones</option>
            <option value="Incapacidad">En Incapacidad</option>
            <option value="Retirado">Retirado</option>
          </select>
      </div>
      <div class="form-group">
        <label>Área Asignada:</label>
        <select name="area_asignada" id="pmAreaAsignada">
          <option value="Eléctrica">Eléctrica</option>
          <option value="Plomería">Plomería</option>
          <option value="Jardinería">Jardinería</option>
          <option value="Limpieza">Limpieza</option>
          <option value="Otros">Otros</option>
        </select>
      </div>
      <div class="form-group">
        <label>Supervisor:</label>
        <input type="text" name="supervisor" id="pmSupervisor">
      </div>
      <div class="form-group">
        <label>Turno:</label>
        <select name="turno" id="pmTurno">
          <option value="Mañana">Mañana</option>
          <option value="Tarde">Tarde</option>
          <option value="Noche">Noche</option>
        </select>
      </div>
      <div class="form-group">
        <label>Horario:</label>
        <select name="horario" id="pmHorario">
          <option value="Lunes a Viernes 7:00-15:00">Lunes a Viernes 7:00-15:00</option>
          <option value="Lunes a Viernes 15:00-23:00">Lunes a Viernes 15:00-23:00</option>
          <option value="Fines de semana">Fines de semana</option>
        </select>
      </div>
      <div class="form-group">
        <label>Teléfono:</label>
        <input type="text" name="telefono" id="pmTelefono">
      </div>
      <div class="form-group">
        <label>Correo Electrónico:</label>
        <input type="email" name="correo" id="pmCorreo">
      </div>

      <!-- === SECCIÓN MÉDICA (dentro del mismo formulario) === -->
      <h4 style="margin-top:20px; color:#1a472a;">✚ Información Médica</h4>

      <div class="form-group">
        <label>Estado de salud actual:</label>
        <select name="estado_salud" id="pmEstadoSalud">
          <option value="">-- Seleccione --</option>
          <option value="estable">Estable</option>
          <option value="tratamiento">Con tratamiento crónico</option>
          <option value="recuperacion">En recuperación</option>
          <option value="otro">Otro</option>
        </select>
      </div>

      <div class="form-group">
        <label>
          <input type="checkbox" name="tiene_alergias" id="pmTieneAlergias" value="1">
          ¿Tiene alergias o restricciones médicas?
        </label>
        <div id="pmCampoAlergias" style="display:none; margin-top:8px;">
          <textarea name="detalle_alergias" placeholder="Ej: alergia al látex..."></textarea>
        </div>
      </div>


      <div class="form-group">
        <label>
          <input type="checkbox" name="tiene_accidente" id="pmTieneAccidente" value="1">
          ¿Ha tenido accidentes laborales registrados?
        </label>
        <div id="pmCampoAccidente" style="display:none; margin-top:8px;">
          <div class="form-grid">
            <div class="form-group">
              <label>Fecha del último accidente:</label>
              <input type="date" name="fecha_accidente">
            </div>
            <div class="form-group">
              <label>Descripción:</label>
              <textarea name="detalle_accidente" placeholder="Ej: caída en bodega..."></textarea>
            </div>
          </div>
        </div>
      </div>

      <!-- ARCHIVOS -->
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
      </div>

      <div class="form-buttons">
        <button type="submit" class="btn-primario">Guardar</button>
        <button type="button" onclick="cerrarFormMantenimiento()" class="btn-secundario">❌ Cancelar</button>
      </div>
    </form>
  </div>
</div>

<!-- TARJETAS -->
<div class="perfil-lista">
  <?php foreach ($personal as $p): ?>
    <div class="perfil-card perfil-card-expandido">
      <img src="<?= $p['foto'] ? htmlspecialchars($p['foto']) : 'img/default_user.png' ?>" 
           alt="<?= htmlspecialchars($p['nombre']) ?>" class="perfil-foto"
           onerror="this.src='img/default_user.png'">
      <h3><?= htmlspecialchars($p['nombre']) ?></h3>
      <p><strong>Cargo:</strong> <?= htmlspecialchars($p['cargo']) ?></p>
      <p><strong>Año de ingreso:</strong> <?= $p['año_ingreso'] ?></p>
      <p><strong>Años de servicio:</strong> <?= $p['años_servicio'] ?></p>
      
      <!-- Mostrar estado laboral con color -->
      <p><strong>Estado:</strong> 
        <?php
        $estado = $p['estado_laboral'] ?? 'Activo';
        $color = match(strtolower($estado)) {
            'activo' => 'green',
            'vacaciones' => 'orange',
            'incapacidad' => 'red',
            'retirado' => 'gray',
            default => 'black'
        };
        ?>
        <span style="color:<?= $color ?>; font-weight:bold;"><?= htmlspecialchars($estado) ?></span>
      </p>

      <?php if ($p['notas']): ?>
        <div class="perfil-notas">
          <strong>Notas:</strong>
          <p><?= nl2br(htmlspecialchars($p['notas'])) ?></p>
        </div>
      <?php endif; ?>

     <!-- Botón Ver Perfil (siempre visible, no solo si hay documento) -->
<button type="button" 
        onclick="window.location='descargar_perfil.php?id=<?= $p['id'] ?>'" 
        class="btn-documento">
  Ver Perfil (Word)
</button>


      <div class="perfil-acciones">
        <button type="button" onclick='editarMantenimiento(<?= json_encode($p, JSON_HEX_TAG | JSON_HEX_AMP) ?>)' class="btn-primario btn-pequeno">Editar</button>
        <button type="button" onclick="eliminarMantenimiento(<?= $p['id'] ?>)" class="btn-secundario btn-pequeno">Eliminar</button>
      </div>
    </div>
  <?php endforeach; ?>
</div>


<script>
// === MOSTRAR/OCULTAR CAMPOS MÉDICOS ===
document.addEventListener('DOMContentLoaded', function() {
  const alergias = document.getElementById('pmTieneAlergias');
  const epp = document.getElementById('pmUsaEPP');
  const accidente = document.getElementById('pmTieneAccidente');

  if (alergias) alergias.addEventListener('change', () => {
    document.getElementById('pmCampoAlergias').style.display = alergias.checked ? 'block' : 'none';
  });
  if (epp) epp.addEventListener('change', () => {
    document.getElementById('pmCampoEPP').style.display = epp.checked ? 'block' : 'none';
  });
  if (accidente) accidente.addEventListener('change', () => {
    document.getElementById('pmCampoAccidente').style.display = accidente.checked ? 'block' : 'none';
  });
});

// === FUNCIONES EXISTENTES (editar, eliminar, etc.) ===
function mostrarFormMantenimiento() {
  document.getElementById('formDivMantenimiento').style.display = 'flex';
  // Limpiar formulario
  document.getElementById('mantenimientoForm').reset();
  document.getElementById('pmId').value = '';
}

function cerrarFormMantenimiento() {
  document.getElementById('formDivMantenimiento').style.display = 'none';
}

function editarMantenimiento(data) {
  const fields = ['id','nombre','cargo','anio','notas','estado_laboral','area_asignada','supervisor','turno','horario','telefono','correo','estado_salud','detalle_alergias','tipo_epp','ultima_evaluacion','proxima_evaluacion','detalle_accidente'];
  fields.forEach(f => {
    const el = document.getElementById('pm'+f.charAt(0).toUpperCase() + f.slice(1));
    if (el) {
      if (el.type === 'checkbox') {
        el.checked = data[f.replace('pm','').toLowerCase()] == 1;
      } else if (el.tagName === 'SELECT') {
        el.value = data[f.replace('pm','').toLowerCase()] || '';
      } else {
        el.value = data[f.replace('pm','').toLowerCase()] || '';
      }
    }
  });

  // Manejar checkboxes especiales
  document.getElementById('pmTieneAlergias').checked = data.tiene_alergias == 1;
  document.getElementById('pmUsaEPP').checked = data.usa_epp == 1;
  document.getElementById('pmTieneAccidente').checked = data.tiene_accidente == 1;

  // Mostrar/ocultar campos si es necesario
  setTimeout(() => {
    document.getElementById('pmCampoAlergias').style.display = data.tiene_alergias ? 'block' : 'none';
    document.getElementById('pmCampoEPP').style.display = data.usa_epp ? 'block' : 'none';
    document.getElementById('pmCampoAccidente').style.display = data.tiene_accidente ? 'block' : 'none';
  }, 100);

  mostrarFormMantenimiento();
}

function eliminarMantenimiento(id) {
  if (confirm('¿Eliminar este registro?')) {
    fetch('', {
      method: 'POST',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
      body: 'delete=' + id
    }).then(() => location.reload());
  }
}

// === GUARDAR FORMULARIO ===
document.getElementById('mantenimientoForm').addEventListener('submit', function(e) {
  e.preventDefault();
  const formData = new FormData(this);
  formData.append('ajax', '1');
  
  fetch('', {
    method: 'POST',
    body: formData
  })
  .then(res => res.text())
  .then(txt => {
    if (txt === 'OK') {
      cerrarFormMantenimiento();
      location.reload();
    } else {
      alert('Error: ' + txt);
    }
  });
});
<!-- Modal de Ver Perfil -->
<div id="perfilModal" class="modal-overlay" style="display:none;">
  <div class="modal-form modal-form-amplio">
    <h3>👤 Perfil Completo</h3>
    
    <div class="perfil-contenido">
      <img id="perfilFoto" src="" alt="Foto" class="perfil-foto-modal">
      
      <div class="perfil-datos">
        <p><strong>Nombre:</strong> <span id="perfilNombre"></span></p>
        <p><strong>Cargo:</strong> <span id="perfilCargo"></span></p>
        <p><strong>Año de ingreso:</strong> <span id="perfilAnioIngreso"></span></p>
        <p><strong>Años de servicio:</strong> <span id="perfilAniosServicio"></span></p>
        
        <h4 style="margin-top:15px; color:#1a472a;">📋 Datos Laborales</h4>
        <p><strong>Estado laboral:</strong> <span id="perfilEstadoLaboral"></span></p>
        <p><strong>Área asignada:</strong> <span id="perfilAreaAsignada"></span></p>
        <p><strong>Supervisor:</strong> <span id="perfilSupervisor"></span></p>
        <p><strong>Turno:</strong> <span id="perfilTurno"></span></p>
        <p><strong>Horario:</strong> <span id="perfilHorario"></span></p>
        <p><strong>Teléfono:</strong> <span id="perfilTelefono"></span></p>
        <p><strong>Correo:</strong> <span id="perfilCorreo"></span></p>
        
        <h4 style="margin-top:15px; color:#c00;">✚ Información Médica</h4>
        <p><strong>Estado de salud:</strong> <span id="perfilEstadoSalud"></span></p>
        <p><strong>Alergias:</strong> <span id="perfilAlergias"></span></p>
        <p><strong>Accidentes laborales:</strong> <span id="perfilAccidentes"></span></p>
      </div>
    </div>

    <div class="form-buttons" style="margin-top:20px;">
      <button type="button" onclick="cerrarPerfilModal()" class="btn-secundario">Cerrar</button>
    </div>
  </div>
</div>
</script>