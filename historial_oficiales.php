<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'bd.php';

// 1) Crear o actualizar (solo si POST AJAX)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax'])) {
  $id      = $_POST['id'] ?: null;
  $nombre  = trim($_POST['nombre']);
  $rango   = trim($_POST['rango']);
  $anio    = intval($_POST['anios_asignado']);
  $salario = floatval($_POST['salario']); // ✅ NUEVO
  $notas   = trim($_POST['notas']);       // ✅ NUEVO

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
    
    $nombre_archivo = uniqid('oficial_') . '.' . $ext;
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
    
    $nombre_doc = uniqid('doc_') . '.pdf';
    $ruta_doc = 'documentos/' . $nombre_doc;
    
    if (move_uploaded_file($_FILES['documento']['tmp_name'], $ruta_doc)) {
      $documento = $ruta_doc;
    }
  }

  if ($id) {
    // Actualizar registro existente
    if ($foto && $documento) {
      $stmt = $pdo->prepare(
        "UPDATE oficiales SET nombre = ?, rango = ?, años_asignado = ?, salario = ?, notas = ?, foto = ?, documento = ? WHERE id = ?"
      );
      $stmt->execute([$nombre, $rango, $anio, $salario, $notas, $foto, $documento, $id]);
    } elseif ($foto) {
      $stmt = $pdo->prepare(
        "UPDATE oficiales SET nombre = ?, rango = ?, años_asignado = ?, salario = ?, notas = ?, foto = ? WHERE id = ?"
      );
      $stmt->execute([$nombre, $rango, $anio, $salario, $notas, $foto, $id]);
    } elseif ($documento) {
      $stmt = $pdo->prepare(
        "UPDATE oficiales SET nombre = ?, rango = ?, años_asignado = ?, salario = ?, notas = ?, documento = ? WHERE id = ?"
      );
      $stmt->execute([$nombre, $rango, $anio, $salario, $notas, $documento, $id]);
    } else {
      $stmt = $pdo->prepare(
        "UPDATE oficiales SET nombre = ?, rango = ?, años_asignado = ?, salario = ?, notas = ? WHERE id = ?"
      );
      $stmt->execute([$nombre, $rango, $anio, $salario, $notas, $id]);
    }
  } else {
    // Insertar nuevo registro
    $stmt = $pdo->prepare(
      "INSERT INTO oficiales (nombre, rango, años_asignado, salario, notas, foto, documento) VALUES (?, ?, ?, ?, ?, ?, ?)"
    );
    $stmt->execute([$nombre, $rango, $anio, $salario, $notas, $foto, $documento]);
  }
  exit('OK');
}

// 2) Eliminar (AJAX)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
  // Obtener rutas de archivos antes de eliminar
  $stmt = $pdo->prepare("SELECT foto, documento FROM oficiales WHERE id = ?");
  $stmt->execute([$_POST['delete']]);
  $oficial = $stmt->fetch();
  
  // Eliminar archivos físicos
  if ($oficial['foto'] && file_exists($oficial['foto'])) {
    unlink($oficial['foto']);
  }
  if ($oficial['documento'] && file_exists($oficial['documento'])) {
    unlink($oficial['documento']);
  }
  
  // Eliminar registro de BD
  $stmt = $pdo->prepare("DELETE FROM oficiales WHERE id = ?");
  $stmt->execute([$_POST['delete']]);
  exit('OK');
}

// 3) Leer todos
$stmt = $pdo->query(
  "SELECT *, YEAR(CURDATE()) - años_asignado AS años_servicio 
   FROM oficiales
   ORDER BY años_asignado DESC"
);
$oficiales = $stmt->fetchAll();
?>

<div class="titulo-con-boton">
  <h2 class="titulo-centrado">Historial de Oficiales</h2>
  <button type="button" onclick="mostrarFormOficial()" class="btn-primario btn-nuevo-oficial">+ Nuevo Oficial</button>
</div>

