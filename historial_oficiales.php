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
  $notas   = trim($_POST['notas']);

  // Obtener archivos existentes si vamos a actualizar
  $existing = ['foto' => null, 'documento' => null, 'estado' => null];
  if ($id) {
    $stmtEx = $pdo->prepare("SELECT foto, documento, estado FROM oficiales WHERE id = ?");
    $stmtEx->execute([$id]);
    $f = $stmtEx->fetch();
    if ($f) $existing = array_merge($existing, $f);
  }
 
  // estado recibido (opcional)
  $estadoInput = strtolower(trim($_POST['estado'] ?? ''));
  $allowedStatuses = ['traslado','permiso','activo','faltista','retirado'];
  $final_estado = in_array($estadoInput, $allowedStatuses) ? $estadoInput : ($existing['estado'] ?? 'activo');
 
  // Manejo de imagen de perfil
  $foto = null;
  if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
    $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    if (!in_array(strtolower($ext), $allowed)) exit('ERROR: Solo se permiten imágenes');
    if (!file_exists('img')) mkdir('img', 0777, true);
    $nombre_archivo = uniqid('oficial_') . '.' . $ext;
    $ruta_destino = 'img/' . $nombre_archivo;
    if (move_uploaded_file($_FILES['foto']['tmp_name'], $ruta_destino)) $foto = $ruta_destino;
  }

  // Manejo documento CV (documento)
  $documento = null;
  if (isset($_FILES['documento']) && $_FILES['documento']['error'] === UPLOAD_ERR_OK) {
    $ext = pathinfo($_FILES['documento']['name'], PATHINFO_EXTENSION);
    if (strtolower($ext) !== 'pdf') exit('ERROR: Solo se permiten archivos PDF');
    if (!file_exists('documentos')) mkdir('documentos', 0777, true);
    $nombre_doc = uniqid('doc_cv_') . '.pdf';
    $ruta_doc = 'documentos/' . $nombre_doc;
    if (move_uploaded_file($_FILES['documento']['tmp_name'], $ruta_doc)) $documento = $ruta_doc;
  }

  // Conservar existentes si no se sube nuevo
  $final_foto = $foto ?? $existing['foto'];
  $final_doc1 = $documento ?? $existing['documento'];
  
 
  if ($id) {
    // incluye estado al actualizar
    $stmt = $pdo->prepare("UPDATE oficiales SET nombre = ?, rango = ?, años_asignado = ?, notas = ?, documento = ?, foto = ?, estado = ? WHERE id = ?");
    $stmt->execute([$nombre, $rango, $anio, $notas, $final_doc1, $final_foto, $final_estado, $id]);
  } else {
    // incluye estado al insertar
    $stmt = $pdo->prepare("INSERT INTO oficiales (nombre, rango, años_asignado, notas, documento, foto, estado) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$nombre, $rango, $anio, $notas, $final_doc1, $final_foto, $final_estado]);
  }
 
  exit('OK');
}

// 2) Eliminar (AJAX)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
  $stmt = $pdo->prepare("SELECT foto, documento FROM oficiales WHERE id = ?");
  $stmt->execute([$_POST['delete']]);
  $oficial = $stmt->fetch();

  if ($oficial['foto'] && file_exists($oficial['foto'])) @unlink($oficial['foto']);
  if ($oficial['documento'] && file_exists($oficial['documento'])) @unlink($oficial['documento']);
 

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

<!-- TITULO Y CONTROLES -->
<div class="titulo-con-boton">
  <div class="titulo-izq">
    <h2 class="titulo-centrado">Historial de Oficiales</h2>
  </div>

  <div class="controls-der">
    <button type="button" onclick="mostrarFormOficial()" class="btn btn-primario btn-nuevo-oficial">+ Nuevo Oficial</button>
  </div>
</div>


<!-- Resultado rápido: muestra quiénes están en el estado seleccionado -->
<div id="statusResult" style="text-align:center;margin:0.8rem 0 1.2rem;color:var(--primary);font-weight:600;"></div>

