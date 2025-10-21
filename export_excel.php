<?php
require 'bd.php';

header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="reporte_dashboard_' . date('Y-m-d') . '.xls"');
header('Pragma: no-cache');
header('Expires: 0');

// Obtener datos para el Excel
try {
  $oficiales = $pdo->query("SELECT COUNT(*) FROM oficiales")->fetchColumn();
  $docentes = $pdo->query("SELECT COUNT(*) FROM docentes")->fetchColumn();
  $mantenimiento = $pdo->query("SELECT COUNT(*) FROM mantenimiento")->fetchColumn();
  $administrativo = $pdo->query("SELECT COUNT(*) FROM administrativo")->fetchColumn();
  $total = $oficiales + $docentes + $mantenimiento + $administrativo;
  
  $veteranos = $pdo->query("
    SELECT nombre, rango AS cargo, YEAR(CURDATE()) - años_asignado AS anos, 'Oficial' AS tipo 
    FROM oficiales
    ORDER BY anos DESC LIMIT 5
  ")->fetchAll();
} catch (PDOException $e) {
  $oficiales = $docentes = $mantenimiento = $administrativo = $total = 0;
  $veteranos = [];
}

echo '<table border="1">';
echo '<tr><th colspan="3" style="background:#333; color:white; font-size:16px;">📊 Panel de Control - INTEGRA</th></tr>';
echo '<tr><td colspan="3"><strong>Reporte generado el:</strong> ' . date('d/m/Y H:i:s') . '</td></tr>';
echo '<tr><td colspan="3">&nbsp;</td></tr>';

// Estadísticas
echo '<tr><th colspan="3" style="background:#666; color:white;">Estadísticas de Personal</th></tr>';
echo '<tr><td><strong>Administrativo:</strong></td><td>' . $administrativo . '</td><td></td></tr>';
echo '<tr><td><strong>Oficiales:</strong></td><td>' . $oficiales . '</td><td></td></tr>';
echo '<tr><td><strong>Docentes:</strong></td><td>' . $docentes . '</td><td></td></tr>';
echo '<tr><td><strong>Mantenimiento:</strong></td><td>' . $mantenimiento . '</td><td></td></tr>';
echo '<tr style="background:#f0f0f0;"><td><strong>TOTAL:</strong></td><td><strong>' . $total . '</strong></td><td></td></tr>';

echo '<tr><td colspan="3">&nbsp;</td></tr>';

// Personal veterano
if (count($veteranos) > 0) {
    echo '<tr><th colspan="3" style="background:#666; color:white;">Personal con Más Antigüedad</th></tr>';
    echo '<tr style="background:#e0e0e0;"><td><strong>Nombre</strong></td><td><strong>Cargo</strong></td><td><strong>Años</strong></td></tr>';
    
    foreach ($veteranos as $v) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($v['nombre']) . '</td>';
        echo '<td>' . htmlspecialchars($v['cargo']) . '</td>';
        echo '<td>' . $v['anos'] . ' años</td>';
        echo '</tr>';
    }
}

echo '</table>';
?>