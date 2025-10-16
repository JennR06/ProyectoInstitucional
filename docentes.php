<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'bd.php';

// 1) Crear o actualizar (solo si POST AJAX)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax'])) {
  $id              = $_POST['id'] ?: null;
  $nombre          = trim($_POST['nombre']);
  $especialidad    = trim($_POST['especialidad']);
  $anio            = intval($_POST['anio_ingreso']);
  $salario         = floatval($_POST['salario']);
  $notas           = trim($_POST['notas']);
  $nivel_educativo = trim($_POST['nivel_educativo']);
  $horario         = trim($_POST['horario']);

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
    
    $nombre_archivo = uniqid('docente_') . '.' . $ext;
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
    
    $nombre_doc = uniqid('doc_docente_') . '.pdf';
    $ruta_doc = 'documentos/' . $nombre_doc;
    
    if (move_uploaded_file($_FILES['documento']['tmp_name'], $ruta_doc)) {
      $documento = $ruta_doc;
    }
  }

  if ($id) {
    // Actualizar registro existente
    if ($foto && $documento) {
      $stmt = $pdo->prepare(
        "UPDATE docentes SET nombre = ?, especialidad = ?, año_ingreso = ?, foto = ?, salario = ?, notas = ?, documento = ?, nivel_educativo = ?, horario = ? WHERE id = ?"
      );
      $stmt->execute([$nombre, $especialidad, $anio, $foto, $salario, $notas, $documento, $nivel_educativo, $horario, $id]);
    } elseif ($foto) {
      $stmt = $pdo->prepare(
        "UPDATE docentes SET nombre = ?, especialidad = ?, año_ingreso = ?, foto = ?, salario = ?, notas = ?, nivel_educativo = ?, horario = ? WHERE id = ?"
      );
      $stmt->execute([$nombre, $especialidad, $anio, $foto, $salario, $notas, $nivel_educativo, $horario, $id]);
    } elseif ($documento) {
      $stmt = $pdo->prepare(
        "UPDATE docentes SET nombre = ?, especialidad = ?, año_ingreso = ?, salario = ?, notas = ?, documento = ?, nivel_educativo = ?, horario = ? WHERE id = ?"
      );
      $stmt->execute([$nombre, $especialidad, $anio, $salario, $notas, $documento, $nivel_educativo, $horario, $id]);
    } else {
      $stmt = $pdo->prepare(
        "UPDATE docentes SET nombre = ?, especialidad = ?, año_ingreso = ?, salario = ?, notas = ?, nivel_educativo = ?, horario = ? WHERE id = ?"
      );
      $stmt->execute([$nombre, $especialidad, $anio, $salario, $notas, $nivel_educativo, $horario, $id]);
    }
  } else {
    // Insertar nuevo registro
    $stmt = $pdo->prepare(
      "INSERT INTO docentes (nombre, especialidad, año_ingreso, foto, salario, notas, documento, nivel_educativo, horario) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );
    $stmt->execute([$nombre, $especialidad, $anio, $foto, $salario, $notas, $documento, $nivel_educativo, $horario]);
  }
  exit('OK');
}

// 2) Eliminar (AJAX)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
  // Obtener rutas de archivos antes de eliminar
  $stmt = $pdo->prepare("SELECT foto, documento FROM docentes WHERE id = ?");
  $stmt->execute([$_POST['delete']]);
  $docente = $stmt->fetch();
  
  // Eliminar archivos físicos
  if ($docente['foto'] && file_exists($docente['foto'])) {
    unlink($docente['foto']);
  }
  if ($docente['documento'] && file_exists($docente['documento'])) {
    unlink($docente['documento']);
  }
  
  // Eliminar registro de BD
  $stmt = $pdo->prepare("DELETE FROM docentes WHERE id = ?");
  $stmt->execute([$_POST['delete']]);
  exit('OK');
}

// 3) Leer todos
$stmt = $pdo->query(
  "SELECT *, YEAR(CURDATE()) - año_ingreso AS años_servicio 
   FROM docentes
   ORDER BY año_ingreso DESC"
);
$docentes = $stmt->fetchAll();
?>

<div class="titulo-con-boton">
  <h2 class="titulo-centrado">Docentes</h2>
  <button type="button" onclick="mostrarFormDocente()" class="btn-primario btn-nuevo-oficial">+ Nuevo Docente</button>
</div>

