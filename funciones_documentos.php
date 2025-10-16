<?php
require 'bd.php';

// Subir documento
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['subir_documento'])) {
  $tabla_origen   = $_POST['tabla_origen'];
  $id_referencia  = intval($_POST['id_referencia']);
  $descripcion    = trim($_POST['descripcion']);
  
  if (isset($_FILES['archivo']) && $_FILES['archivo']['error'] === UPLOAD_ERR_OK) {
    $ext = pathinfo($_FILES['archivo']['name'], PATHINFO_EXTENSION);
    
    if (strtolower($ext) !== 'pdf') {
      exit('ERROR: Solo se permiten archivos PDF');
    }
    
    if (!file_exists('documentos')) {
      mkdir('documentos', 0777, true);
    }
    
    $nombre_original = pathinfo($_FILES['archivo']['name'], PATHINFO_FILENAME);
    $nombre_archivo  = uniqid('doc_') . '.pdf';
    $ruta_archivo    = 'documentos/' . $nombre_archivo;
    
    if (move_uploaded_file($_FILES['archivo']['tmp_name'], $ruta_archivo)) {
      $stmt = $pdo->prepare(
        "INSERT INTO documentos (tabla_origen, id_referencia, nombre_archivo, ruta_archivo, descripcion) 
         VALUES (?, ?, ?, ?, ?)"
      );
      $stmt->execute([$tabla_origen, $id_referencia, $nombre_original, $ruta_archivo, $descripcion]);
      exit('OK');
    } else {
      exit('ERROR: No se pudo subir el archivo');
    }
  }
  exit('ERROR: No se recibió archivo');
}

// Eliminar documento
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar_documento'])) {
  $id_documento = intval($_POST['eliminar_documento']);
  
  // Obtener ruta antes de eliminar
  $stmt = $pdo->prepare("SELECT ruta_archivo FROM documentos WHERE id = ?");
  $stmt->execute([$id_documento]);
  $doc = $stmt->fetch();
  
  if ($doc && file_exists($doc['ruta_archivo'])) {
    unlink($doc['ruta_archivo']);
  }
  
  $stmt = $pdo->prepare("DELETE FROM documentos WHERE id = ?");
  $stmt->execute([$id_documento]);
  exit('OK');
}

// Listar documentos
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['listar_documentos'])) {
  $tabla_origen  = $_GET['tabla_origen'];
  $id_referencia = intval($_GET['id_referencia']);
  
  $stmt = $pdo->prepare(
    "SELECT * FROM documentos 
     WHERE tabla_origen = ? AND id_referencia = ? 
     ORDER BY fecha_subida DESC"
  );
  $stmt->execute([$tabla_origen, $id_referencia]);
  $documentos = $stmt->fetchAll();
  
  header('Content-Type: application/json');
  echo json_encode($documentos);
  exit;
}
?>