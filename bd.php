<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sistema Talento Humano - Liceo Militar de Honduras</title>
  <!-- Hoja de estilos principal -->
  <link rel="stylesheet" href="styles.css">
  <link rel="stylesheet" href="index.php">
  <link rel="stylesheet" href="bd.php">

<?php
// db.php
$host = 'localhost';
$db   = 'talento_humano';
$user = 'root';
$pass = '';          // tu contraseña si aplica

$dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
$options = [
  PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
];

$pdo = new PDO($dsn, $user, $pass, $options);
?>