<?php
// generar_perfil_oficial.php
// Genera un perfil en Word (.docx) para un oficial del Liceo Militar de Honduras

// Configuración de errores (opcional en producción)
error_reporting(E_ALL);
ini_set('display_errors', 0); // Cambia a 1 si necesitas depurar

// Incluir conexión a la base de datos
require 'db.php';

// Cargar PhpWord (asegúrate de tener Composer instalado)
if (!file_exists('vendor/autoload.php')) {
    die('Error: No se encontró la librería PhpWord. Ejecuta "composer require phpoffice/phpword" en la raíz del proyecto.');
}
require_once 'vendor/autoload.php';

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\SimpleType\Jc;

// Validar ID desde la URL
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    die('ID de oficial no válido.');
}

// Obtener datos del oficial
$stmt = $pdo->prepare("SELECT * FROM oficiales WHERE id = ?");
$stmt->execute([$id]);
$oficial = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$oficial) {
    die('Oficial no encontrado.');
}

// Crear nuevo documento Word
$phpWord = new PhpWord();
$phpWord->setDefaultFontName('Arial');
$phpWord->setDefaultFontSize(11);

// Estilos de título
$phpWord->addTitleStyle(1, ['bold' => true, 'size' => 16, 'color' => '000000']);
$phpWord->addTitleStyle(2, ['bold' => true, 'size' => 14, 'color' => '1E3A8A']);

// Configurar sección
$section = $phpWord->addSection([
    'marginTop' => 600,
    'marginBottom' => 600,
    'marginLeft' => 900,
    'marginRight' => 900
]);

// === ENCABEZADO ===
$section->addTitle('FICHA DE TALENTO HUMANO - OFICIAL', 1);
$section->addText("Liceo Militar de Honduras", ['bold' => true, 'size' => 12], ['alignment' => Jc::CENTER]);
$section->addTextBreak(2);

// === FOTO (si existe) ===
if (!empty($oficial['foto']) && file_exists($oficial['foto'])) {
    $section->addImage($oficial['foto'], [
        'width' => 120,
        'height' => 150,
        'alignment' => Jc::RIGHT,
        'wrappingStyle' => 'square'
    ]);
}

// === FUNCIÓN AUXILIAR PARA TABLAS ===
function addRow($table, $label, $value) {
    $table->addRow();
    $table->addCell(3000)->addText($label . ':', ['bold' => true, 'size' => 11]);
    $table->addCell(6000)->addText($value ?: 'No especificado', ['size' => 11]);
}

// === SECCIÓN 1: DATOS PERSONALES ===
$section->addTitle('1. Datos Personales', 2);
$tablePersonal = $section->addTable(['width' => 9000]);
addRow($tablePersonal, 'Nombre completo', htmlspecialchars($oficial['nombre']));
addRow($tablePersonal, 'Rango militar', htmlspecialchars($oficial['rango']));
addRow($tablePersonal, 'Número de identificación', htmlspecialchars($oficial['numero_identificacion']));
addRow($tablePersonal, 'Fecha de nacimiento', $oficial['fecha_nacimiento'] ? date('d/m/Y', strtotime($oficial['fecha_nacimiento'])) : 'No especificado');
addRow($tablePersonal, 'Género', ucfirst($oficial['genero'] ?? ''));
addRow($tablePersonal, 'Estado civil', ucfirst($oficial['estado_civil'] ?? ''));
addRow($tablePersonal, 'Teléfono', htmlspecialchars($oficial['numero_telefono']));
addRow($tablePersonal, 'Dirección', htmlspecialchars($oficial['direccion']));
addRow($tablePersonal, 'Departamento asignado', htmlspecialchars($oficial['departamento']));
addRow($tablePersonal, 'Año de asignación', $oficial['años_asignado']);
addRow($tablePersonal, 'Años de servicio', date('Y') - ($oficial['años_asignado'] ?? date('Y')));
addRow($tablePersonal, 'Estado actual', ucfirst($oficial['estado'] ?? 'Activo'));

// Notas adicionales (si existen)
if (!empty($oficial['notas'])) {
    $section->addTextBreak(1);
    $section->addText('Notas adicionales:', ['bold' => true, 'size' => 11]);
    $section->addText(htmlspecialchars($oficial['notas']), ['size' => 11]);
}

// === SALTO DE PÁGINA ===
$section->addPageBreak();

// === SECCIÓN 2: DATOS MÉDICOS ===
$section->addTitle('2. Datos Médicos', 2);
$tableMedico = $section->addTable(['width' => 9000]);
addRow($tableMedico, 'Tipo de sangre', htmlspecialchars($oficial['tipo_sangre']));
addRow($tableMedico, 'Alergias conocidas', htmlspecialchars($oficial['alergias']));
addRow($tableMedico, 'Enfermedades crónicas', htmlspecialchars($oficial['enfermedades_cronicas']));
addRow($tableMedico, 'Última evaluación médica', $oficial['ultima_evaluacion'] ? date('d/m/Y', strtotime($oficial['ultima_evaluacion'])) : 'No registrada');
addRow($tableMedico, 'Accidentes laborales', htmlspecialchars($oficial['accidentes_laborales']));
addRow($tableMedico, '¿Utiliza EPP?', ($oficial['usa_epp'] ? 'Sí' : 'No'));

// === DESCARGAR EL ARCHIVO ===
$nombreArchivo = 'Perfil_Oficial_' . preg_replace('/[^a-zA-Z0-9]/', '_', $oficial['nombre']) . '.docx';

header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
header('Content-Disposition: attachment; filename="' . $nombreArchivo . '"');
header('Cache-Control: max-age=0');

$writer = IOFactory::createWriter($phpWord, 'Word2007');
$writer->save('php://output');
exit;