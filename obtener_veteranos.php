<?php
require 'bd.php';

// Obtener parámetros de filtro
$filtro_tipo = $_GET['tipo'] ?? 'todos';
$filtro_genero = $_GET['genero'] ?? 'todos';

// Personal veterano con filtros
try {
  $sql_parts = [];
  $params = [];
  
  // Oficiales
  if ($filtro_tipo === 'todos' || $filtro_tipo === 'Oficial') {
    $sql_oficiales = "SELECT nombre, rango AS cargo, años_asignado AS anos, 'Oficial' AS tipo, genero FROM oficiales WHERE 1=1";
    
    if ($filtro_genero !== 'todos') {
      $sql_oficiales .= " AND genero = ?";
      $params[] = $filtro_genero;
    }
    $sql_parts[] = $sql_oficiales;
  }
  
  // Docentes
  if ($filtro_tipo === 'todos' || $filtro_tipo === 'Docente') {
    $sql_docentes = "SELECT nombre, especialidad AS cargo, años_asignado AS anos, 'Docente' AS tipo, genero FROM docentes WHERE 1=1";
    
    if ($filtro_genero !== 'todos') {
      $sql_docentes .= " AND genero = ?";
      $params[] = $filtro_genero;
    }
    $sql_parts[] = $sql_docentes;
  }
  
  // Mantenimiento
  if ($filtro_tipo === 'todos' || $filtro_tipo === 'Mantenimiento') {
    $sql_mantenimiento = "SELECT nombre, cargo, años_asignado AS anos, 'Mantenimiento' AS tipo, genero FROM mantenimiento WHERE 1=1";
    
    if ($filtro_genero !== 'todos') {
      $sql_mantenimiento .= " AND genero = ?";
      $params[] = $filtro_genero;
    }
    $sql_parts[] = $sql_mantenimiento;
  }
  
  // Administrativo
  if ($filtro_tipo === 'todos' || $filtro_tipo === 'Administrativo') {
    $sql_administrativo = "SELECT nombre, cargo, años_asignado AS anos, 'Administrativo' AS tipo, genero FROM administrativo WHERE 1=1";
    
    if ($filtro_genero !== 'todos') {
      $sql_administrativo .= " AND genero = ?";
      $params[] = $filtro_genero;
    }
    $sql_parts[] = $sql_administrativo;
  }
  
  if (empty($sql_parts)) {
    $veteranos = [];
  } else {
    $sql = implode(" UNION ALL ", $sql_parts) . " ORDER BY anos DESC LIMIT 10";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $veteranos = $stmt->fetchAll();
  }
  
} catch (PDOException $e) {
  error_log("Error en obtener_veteranos: " . $e->getMessage());
  $veteranos = [];
}
?>

<?php if (count($veteranos) > 0): ?>
<div class="veteranos-list">
  <?php foreach ($veteranos as $index => $v): ?>
    <div class="veterano-item">
      <div class="veterano-info">
        <span class="veterano-posicion"><?= $index + 1 ?>.</span>
        <span class="veterano-nombre"><?= htmlspecialchars($v['nombre']) ?></span>
        <span class="veterano-cargo"><?= htmlspecialchars($v['cargo']) ?></span>
        <span class="veterano-tipo">(<?= $v['tipo'] ?>)</span>
        <span class="veterano-genero"><?= $v['genero'] == 'M' ? '👨' : '👩' ?></span>
      </div>
      <span class="veterano-anos"><?= $v['anos'] ?> años</span>
    </div>
  <?php endforeach; ?>
</div>

<div class="resultados-info">
  <p>Mostrando <?= count($veteranos) ?> resultados 
    <?php if ($filtro_tipo !== 'todos'): ?>
      - Filtrado por: <?= $filtro_tipo ?>
    <?php endif; ?>
    <?php if ($filtro_genero !== 'todos'): ?>
      , <?= $filtro_genero == 'M' ? 'Masculino' : 'Femenino' ?>
    <?php endif; ?>
  </p>
</div>
<?php else: ?>
  <div class="sin-resultados">
    <p>No se encontraron resultados con los filtros seleccionados.</p>
    <?php if ($filtro_tipo !== 'todos' || $filtro_genero !== 'todos'): ?>
      <p class="filtros-activos">
        <strong>Filtros activos:</strong><br>
        <?php if ($filtro_tipo !== 'todos'): ?>• Tipo: <?= $filtro_tipo ?><br><?php endif; ?>
        <?php if ($filtro_genero !== 'todos'): ?>• Género: <?= $filtro_genero == 'M' ? 'Masculino' : 'Femenino' ?><br><?php endif; ?>
      </p>
    <?php endif; ?>
  </div>
<?php endif; ?>