<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'bd.php';

// 1) Crear o actualizar (solo si POST AJAX)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax'])) {
  $id      = $_POST['id'] ?: null;
  $nombre  = trim($_POST['nombre']);
  $cargo   = trim($_POST['cargo']);
  $anio    = intval($_POST['anio_ingreso']);
  $notas   = trim($_POST['notas']);

  // Manejo de imagen de perfil
  $foto = null;
  if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
    $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    
    if (!in_array(strtolower($ext), $allowed)) {
      exit('ERROR: Solo se permiten imágenes');
    }
    
    if (!file_exists('img')) {
      mkdir('img', 0777, true);
    }
    
    $nombre_archivo = uniqid('mantenimiento_') . '.' . $ext;
    $ruta_destino = 'img/' . $nombre_archivo;
    
    if (move_uploaded_file($_FILES['foto']['tmp_name'], $ruta_destino)) {
      $foto = $ruta_destino;
    }
  }

  // Manejo de documento PDF
  $documento = null;
  if (isset($_FILES['documento']) && $_FILES['documento']['error'] === UPLOAD_ERR_OK) {
    $ext = pathinfo($_FILES['documento']['name'], PATHINFO_EXTENSION);
    
    if (strtolower($ext) !== 'pdf') {
      exit('ERROR: Solo se permiten archivos PDF');
    }
    
    if (!file_exists('documentos')) {
      mkdir('documentos', 0777, true);
    }
    
    $nombre_doc = uniqid('doc_mant_') . '.pdf';
    $ruta_doc = 'documentos/' . $nombre_doc;
    
    if (move_uploaded_file($_FILES['documento']['tmp_name'], $ruta_doc)) {
      $documento = $ruta_doc;
    }
  }

  if ($id) {
    // Actualizar registro existente (sin salario)
    if ($foto && $documento) {
      $stmt = $pdo->prepare(
        "UPDATE mantenimiento SET nombre = ?, cargo = ?, año_ingreso = ?, foto = ?, notas = ?, documento = ? WHERE id = ?"
      );
      $stmt->execute([$nombre, $cargo, $anio, $foto, $notas, $documento, $id]);
    } elseif ($foto) {
      $stmt = $pdo->prepare(
        "UPDATE mantenimiento SET nombre = ?, cargo = ?, año_ingreso = ?, foto = ?, notas = ? WHERE id = ?"
      );
      $stmt->execute([$nombre, $cargo, $anio, $foto, $notas, $id]);
    } elseif ($documento) {
      $stmt = $pdo->prepare(
        "UPDATE mantenimiento SET nombre = ?, cargo = ?, año_ingreso = ?, notas = ?, documento = ? WHERE id = ?"
      );
      $stmt->execute([$nombre, $cargo, $anio, $notas, $documento, $id]);
    } else {
      $stmt = $pdo->prepare(
        "UPDATE mantenimiento SET nombre = ?, cargo = ?, año_ingreso = ?, notas = ? WHERE id = ?"
      );
      $stmt->execute([$nombre, $cargo, $anio, $notas, $id]);
    }
  } else {
    // Insertar nuevo registro (sin salario)
    $stmt = $pdo->prepare(
      "INSERT INTO mantenimiento (nombre, cargo, año_ingreso, foto, notas, documento) VALUES (?, ?, ?, ?, ?, ?)"
    );
    $stmt->execute([$nombre, $cargo, $anio, $foto, $notas, $documento]);
  }
  exit('OK');
}

// 2) Eliminar (AJAX)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
  // Obtener rutas de archivos antes de eliminar
  $stmt = $pdo->prepare("SELECT foto, documento FROM mantenimiento WHERE id = ?");
  $stmt->execute([$_POST['delete']]);
  $personal = $stmt->fetch();
  
  // Eliminar archivos físicos
  if ($personal['foto'] && file_exists($personal['foto'])) {
    unlink($personal['foto']);
  }
  if ($personal['documento'] && file_exists($personal['documento'])) {
    unlink($personal['documento']);
  }
  
  // Eliminar registro de BD
  $stmt = $pdo->prepare("DELETE FROM mantenimiento WHERE id = ?");
  $stmt->execute([$_POST['delete']]);
  exit('OK');
}

// 3) Leer todos
$stmt = $pdo->query(
  "SELECT *, YEAR(CURDATE()) - año_ingreso AS años_servicio 
   FROM mantenimiento
   ORDER BY año_ingreso DESC"
);
$personal = $stmt->fetchAll();
?>

