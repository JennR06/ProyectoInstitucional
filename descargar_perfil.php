<?php
// --- NADA ANTES DE ESTA LÍNEA ---
while (ob_get_level()) {
    ob_end_clean();
}

// 🔒 Desactivar errores (¡clave para archivos binarios!)
error_reporting(0);
ini_set('display_errors', 0);

// Conexión a la base de datos
require __DIR__ . '/db.php';

// ✅ Cargar y registrar PHPWord (CORREGIDO)
require __DIR__ . '/PhpWord/src/PhpWord/Autoloader.php';
\PhpOffice\PhpWord\Autoloader::register(); // ← ¡Esta es la línea correcta!

$id = $_GET['id'] ?? null;
if (!$id || !is_numeric($id)) {
    exit('ID no válido');
}

try {
    $stmt = $pdo->prepare("SELECT *, YEAR(CURDATE()) - año_ingreso AS años_servicio FROM mantenimiento WHERE id = ?");
    $stmt->execute([$id]);
    $emp = $stmt->fetch();

    if (!$emp) {
        exit('Empleado no encontrado');
    }

    $phpWord = new \PhpOffice\PhpWord\PhpWord();
    $section = $phpWord->addSection();
    $section->addText('📄 PERFIL COMPLETO DEL EMPLEADO', ['bold' => true, 'size' => 16, 'color' => '1A472A']);
    $section->addTextBreak(1);
    $section->addText('📋 DATOS GENERALES', ['bold' => true, 'size' => 14, 'color' => '2C5D3C']);
    $section->addText("Nombre: " . htmlspecialchars($emp['nombre'] ?? '—'));
    $section->addText("Cargo: " . htmlspecialchars($emp['cargo'] ?? '—'));
    $section->addText("Año de ingreso: " . ($emp['año_ingreso'] ?? '—'));
    $section->addText("Años de servicio: " . ($emp['años_servicio'] ?? '—'));
    $section->addTextBreak(1);
    $section->addText('🏢 DATOS LABORALES', ['bold' => true, 'size' => 14, 'color' => '2C5D3C']);
    $section->addText("Estado laboral: " . htmlspecialchars($emp['estado_laboral'] ?? '—'));
    $section->addText("Área asignada: " . htmlspecialchars($emp['area_asignada'] ?? '—'));
    $section->addText("Supervisor: " . htmlspecialchars($emp['supervisor'] ?? '—'));
    $section->addText("Turno: " . htmlspecialchars($emp['turno'] ?? '—'));
    $section->addText("Horario: " . htmlspecialchars($emp['horario'] ?? '—'));
    $section->addText("Teléfono: " . htmlspecialchars($emp['telefono'] ?? '—'));
    $section->addText("Correo: " . htmlspecialchars($emp['correo'] ?? '—'));
    $section->addTextBreak(1);
    $section->addText('✚ INFORMACIÓN MÉDICA', ['bold' => true, 'size' => 14, 'color' => 'C00000']);
    
    $estadoMap = ['estable' => 'Estable', 'tratamiento' => 'Con tratamiento crónico', 'recuperacion' => 'En recuperación', 'otro' => 'Otro'];
    $estadoSalud = $estadoMap[$emp['estado_salud']] ?? ($emp['estado_salud'] ?: 'No registrado');
    $section->addText("Estado de salud actual: " . htmlspecialchars($estadoSalud));
    
    $alergias = $emp['tiene_alergias'] ? "Sí - " . htmlspecialchars($emp['detalle_alergias'] ?: 'Sin detalles') : "No";
    $section->addText("Alergias o restricciones médicas: " . $alergias);
    
    $epp = $emp['usa_epp'] ? "Sí - " . htmlspecialchars($emp['tipo_epp'] ?: 'Sin especificar') : "No";
    $section->addText("Utiliza EPP: " . $epp);
    
    $section->addText("Última evaluación médica: " . ($emp['ultima_evaluacion'] ?: 'No registrada'));
    $section->addText("Próxima evaluación médica: " . ($emp['proxima_evaluacion'] ?: 'No programada'));
    
    $accidentes = $emp['tiene_accidente'] ? 
        "Sí - Fecha: " . ($emp['fecha_accidente'] ?: 'Sin fecha') . " | " . htmlspecialchars($emp['detalle_accidente'] ?: 'Sin descripción') : 
        "No";
    $section->addText("Accidentes laborales registrados: " . $accidentes);

    if (!empty($emp['notas'])) {
        $section->addTextBreak(1);
        $section->addText('📝 NOTAS ADICIONALES', ['bold' => true, 'size' => 14]);
        $section->addText(htmlspecialchars($emp['notas']));
    }

    // === DESCARGAR ===
    header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
    header('Content-Disposition: attachment; filename="Perfil_' . urlencode($emp['nombre']) . '.docx"');
    header('Cache-Control: max-age=0');

    $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
    $objWriter->save('php://output');
    exit;

} catch (Exception $e) {
    exit; // Silencioso en producción
}