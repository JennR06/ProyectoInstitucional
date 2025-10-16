<?php
require 'bd.php';

$anio = isset($_POST['anio']) ? intval($_POST['anio']) : date('Y');
$tipo = $_POST['tipo'] ?? 'todos';

function obtenerReporte($tabla, $anio, $pdo) {
  $columna = match($tabla) {
    'oficiales'     => 'rango',
    'docentes'      => 'especialidad',
    'mantenimiento' => 'cargo',
    'administrativo'=> 'cargo',
    default         => 'cargo'
  };

  $campo_anio = $tabla === 'oficiales' ? 'años_asignado' : 'año_ingreso';

  $stmt = $pdo->prepare("
    SELECT $columna AS categoria, COUNT(*) AS cantidad, AVG(salario) AS promedio_salario
    FROM $tabla
    WHERE $campo_anio <= ?
    GROUP BY $columna
  ");
  $stmt->execute([$anio]);
  return $stmt->fetchAll();
}

$datos = [];

if ($tipo === 'todos') {
  $tablas = ['oficiales', 'docentes', 'mantenimiento', 'administrativo'];
  foreach ($tablas as $t) {
    $datos[$t] = obtenerReporte($t, $anio, $pdo);
  }
} else {
  $datos[$tipo] = obtenerReporte($tipo, $anio, $pdo);
}
?>

<div class="titulo-con-boton">
  <h2 class="titulo-centrado">Reportes</h2>
</div>

<div class="reporte-box">
  <form id="formReporte" class="form-crud">
    <input type="number" name="anio" placeholder="Año" min="1983" max="2030" value="<?= $anio ?>" required>
    <select name="tipo">
      <option value="todos" <?= $tipo === 'todos' ? 'selected' : '' ?>>Todos</option>
      <option value="oficiales" <?= $tipo === 'oficiales' ? 'selected' : '' ?>>Oficiales</option>
      <option value="docentes" <?= $tipo === 'docentes' ? 'selected' : '' ?>>Docentes</option>
      <option value="mantenimiento" <?= $tipo === 'mantenimiento' ? 'selected' : '' ?>>Mantenimiento</option>
      <option value="administrativo" <?= $tipo === 'administrativo' ? 'selected' : '' ?>>Administrativo</option>
    </select>
    <button type="submit" class="btn btn-primario">Generar</button>
  </form>

  <div id="resultadoReporte">
    <?php foreach ($datos as $grupo => $filas): ?>
      <div class="tabla-grupo">
        <div class="bloque-titulo"><?= strtoupper($grupo) ?></div>
        <table>
          <thead>
            <tr>
              <th>Cargo</th>
              <th>Cantidad</th>
              <th>Promedio Salarial (L)</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($filas as $fila): ?>
              <tr>
                <td><?= htmlspecialchars($fila['categoria']) ?></td>
                <td><?= $fila['cantidad'] ?></td>
                <td>L <?= number_format($fila['promedio_salario'], 2) ?></td>
              </tr>
            <?php endforeach; ?>
            <?php
            if (is_array($filas) && count($filas) > 0) {
              $total = array_reduce($filas, fn($acc, $f) => $acc + $f['cantidad'], 0);
              echo "<tr><td><strong>Total</strong></td><td><strong>$total</strong></td><td></td></tr>";
            }
            ?>
          </tbody>
        </table>
      </div>
    <?php endforeach; ?>
  </div>
</div>