<div class="titulo-con-boton">
  <h2 class="titulo-centrado">Personal de Mantenimiento</h2>
  <button type="button" onclick="mostrarFormMantenimiento()" class="btn-primario btn-nuevo-oficial">+ Nuevo Personal</button>
</div>

<!-- Formulario mejorado -->
<div id="formDivMantenimiento" class="modal-overlay" style="display:none;">
  <div class="modal-form modal-form-amplio">
    <h3>Agregar/Editar Personal de Mantenimiento</h3>
    <form id="mantenimientoForm" enctype="multipart/form-data">
      <input type="hidden" name="id" id="pmId">
      
      <div class="form-grid">
        <div class="form-group">
          <label>Nombre completo:</label>
          <input type="text" name="nombre" id="pmNombre" placeholder="Ej: Carlos López" required>
        </div>
        
        <div class="form-group">
          <label>Cargo:</label>
          <input type="text" name="cargo" id="pmCargo" placeholder="Ej: Electricista" required>
        </div>
        
        <div class="form-group">
          <label>Año de ingreso:</label>
          <input type="number" name="anio_ingreso" id="pmAnioIngreso" placeholder="Ej: 2018" required min="1980" max="2030">
        </div>
      </div>
      
      <div class="form-group">
        <label>Notas adicionales:</label>
        <textarea name="notas" id="pmNotas" rows="4" placeholder="Observaciones, especialidades, turnos, etc."></textarea>
      </div>
      
      <div class="form-grid">
        <div class="form-group">
          <label>Fotografía:</label>
          <input type="file" name="foto" id="pmFoto" accept="image/*">
          <small>Formatos: JPG, PNG, GIF</small>
        </div>
        
        <div class="form-group">
          <label>Documento (PDF):</label>
          <input type="file" name="documento" id="pmDocumento" accept=".pdf">
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

<!-- Tarjetas de personal mejoradas -->
<div class="perfil-lista">
  <?php foreach ($personal as $p): ?>
    <div class="perfil-card perfil-card-expandido">
      <img 
        src="<?= $p['foto'] ? htmlspecialchars($p['foto']) : 'img/default_user.png' ?>" 
        alt="Foto de <?= htmlspecialchars($p['nombre']) ?>" 
        class="perfil-foto"
        onerror="this.src='img/default_user.png'"
      >
      <h3><?= htmlspecialchars($p['nombre']) ?></h3>
      <p><strong>Cargo:</strong> <?= htmlspecialchars($p['cargo']) ?></p>
      <p><strong>Año de ingreso:</strong> <?= $p['año_ingreso'] ?></p>
      <p><strong>Años de servicio:</strong> <?= $p['años_servicio'] ?></p>

      <?php if ($p['notas']): ?>
        <div class="perfil-notas">
          <strong>Notas:</strong>
          <p><?= nl2br(htmlspecialchars($p['notas'])) ?></p>
        </div>
      <?php endif; ?>
      
      <?php if ($p['documento']): ?>
        <div class="perfil-documento" style="display:flex;gap:0.5rem;flex-wrap:wrap;align-items:center;">
          <!-- Ver CV -->
          <?php if (!empty($p['documento'])): ?>
            <a href="<?= htmlspecialchars($p['documento']) ?>" target="_blank" class="btn-documento" aria-label="Ver CV de <?= htmlspecialchars($p['nombre']) ?>">
              Ver Perfil
            </a>
          <?php endif; ?>

          <!-- Ver Constancia -->
          <?php if (!empty($p['documento2'])): ?>
            <a href="<?= htmlspecialchars($p['documento2']) ?>" target="_blank" class="btn-documento" aria-label="Ver Constancia de <?= htmlspecialchars($p['nombre']) ?>">
              Ver Constancia
            </a>
          <?php endif; ?>
        </div>
      <?php endif; ?>
      
      <div class="perfil-acciones">
        <!-- Ver Perfil: abre modal con la ficha del personal -->
      
      </button>

        <button
          type="button"
          onclick='editarMantenimiento(<?= json_encode([
            "id" => $p["id"],
            "nombre" => $p["nombre"],
            "cargo" => $p["cargo"],
            "anio" => $p["año_ingreso"],
            "notas" => $p["notas"]
          ]) ?>)'
          class="btn-primario btn-pequeno"
        >Editar</button>
        <button
          type="button"
          onclick="eliminarMantenimiento(<?= $p['id'] ?>)"
          class="btn-secundario btn-pequeno"
        >Eliminar</button>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<!-- Modal: Ficha Personal de Mantenimiento -->
