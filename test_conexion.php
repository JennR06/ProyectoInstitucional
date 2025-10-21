<?php
// test_conexion.php
$host = 'localhost';
$port = '3306';  // Cambia si es necesario
$db   = 'talento_humano';
$user = 'root';
$pass = '';

echo "<h2>Probando conexión a MySQL...</h2>";
echo "Host: $host:$port<br>";
echo "Base de datos: $db<br>";
echo "Usuario: $user<br><br>";

$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";

try {
  $pdo = new PDO($dsn, $user, $pass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
  ]);
  echo "✅ <strong style='color:green;'>¡Conexión exitosa!</strong><br>";
  echo "Servidor MySQL: " . $pdo->getAttribute(PDO::ATTR_SERVER_VERSION);
} catch (PDOException $e) {
  echo "❌ <strong style='color:red;'>Error de conexión:</strong><br>";
  echo $e->getMessage();
}
?>