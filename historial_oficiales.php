<?php
require 'bd.php';

// 1) Crear o actualizar (solo si POST AJAX)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax'])) {
  $id     = $_POST['id'] ?: null;
  $nombre = trim($_POST['nombre']);
  $rango  = trim($_POST['rango']);
  $anio   = intval($_POST['años_asignado']);

  // Manejo de imagen
  $foto = null;
  if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
    $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
    $nombre_archivo = uniqid('oficial_') . '.' . $ext;
    $ruta_destino = 'img/' . $nombre_archivo;
    if (move_uploaded_file($_FILES['foto']['tmp_name'], $ruta_destino)) {
      $foto = $ruta_destino;
    }
  }

  if ($id) {
    // Si se subió nueva foto, actualiza el campo
    if ($foto) {
      $stmt = $pdo->prepare(
        "UPDATE oficiales SET nombre = ?, rango = ?, años_asignado = ?, foto = ? WHERE id = ?"
      );
      $stmt->execute([$nombre, $rango, $anio, $foto, $id]);
    } else {
      $stmt = $pdo->prepare(
        "UPDATE oficiales SET nombre = ?, rango = ?, años_asignado = ? WHERE id = ?"
      );
      $stmt->execute([$nombre, $rango, $anio, $id]);
    }
  } else {
    $stmt = $pdo->prepare(
      "INSERT INTO oficiales (nombre, rango, años_asignado, foto) VALUES (?, ?, ?, ?)"
    );
    $stmt->execute([$nombre, $rango, $anio, $foto]);
  }
  exit('OK');
}

// 2) Eliminar (AJAX)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
  $stmt = $pdo->prepare("DELETE FROM oficiales WHERE id = ?");
  $stmt->execute([ $_POST['delete'] ]);
  exit('OK');
}

// 3) Leer todos
$stmt    = $pdo->query(
  "SELECT *, YEAR(CURDATE()) - años_asignado AS años_servicio 
     FROM oficiales
     ORDER BY años_asignado DESC"
);
$oficiales = $stmt->fetchAll();
?>

<div class="titulo-con-boton">
  <h2 class="titulo-centrado">Historial de Oficiales</h2>
  <button onclick="mostrarFormOficial()" class="btn-primario btn-nuevo-oficial">+ Nuevo Oficial</button>
</div>

<!-- Formulario oculto de Añadir/Editar -->
<div id="formDivOficial" class="form-crud" style="display:none;">
  <form id="oficialForm" enctype="multipart/form-data">
    <input type="hidden" name="id" id="ofId">
    <input type="text"   name="nombre" id="ofNombre" placeholder="Nombre completo" required>
    <input type="text"   name="rango"  id="ofRango"  placeholder="Rango militar" required>
    <input type="number" name="años_asignado" id="ofaños_asignado" placeholder="Año de asignación" required>
    <input type="file"   name="foto" id="ofFoto" accept="image/*">
    <button type="submit" class="btn-primario">Guardar</button>
    <button type="button" onclick="cerrarFormOficial()" class="btn-secundario">Cancelar</button>
  </form>
</div>

<div class="perfil-lista">
  <?php foreach ($oficiales as $o): ?>
    <div class="perfil-card">
    <img 
    src="<?= $o['foto'] ? htmlspecialchars($o['foto']) : 'img/default_user.png' ?>" 
    alt="Foto de <?= htmlspecialchars($o['nombre']) ?>" 
    class="perfil-foto"
    >
      <h3><?= htmlspecialchars($o['nombre']) ?></h3>
      <p><strong>Rango:</strong> <?= htmlspecialchars($o['rango']) ?></p>
      <p><strong>Año asignado:</strong> <?= $o['años_asignado'] ?></p>
      <p><strong>Años de servicio:</strong> <?= $o['años_servicio'] ?></p>
      <div class="perfil-acciones">
        <button
          onclick="editarOficial(
            <?= $o['id'] ?>,
            '<?= addslashes($o['nombre']) ?>',
            '<?= addslashes($o['rango']) ?>',
            <?= $o['años_asignado'] ?>
          )"
          class="btn-primario btn-pequeno"
        >Editar</button>
        <button
          onclick="eliminarOficial(<?= $o['id'] ?>)"
          class="btn-secundario btn-pequeno"
        >Eliminar</button>
      </div>
    </div>
  <?php endforeach; ?>
</div>
