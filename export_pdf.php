<?php
require 'bd.php';

// Obtener parámetros de filtro
$filtro_tipo = $_GET['tipo'] ?? 'todos';
$filtro_genero = $_GET['genero'] ?? 'todos';

// Incluir Dompdf manualmente
require_once 'vendor/dompdf/autoload.inc.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// Obtener datos para el PDF con los mismos filtros
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
  error_log("Error en export_pdf: " . $e->getMessage());
  $veteranos = [];
}

$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);

$dompdf = new Dompdf($options);

// Texto del filtro para mostrar en el PDF
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

$html = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Reporte de Personal - INTEGRA</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 20px; }
        .filtro-info { background: #f5f5f5; padding: 10px; border-radius: 5px; margin-bottom: 20px; font-size: 14px; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background: #333; color: white; }
        tr:nth-child(even) { background: #f9f9f9; }
        .no-resultados { text-align: center; padding: 40px; background: #f8f9fa; border-radius: 5px; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Reporte de Personal</h1>
        <p><strong>Sistema INTEGRA</strong> - ' . date('d/m/Y H:i:s') . '</p>
    </div>
    
    <div class="filtro-info">
        <strong>Filtros aplicados:</strong> ' . $texto_filtro . '<br>
        <strong>Total de resultados:</strong> ' . count($veteranos) . '
    </div>';

if (count($veteranos) > 0) {
    $html .= '
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Nombre</th>
                <th>Cargo/Especialidad</th>
                <th>Tipo</th>
                <th>Género</th>
                <th>Años de Servicio</th>
            </tr>
        </thead>
        <tbody>';
    
    foreach ($veteranos as $index => $v) {
        $html .= '
            <tr>
                <td>' . ($index + 1) . '</td>
                <td>' . htmlspecialchars($v['nombre']) . '</td>
                <td>' . htmlspecialchars($v['cargo']) . '</td>
                <td>' . $v['tipo'] . '</td>
                <td>' . ($v['genero'] == 'M' ? 'Masculino' : 'Femenino') . '</td>
                <td>' . $v['anos'] . ' años</td>
            </tr>';
    }
    
    $html .= '
        </tbody>
    </table>';
} else {
    $html .= '
    <div class="no-resultados">
        <p>No se encontraron resultados con los filtros seleccionados.</p>
        <p><strong>Filtros activos:</strong><br>';
    
    if ($filtro_tipo !== 'todos') {
        $html .= '• Tipo: ' . $filtro_tipo . '<br>';
    }
    if ($filtro_genero !== 'todos') {
        $html .= '• Género: ' . ($filtro_genero == 'M' ? 'Masculino' : 'Femenino') . '<br>';
    }
    
    $html .= '</p>
    </div>';
}

$html .= '
</body>
</html>';

$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// Nombre del archivo que incluye los filtros
$nombre_archivo = "reporte_personal";
if ($filtro_tipo !== 'todos') {
    $nombre_archivo .= "_" . strtolower($filtro_tipo);
}
if ($filtro_genero !== 'todos') {
    $nombre_archivo .= "_" . strtolower($filtro_genero);
}
$nombre_archivo .= "_" . date('Y-m-d') . ".pdf";

$dompdf->stream($nombre_archivo, array("Attachment" => true));