<!-- Formulario -->
<div id="formDivOficial" class="modal-overlay" style="display:none;">
  <div class="modal-form modal-form-amplio">
    <h3>📋 Agregar/Editar Oficial</h3>
    <form id="oficialForm" enctype="multipart/form-data">
      <input type="hidden" name="id" id="ofId">
      
      <div class="form-grid">
        <div class="form-group">
          <label>Nombre completo:</label>
          <input type="text" name="nombre" id="ofNombre" placeholder="Ej: Juan Carlos Gómez" required>
        </div>
        
        <div class="form-group">
          <label>Rango militar:</label>
          <input type="text" name="rango" id="ofRango" placeholder="Ej: Capitán" required>
        </div>
        
        <div class="form-group">
          <label>Año de asignación:</label>
          <input type="number" name="anios_asignado" id="ofAniosAsignado" placeholder="Ej: 2020" required min="1980" max="2030">
        </div>
        
        <div class="form-group">
          <label>Salario mensual (L):</label>
          <input type="number" name="salario" id="ofSalario" placeholder="Ej: 25000.00" step="0.01" min="0" required>
        </div>
      </div>
      
      <div class="form-group">
        <label>Notas adicionales:</label>
        <textarea name="notas" id="ofNotas" rows="4" placeholder="Observaciones, reconocimientos, historial, etc."></textarea>
      </div>
      
      <div class="form-grid">
        <div class="form-group">
          <label>Fotografía:</label>
          <input type="file" name="foto" id="ofFoto" accept="image/*">
          <small>Formatos: JPG, PNG, GIF</small>
        </div>
        
        <div class="form-group">
          <label>Documento (PDF):</label>
          <input type="file" name="documento" id="ofDocumento" accept=".pdf">
          <small>Hoja de vida, certificados, etc.</small>
        </div>
      </div>
      
      <div class="form-buttons">
        <button type="submit" class="btn-primario">Guardar</button>
        <button type="button" onclick="cerrarFormOficial()" class="btn-secundario">❌ Cancelar</button>
      </div>
    </form>
  </div>
</div>

<!-- Tarjetas de oficiales mejoradas -->
<div class="perfil-lista">
  <?php foreach ($oficiales as $o): ?>
    <div class="perfil-card perfil-card-expandido">
      <img 
        src="<?= $o['foto'] ? htmlspecialchars($o['foto']) : 'img/default_user.png' ?>" 
        alt="Foto de <?= htmlspecialchars($o['nombre']) ?>" 
        class="perfil-foto"
        onerror="this.src='img/default_user.png'"
      >
      <h3><?= htmlspecialchars($o['nombre']) ?></h3>
      <p><strong>Rango:</strong> <?= htmlspecialchars($o['rango']) ?></p>
      <p><strong>Año asignado:</strong> <?= $o['años_asignado'] ?></p>
      <p><strong>Años de servicio:</strong> <?= $o['años_servicio'] ?></p>
      <p><strong>Salario:</strong> L <?= number_format($o['salario'], 2) ?></p>

      <?php if ($o['notas']): ?>
        <div class="perfil-notas">
          <strong>Notas:</strong>
          <p><?= nl2br(htmlspecialchars($o['notas'])) ?></p>
        </div>
      <?php endif; ?>
      
      <?php if ($o['documento']): ?>
        <div class="perfil-documento">
          <a href="<?= htmlspecialchars($o['documento']) ?>" target="_blank" class="btn-documento">
            Ver Documento
          </a>
        </div>
      <?php endif; ?>
      
      <div class="perfil-acciones">
        <button
          type="button"
          onclick='editarOficial(<?= json_encode([
            "id" => $o["id"],
            "nombre" => $o["nombre"],
            "rango" => $o["rango"],
            "anio" => $o["años_asignado"],
            "salario" => $o["salario"],
            "notas" => $o["notas"]
          ]) ?>)'
          class="btn-primario btn-pequeno"
        >Editar</button>
        <button
          type="button"
          onclick="eliminarOficial(<?= $o['id'] ?>)"
          class="btn-secundario btn-pequeno"
        >Eliminar</button>
      </div>
    </div>
  <?php endforeach; ?>
</div>