<div id="perfilMantenimientoModal" class="modal-overlay" style="display:none;">
  <div class="modal-form modal-form-amplio" role="dialog" aria-modal="true" aria-labelledby="perfilMantenimientoTitulo">
    <h3 id="perfilMantenimientoTitulo">FICHA - Personal de Mantenimiento</h3>

    <div class="ficha-grid">
      <section>
        <h4>DATOS PERSONALES</h4>
        <p><strong>Nombre completo:</strong> <span id="m_f_nombre">—</span></p>
        <p><strong>Documento de identidad:</strong> <span id="m_f_documento_identidad">—</span></p>
        <p><strong>Fecha de nacimiento:</strong> <span id="m_f_fecha_nacimiento">—</span></p>
        <p><strong>Nacionalidad:</strong> <span id="m_f_nacionalidad">—</span></p>
        <p><strong>Género:</strong> <span id="m_f_genero">—</span></p>
        <p><strong>Estado civil:</strong> <span id="m_f_estado_civil">—</span></p>
        <p><strong>Teléfono(s):</strong> <span id="m_f_telefonos">—</span></p>
        <p><strong>Correo personal:</strong> <span id="m_f_correo_personal">—</span></p>
        <p><strong>Correo institucional:</strong> <span id="m_f_correo_institucional">—</span></p>
        <p><strong>Dirección domiciliaria:</strong> <span id="m_f_direccion">—</span></p>
      </section>

      <section>
        <h4>ZONAS / EDIFICIOS A SU CARGO</h4>
        <p id="m_f_zonas">—</p>

        <h4>ACTIVIDADES PRINCIPALES</h4>
        <p id="m_f_actividades">—</p>
      </section>
    </div>

    <div class="form-buttons" style="margin-top:0.8rem;">
      <button type="button" onclick="cerrarPerfilMantenimiento()" class="btn btn-secundario">Cerrar</button>
    </div>
  </div>
</div>

<script>
// Parsear data-doc y mostrar modal
function mostrarPerfilMantenimientoFromButton(btn){
  try {
    const json = btn.getAttribute('data-doc') || '{}';
    const data = JSON.parse(json);
    mostrarPerfilMantenimiento(data);
  } catch (e) {
    console.error('Error al leer data-doc:', e);
    alert('Error al abrir el perfil.');
  }
}

function mostrarPerfilMantenimiento(data) {
  try {
    document.getElementById('m_f_nombre').textContent = data.nombre || '—';
    document.getElementById('m_f_documento_identidad').textContent = data.documento_identidad || '—';
    document.getElementById('m_f_fecha_nacimiento').textContent = data.fecha_nacimiento || '—';
    document.getElementById('m_f_nacionalidad').textContent = data.nacionalidad || '—';
    document.getElementById('m_f_genero').textContent = data.genero || '—';
    document.getElementById('m_f_estado_civil').textContent = data.estado_civil || '—';
    document.getElementById('m_f_telefonos').textContent = data.telefonos || '—';
    document.getElementById('m_f_correo_personal').textContent = data.correo_personal || '—';
    document.getElementById('m_f_correo_institucional').textContent = data.correo_institucional || '—';
    document.getElementById('m_f_direccion').textContent = data.direccion || '—';

    // zonas y actividades (si vienen como texto coma-sep o texto libre)
    document.getElementById('m_f_zonas').textContent = data.zonas || '—';
    document.getElementById('m_f_actividades').textContent = data.actividades || '—';

    const modal = document.getElementById('perfilMantenimientoModal');
    if(!modal) throw new Error('Modal no encontrado');
    modal.style.display = 'block';
    modal.setAttribute('aria-hidden','false');

    // cerrar con Esc
    const escHandler = (e) => { if (e.key === 'Escape') cerrarPerfilMantenimiento(); };
    document.addEventListener('keydown', escHandler);
    modal._escHandler = escHandler;
  } catch (err) {
    console.error('mostrarPerfilMantenimiento error:', err);
    alert('No se pudo abrir el perfil.');
  }
}

function cerrarPerfilMantenimiento() {
  const modal = document.getElementById('perfilMantenimientoModal');
  if(!modal) return;
  modal.style.display = 'none';
  modal.setAttribute('aria-hidden','true');
  if (modal._escHandler) {
    document.removeEventListener('keydown', modal._escHandler);
    modal._escHandler = null;
  }
}

// Cerrar si clic afuera del contenido
document.addEventListener('click', function(e){
  const modal = document.getElementById('perfilMantenimientoModal');
  if(!modal || modal.style.display !== 'block') return;
  const content = modal.querySelector('.modal-form');
  if(!content) return;
  if(!content.contains(e.target)) cerrarPerfilMantenimiento();
});
</script>