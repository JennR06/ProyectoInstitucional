<?php
require 'bd.php';

// 1) Crear o actualizar (solo si POST AJAX)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax'])) {
  $id     = $_POST['id'] ?: null;
  $nombre = trim($_POST['nombre']);
  $cargo  = trim($_POST['cargo']);
  $anio   = intval($_POST['año_ingreso']);

  if ($id) {
    $stmt = $pdo->prepare(
      "UPDATE mantenimiento SET nombre = ?, cargo = ?, año_ingreso = ? WHERE id = ?"
    );
    $stmt->execute([$nombre, $cargo, $anio, $id]);
  } else {
    $stmt = $pdo->prepare(
      "INSERT INTO mantenimiento (nombre, cargo, año_ingreso) VALUES (?, ?, ?)"
    );
    $stmt->execute([$nombre, $cargo, $anio]);
  }
  exit('OK');
}

// 2) Eliminar (AJAX)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
  $stmt = $pdo->prepare("DELETE FROM mantenimiento WHERE id = ?");
  $stmt->execute([ $_POST['delete'] ]);
  exit('OK');
}

// 3) Leer todos
$stmt    = $pdo->query(
  "SELECT *, YEAR(CURDATE()) - año_ingreso AS años_servicio 
     FROM mantenimiento
     ORDER BY año_ingreso DESC"
);
$personal = $stmt->fetchAll();
?>

<div class="titulo-con-boton">
  <h2 class="titulo-centrado">Personal de Mantenimiento</h2>
  <button onclick="mostrarFormMantenimiento()" class="btn-primario btn-nuevo-oficial">+ Nuevo Personal</button>
</div>

<!-- Formulario oculto de Añadir/Editar -->
<div id="formDivMantenimiento" class="form-crud" style="display:none;">
  <form id="mantenimientoForm">
    <input type="hidden" name="id" id="pmId">
    <input type="text"   name="nombre" id="pmNombre" placeholder="Nombre completo" required>
    <input type="text"   name="cargo"  id="pmCargo"  placeholder="Cargo" required>
    <input type="number" name="año_ingreso" id="pmAñoIngreso" placeholder="Año de ingreso" required>
    <button type="submit" class="btn-primario">Guardar</button>
    <button type="button" onclick="cerrarFormMantenimiento()" class="btn-secundario">Cancelar</button>
  </form>
</div>

<div class="perfil-lista">
  <?php foreach ($personal as $p): ?>
    <div class="perfil-card">
    <img 
    src="<?= $p['foto'] ? htmlspecialchars($p['foto']) : 'img/default_user.png' ?>" 
    alt="Foto de <?= htmlspecialchars($p['nombre']) ?>" 
    class="perfil-foto"
    >
      <h3><?= htmlspecialchars($p['nombre']) ?></h3>
      <p><strong>Cargo:</strong> <?= htmlspecialchars($p['cargo']) ?></p>
      <p><strong>Año de ingreso:</strong> <?= $p['año_ingreso'] ?></p>
      <p><strong>Años de servicio:</strong> <?= $p['años_servicio'] ?></p>
      <div class="perfil-acciones">
        <button
          onclick="editarMantenimiento(
            <?= $p['id'] ?>,
            '<?= addslashes($p['nombre']) ?>',
            '<?= addslashes($p['cargo']) ?>',
            <?= $p['año_ingreso'] ?>
          )"
          class="btn-primario btn-pequeno"
        >Editar</button>
        <button
          onclick="eliminarMantenimiento(<?= $p['id'] ?>)"
          class="btn-secundario btn-pequeno"
        >Eliminar</button>
      </div>
    </div>
  <?php endforeach; ?>
</div>
