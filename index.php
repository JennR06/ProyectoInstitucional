<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sistema Talento Humano - Liceo Militar de Honduras</title>
  <!-- Hoja de estilos principal -->
  <link rel="stylesheet" href="styles.css">
  <link rel="stylesheet" href="index.php">
  <link rel="stylesheet" href="bd.php">

<section id="historial" class="seccion">
  <h2>Historial de Oficiales</h2>

  <?php
    require 'db.php';

    // 1) Crear o actualizar
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
      $id     = $_POST['id'] ?: null;
      $nombre = trim($_POST['nombre']);
      $rango  = trim($_POST['rango']);
      $anio   = intval($_POST['años_asignado']);

      if ($id) {
        // UPDATE
        $stmt = $pdo->prepare(
          "UPDATE oficiales 
             SET nombre = ?, rango = ?, años_asignado = ? 
           WHERE id = ?"
        );
        $stmt->execute([$nombre, $rango, $anio, $id]);
      } else {
        // INSERT
        $stmt = $pdo->prepare(
          "INSERT INTO oficiales (nombre, rango, años_asignado) 
           VALUES (?, ?, ?)"
        );
        $stmt->execute([$nombre, $rango, $anio]);
      }
    }

    // 2) Eliminar
    if (isset($_GET['delete'])) {
      $stmt = $pdo->prepare("DELETE FROM oficiales WHERE id = ?");
      $stmt->execute([ $_GET['delete'] ]);
    }

    // 3) Leer todos
    $stmt    = $pdo->query(
      "SELECT *, YEAR(CURDATE()) - años_asignado AS años_servicio 
         FROM oficiales
         ORDER BY años_asignado DESC"
    );
    $oficiales = $stmt->fetchAll();
  ?>

  <!-- Botón para desplegar formulario -->
  <button onclick="mostrarForm()" class="btn-primario">+ Nuevo Oficial</button>

  <!-- Formulario oculto de Añadir/Editar -->
  <div id="formDiv" class="form-crud" style="display:none;">
    <form method="POST">
      <input type="hidden" name="id" id="ofId">
      <input type="text"   name="nombre"             id="ofNombre"
             placeholder="Nombre completo" required>
      <input type="text"   name="rango"              id="ofRango"
             placeholder="Rango militar"    required>
      <input type="number" name="años_asignado"    id="ofaños_asignado"
             placeholder="Año de asignación" required>
      <button type="submit" name="save" class="btn-primario">Guardar</button>
      <button type="button" onclick="cerrarForm()" class="btn-secundario">Cancelar</button>
    </form>
  </div>

  <!-- Tabla de Oficiales -->
  <table>
    <thead>
      <tr>
        <th>Nombre</th>
        <th>Rango</th>
        <th>Año asignado</th>
        <th>Años de servicio</th>
        <th>Acciones</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($oficiales as $o): ?>
        <tr>
          <td><?= htmlspecialchars($o['nombre']) ?></td>
          <td><?= htmlspecialchars($o['rango']) ?></td>
          <td><?= $o['años_asignado'] ?></td>
          <td><?= $o['años_servicio'] ?></td>
          <td>
            <button
              onclick='editar(
                <?= $o['id'] ?>,
                "<?= addslashes($o['nombre']) ?>",
                "<?= addslashes($o['rango']) ?>",
                <?= $o['años_asignado'] ?>
              )'
              class="btn-primario btn-pequeno"
            >Editar</button>

            <a
              href="?delete=<?= $o['id'] ?>"
              onclick="return confirm('¿Eliminar este oficial?');"
              class="btn-secundario btn-pequeno"
            >Eliminar</a>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <!-- JS local para mostrar/ocultar y precargar formulario -->
  <script>
    function mostrarForm() {
      document.getElementById('formDiv').style.display = 'block';
    }
    function cerrarForm() {
      document.getElementById('formDiv').style.display = 'none';
      document.getElementById('ofId').value = '';
      document.getElementById('ofNombre').value = '';
      document.getElementById('ofRango').value = '';
      document.getElementById('ofaños_asignado').value = '';
    }
    function editar(id, nombre, rango, anio) {
      document.getElementById('ofId').value     = id;
      document.getElementById('ofNombre').value = nombre;
      document.getElementById('ofRango').value  = rango;
      document.getElementById('ofaños_asignado').value   = años;
      mostrarForm();
    }
  </script>
</section>

