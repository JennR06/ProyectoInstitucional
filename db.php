<?php
// bd.php - Conexión segura para descarga de archivos
$host = 'localhost';
$port = '3306';
$db   = 'talento_humano';
$user = 'root';
$pass = ''; // ← Pon tu contraseña si la tienes

$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    // 🔒 En producción, NO muestres errores al usuario (rompe descargas)
    // Solo registra el error si necesitas depurarlo:
    // error_log("Error BD: " . $e->getMessage());
    exit; // Salir en silencio
}
