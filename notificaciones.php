<?php
require 'bd.php';

// Crear notificación
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax'])) {
  $titulo = trim($_POST['titulo']);
  $mensaje = trim($_POST['mensaje']);
  $tipo = $_POST['tipo'];

  $stmt = $pdo->prepare("INSERT INTO notificaciones (titulo, mensaje, tipo) VALUES (?, ?, ?)");
  $stmt->execute([$titulo, $mensaje, $tipo]);
  exit('OK');
}

// Eliminar notificación
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
  $id = intval($_POST['delete']);
  $stmt = $pdo->prepare("DELETE FROM notificaciones WHERE id = ?");
  $stmt->execute([$id]);
  exit('OK');
}

// Listar notificaciones
$stmt = $pdo->query("SELECT * FROM notificaciones ORDER BY fecha DESC");
$notificaciones = $stmt->fetchAll();
?>

<div class="titulo-con-boton">
  <h2 class="titulo-centrado">🔔 Notificaciones</h2>
  <button onclick="mostrarFormNotificacion()" class="btn btn-primario btn-nuevo-oficial">+ Nueva Notificación</button>
</div>

<div class="perfil-lista">
  <?php foreach ($notificaciones as $n): ?>
    <div class="notificacion <?= htmlspecialchars($n['tipo']) ?>">
      <h4><?= htmlspecialchars($n['titulo']) ?></h4>
      <p><?= nl2br(htmlspecialchars($n['mensaje'])) ?></p>
      <small><em><?= date('d M Y H:i', strtotime($n['fecha'])) ?></em></small>
      <div class="perfil-acciones">
        <button onclick="eliminarNotificacion(<?= $n['id'] ?>)" class="btn btn-secundario btn-pequeno">Eliminar</button>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<!-- Modal -->
<div id="formDivNotificacion" class="modal-overlay">
  <div class="modal-form">
    <h3>Agregar Notificación</h3>
    <form id="notificacionForm">
      <div class="form-group">
        <label>Título:</label>
        <input type="text" name="titulo" required>
      </div>
      <div class="form-group">
        <label>Mensaje:</label>
        <textarea name="mensaje" rows="4" required></textarea>
      </div>
      <div class="form-group">
        <label>Tipo:</label>
        <select name="tipo" required>
          <option value="">Seleccione...</option>
          <option value="urgente">Urgente</option>
          <option value="informativa">Informativa</option>
          <option value="administrativa">Administrativa</option>
        </select>
      </div>
      <div class="form-buttons">
        <button type="submit" class="btn-primario">Guardar</button>
        <button type="button" onclick="cerrarFormNotificacion()" class="btn-secundario">Cancelar</button>
      </div>
    </form>
  </div>
</div>
