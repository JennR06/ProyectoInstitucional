<?php
// bd.php
$host = 'localhost';
$port = '3306';  // ✅ Cambia este número si tu MySQL usa otro puerto
$db   = 'talento_humano';
$user = 'root';
$pass = '';      // Si pusiste contraseña en la nueva PC, ponla aquí

// Agregar el puerto al DSN
$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";

$options = [
  PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  PDO::ATTR_EMULATE_PREPARES => false
];

try {
  $pdo = new PDO($dsn, $user, $pass, $options);
  // echo "✅ Conexión exitosa"; // Descomenta para probar
} catch (PDOException $e) {
  die("❌ Error de conexión: " . $e->getMessage());
}
?>