<!-- FORMULARIO (MODAL) -->
<div id="formDivOficial" class="modal-overlay" aria-hidden="true" style="display:none;">
  <div class="modal-form modal-form-amplio" role="dialog" aria-modal="true" aria-labelledby="modalTituloOficial">
    <h3 id="modalTituloOficial">📋 Agregar / Editar Oficial</h3>
    <form id="oficialForm" enctype="multipart/form-data" autocomplete="off">
      <input type="hidden" name="id" id="ofId">

      <div class="form-grid">
        <div class="form-group">
          <label for="ofNombre">Nombre completo</label>
          <input type="text" name="nombre" id="ofNombre" placeholder="Ej: Juan Carlos Gómez" required>
        </div>

        <div class="form-group">
          <label for="ofRango">Rango militar</label>
          <input type="text" name="rango" id="ofRango" placeholder="Ej: Capitán" required>
        </div>

        <div class="form-group">
          <label for="ofAniosAsignado">Año de asignación</label>
          <input type="number" name="anios_asignado" id="ofAniosAsignado" placeholder="Ej: 2020" required min="1980" max="2030">
        </div>
      </div>

      <div class="form-group">
        <label for="ofNotas">Notas adicionales</label>
        <textarea name="notas" id="ofNotas" rows="4" placeholder="Observaciones, reconocimientos, historial, etc."></textarea>
      </div>

      <div class="form-grid">
        <div class="form-group">
          <label for="ofFoto">Fotografía</label>
          <input type="file" name="foto" id="ofFoto" accept="image/*">
          <small class="form-help">JPG / PNG / GIF</small>
        </div>

        <div class="form-group">
          <label for="ofDocumento">Curriculum Vitae (PDF)</label>
          <input type="file" name="documento" id="ofDocumento" accept=".pdf">
          <small class="form-help">Hoja de vida, certificados, etc.</small>
        </div>

        

      </div>

      <div class="form-buttons">
        <button type="submit" class="btn btn-primario">Guardar</button>
        <button type="button" onclick="cerrarFormOficial()" class="btn btn-secundario">Cancelar</button>
      </div>
    </form>
  </div>
</div>

<!-- LISTA / TARJETAS -->
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
          <!-- CV -->
          <?php if (!empty($o['documento'])): ?>
            <a href="<?= htmlspecialchars($o['documento']) ?>" target="_blank" class="btn btn-outline btn-documento" aria-label="Ver CV de <?= htmlspecialchars($o['nombre']) ?>">
              Ver CV
            </a>
          <?php endif; ?>

        <div class="accion-grupo">
          <button
            type="button"
            onclick='editarOficial(<?= json_encode([
              "id" => $o["id"],
              "nombre" => $o["nombre"],
              "rango" => $o["rango"],
              "anio" => $o["años_asignado"],
              "notas" => $o["notas"]
            ]) ?>)'
            class="btn btn-primario btn-pequeno"
            aria-label="Editar <?= htmlspecialchars($o['nombre']) ?>"
          >Editar</button>

          <button
            type="button"
            onclick="eliminarOficial(<?= $o['id'] ?>)"
            class="btn btn-secundario btn-pequeno"
            aria-label="Eliminar <?= htmlspecialchars($o['nombre']) ?>"
          >Eliminar</button>
        </div>
      </div>
    </article>
  <?php endforeach; ?>
</div>

<!-- SCRIPT: pequeño helper para filtrar por estado (sin barra de búsqueda/rango/orden) -->
<script>
(function(){
  const lista = document.getElementById('listaOficiales');
  const statusBtns = Array.from(document.querySelectorAll('.status-btn'));
  const resultContainer = document.getElementById('statusResult');
  let statusFilter = ''; // '' = todos

  if (!lista) return;

  function actualizarResultado(matched) {
    if (!resultContainer) return;
    if (!statusFilter) {
      resultContainer.textContent = `Mostrando todos los oficiales (${matched.length})`;
      return;
    }
    if (matched.length === 0) {
      resultContainer.textContent = `No hay oficiales en estado "${statusFilter}"`;
      return;
    }
    const nombres = matched.map(card => (card.querySelector('h3')?.textContent || '').trim()).filter(Boolean);
    // mostrar hasta 30 nombres para no romper el diseño
    const mostrar = nombres.slice(0, 30);
    const more = nombres.length > mostrar.length ? `, y ${nombres.length - mostrar.length} más` : '';
    resultContainer.innerHTML = `${nombres.length} resultado(s) — ${mostrar.join(', ')}${more}`;
  }

  function aplicarFiltro() {
    const tarjetas = Array.from(lista.querySelectorAll('.perfil-card'));
    tarjetas.forEach(card => {
      const cardStatus = (card.dataset.status || 'activo').toLowerCase();
      card.style.display = (!statusFilter || cardStatus === statusFilter) ? 'block' : 'none';
    });

    // construir lista de coincidencias (sin contar elementos ocultos por otras razones)
    const matched = Array.from(lista.querySelectorAll('.perfil-card'))
      .filter(card => {
        const cardStatus = (card.dataset.status || 'activo').toLowerCase();
        return !statusFilter || cardStatus === statusFilter;
      });

    actualizarResultado(matched);
  }

  statusBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      statusBtns.forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      statusFilter = btn.dataset.status || '';
      aplicarFiltro();
    });
  });

  // aplicar filtro inicial
  aplicarFiltro();
})();

// Imprimir PDF abriendo en nueva pestaña y solicitando print()
function imprimirDocumento(url) {
  if (!url) return alert('Documento no disponible');
  const w = window.open(url, '_blank');
  if (!w) return alert('Permite ventanas emergentes para imprimir');
  w.addEventListener ? w.addEventListener('load', () => { try { w.print(); } catch(e){} }) : null;
  setTimeout(() => { try { w.print(); } catch(e){} }, 900);
}
</script>