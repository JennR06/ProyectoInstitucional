<?php
require 'db.php';

// Obtener parámetros de filtro
$filtro_tipo = $_GET['tipo'] ?? 'todos';
$filtro_genero = $_GET['genero'] ?? 'todos';

header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="reporte_personal_' . date('Y-m-d') . '.xls"');
header('Pragma: no-cache');
header('Expires: 0');

// Obtener datos para el Excel con los mismos filtros
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
  error_log("Error en export_excel: " . $e->getMessage());
  $veteranos = [];
}

// Texto del filtro para mostrar en el Excel
$texto_filtro = "";
if ($filtro_tipo !== 'todos') {
    $texto_filtro .= "Tipo: " . $filtro_tipo;
}
if ($filtro_genero !== 'todos') {
    $texto_filtro .= ($texto_filtro ? ", " : "") . "Género: " . ($filtro_genero == 'M' ? 'Masculino' : 'Femenino');
}
if (!$texto_filtro) {
    $texto_filtro = "Todos los resultados";
}

echo '<table border="1">';
echo '<tr><th colspan="6" style="background:#333; color:white; font-size:16px;">Reporte de Personal con Más Antigüedad</th></tr>';
echo '<tr><td colspan="6"><strong>Sistema INTEGRA</strong> - Reporte generado el: ' . date('d/m/Y H:i:s') . '</td></tr>';
echo '<tr><td colspan="6"><strong>Filtros aplicados:</strong> ' . $texto_filtro . '</td></tr>';
echo '<tr><td colspan="6"><strong>Total de resultados:</strong> ' . count($veteranos) . '</td></tr>';
echo '<tr><td colspan="6">&nbsp;</td></tr>';

if (count($veteranos) > 0) {
    echo '<tr style="background:#666; color:white;">';
    echo '<th>#</th>';
    echo '<th>Nombre</th>';
    echo '<th>Cargo/Especialidad</th>';
    echo '<th>Tipo</th>';
    echo '<th>Género</th>';
    echo '<th>Años de Servicio</th>';
    echo '</tr>';
    
    foreach ($veteranos as $index => $v) {
        echo '<tr>';
        echo '<td>' . ($index + 1) . '</td>';
        echo '<td>' . htmlspecialchars($v['nombre']) . '</td>';
        echo '<td>' . htmlspecialchars($v['cargo']) . '</td>';
        echo '<td>' . $v['tipo'] . '</td>';
        echo '<td>' . ($v['genero'] == 'M' ? 'Masculino' : 'Femenino') . '</td>';
        echo '<td>' . $v['anos'] . ' años</td>';
        echo '</tr>';
    }
} else {
    echo '<tr><td colspan="6" style="text-align:center; padding:20px; background:#f8f9fa;">';
    echo 'No se encontraron resultados con los filtros seleccionados<br><br>';
    echo '<strong>Filtros activos:</strong><br>';
    if ($filtro_tipo !== 'todos') {
        echo '• Tipo: ' . $filtro_tipo . '<br>';
    }
    if ($filtro_genero !== 'todos') {
        echo '• Género: ' . ($filtro_genero == 'M' ? 'Masculino' : 'Femenino') . '<br>';
    }
    echo '</td></tr>';
}

echo '</table>';
?>