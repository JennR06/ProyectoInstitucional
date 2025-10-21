<?php
// export_pdf.php - Versión simplificada sin Composer
require 'bd.php';

// Incluir Dompdf manualmente
require_once 'vendor/dompdf/dompdf/autoload.inc.php';

// Referenciar el namespace de Dompdf
use Dompdf\Dompdf;
use Dompdf\Options;

// Obtener datos para el PDF
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

// Crear instancia de Dompdf
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);

$dompdf = new Dompdf($options);

// Crear el contenido HTML del PDF
$html = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Reporte Dashboard - INTEGRA</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 20px; }
        .stats-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; margin-bottom: 30px; }
        .stat-card { border: 1px solid #ddd; padding: 15px; border-radius: 5px; border-left: 4px solid; }
        .stat-blue { border-left-color: #2196F3; }
        .stat-green { border-left-color: #4CAF50; }
        .stat-orange { border-left-color: #FF9800; }
        .stat-purple { border-left-color: #9C27B0; }
        .stat-value { font-size: 24px; font-weight: bold; }
        .veterano-item { display: flex; justify-content: space-between; margin-bottom: 10px; padding: 10px; background: #f9f9f9; }
        .section { margin-bottom: 25px; }
        .section-title { background: #333; color: white; padding: 10px; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Panel de Control - INTEGRA</h1>
        <p>Reporte generado el: ' . date('d/m/Y H:i:s') . '</p>
    </div>
    
    <div class="section">
        <h2 class="section-title">Estadísticas de Personal</h2>
        <div class="stats-grid">
            <div class="stat-card stat-blue">
                <div class="stat-value">' . $administrativo . '</div>
                <div class="stat-label">Administrativo</div>
            </div>
            <div class="stat-card stat-green">
                <div class="stat-value">' . $oficiales . '</div>
                <div class="stat-label">Oficiales</div>
            </div>
            <div class="stat-card stat-orange">
                <div class="stat-value">' . $docentes . '</div>
                <div class="stat-label">Docentes</div>
            </div>
            <div class="stat-card stat-purple">
                <div class="stat-value">' . $mantenimiento . '</div>
                <div class="stat-label">Mantenimiento</div>
            </div>
        </div>
        <div style="text-align: center; font-size: 18px; font-weight: bold; margin-top: 15px;">
            Total de Personal: ' . $total . '
        </div>
    </div>';

if (count($veteranos) > 0) {
    $html .= '
    <div class="section">
        <h2 class="section-title">Personal con Más Antigüedad</h2>';
    foreach ($veteranos as $v) {
        $html .= '
        <div class="veterano-item">
            <span>' . htmlspecialchars($v['nombre']) . '</span>
            <span>' . htmlspecialchars($v['cargo']) . '</span>
            <span>' . $v['anos'] . ' años</span>
        </div>';
    }
    $html .= '
    </div>';
}

$html .= '
</body>
</html>';

$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// Descargar el PDF
$dompdf->stream("reporte_dashboard_" . date('Y-m-d') . ".pdf", array("Attachment" => true));
?>