<!-- Formulario mejorado -->
<div id="formDivDocente" class="modal-overlay" style="display:none;">
  <div class="modal-form modal-form-amplio">
    <h3>Agregar/Editar Docente</h3>
    <form id="docenteForm" enctype="multipart/form-data">
      <input type="hidden" name="id" id="docId">
      
      <div class="form-grid">
        <div class="form-group">
          <label>Nombre completo:</label>
          <input type="text" name="nombre" id="docNombre" placeholder="Ej: María García López" required>
        </div>
        
        <div class="form-group">
          <label>Especialidad:</label>
          <input type="text" name="especialidad" id="docEspecialidad" placeholder="Ej: Matemáticas" required>
        </div>
        
        <div class="form-group">
          <label>Año de ingreso:</label>
          <input type="number" name="anio_ingreso" id="docAnioIngreso" placeholder="Ej: 2015" required min="1980" max="2030">
        </div>
        
        <div class="form-group">
          <label>Salario mensual (L):</label>
          <input type="number" name="salario" id="docSalario" placeholder="Ej: 18000.00" step="0.01" min="0" required>
        </div>
        
        <div class="form-group">
          <label>Nivel educativo:</label>
          <select name="nivel_educativo" id="docNivelEducativo" required>
            <option value="">Seleccione...</option>
            <option value="Licenciatura">Licenciatura</option>
            <option value="Maestría">Maestría</option>
            <option value="Doctorado">Doctorado</option>
            <option value="Técnico">Técnico</option>
          </select>
        </div>
        
        <div class="form-group">
          <label>Horario:</label>
          <input type="text" name="horario" id="docHorario" placeholder="Ej: Lunes a Viernes 7:00-12:00" required>
        </div>
      </div>
      
      <div class="form-group">
        <label>Notas adicionales:</label>
        <textarea name="notas" id="docNotas" rows="4" placeholder="Cursos impartidos, logros, observaciones, etc."></textarea>
      </div>
      
      <div class="form-grid">
        <div class="form-group">
          <label>Fotografía:</label>
          <input type="file" name="foto" id="docFoto" accept="image/*">
          <small>Formatos: JPG, PNG, GIF</small>
        </div>
        
        <div class="form-group">
          <label>Documento (PDF):</label>
          <input type="file" name="documento" id="docDocumento" accept=".pdf">
          <small>CV, títulos, certificaciones, etc.</small>
        </div>
      </div>
      
      <div class="form-buttons">
        <button type="submit" class="btn-primario">Guardar</button>
        <button type="button" onclick="cerrarFormDocente()" class="btn-secundario">❌ Cancelar</button>
      </div>
    </form>
  </div>
</div>

<!-- Tarjetas de docentes -->
<div class="perfil-lista">
  <?php foreach ($docentes as $d): ?>
    <div class="perfil-card perfil-card-expandido">
      <img 
        src="<?= $d['foto'] ? htmlspecialchars($d['foto']) : 'img/default_user.png' ?>" 
        alt="Foto de <?= htmlspecialchars($d['nombre']) ?>" 
        class="perfil-foto"
        onerror="this.src='img/default_user.png'"
      >
      <h3><?= htmlspecialchars($d['nombre']) ?></h3>
      <p><strong>Especialidad:</strong> <?= htmlspecialchars($d['especialidad']) ?></p>
      <p><strong>Nivel:</strong> <?= htmlspecialchars($d['nivel_educativo']) ?></p>
      <p><strong>Año de ingreso:</strong> <?= $d['año_ingreso'] ?></p>
      <p><strong>Años de servicio:</strong> <?= $d['años_servicio'] ?></p>
      <p><strong>Horario:</strong> <?= htmlspecialchars($d['horario']) ?></p>
      <p><strong>Salario:</strong> L <?= number_format($d['salario'], 2) ?></p>
      
      <?php if ($d['notas']): ?>
        <div class="perfil-notas">
          <strong>Notas:</strong>
          <p><?= nl2br(htmlspecialchars($d['notas'])) ?></p>
        </div>
      <?php endif; ?>
      
      <?php if ($d['documento']): ?>
        <div class="perfil-documento">
          <a href="<?= htmlspecialchars($d['documento']) ?>" target="_blank" class="btn-documento">
            Ver Documento
          </a>
        </div>
      <?php endif; ?>
      
      <div class="perfil-acciones">
        <button
          type="button"
          onclick='editarDocente(<?= json_encode([
            "id" => $d["id"],
            "nombre" => $d["nombre"],
            "especialidad" => $d["especialidad"],
            "anio" => $d["año_ingreso"],
            "salario" => $d["salario"],
            "notas" => $d["notas"],
            "nivel_educativo" => $d["nivel_educativo"],
            "horario" => $d["horario"]
          ]) ?>)'
          class="btn-primario btn-pequeno"
        >Editar</button>
        <button
          type="button"
          onclick="eliminarDocente(<?= $d['id'] ?>)"
          class="btn-secundario btn-pequeno"
        >Eliminar</button>
      </div>
    </div>
  <?php endforeach; ?>
</div>