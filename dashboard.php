<?php
require 'bd.php';

// Contar personal
try {
  $oficiales = $pdo->query("SELECT COUNT(*) FROM oficiales")->fetchColumn();
  $docentes = $pdo->query("SELECT COUNT(*) FROM docentes")->fetchColumn();
  $mantenimiento = $pdo->query("SELECT COUNT(*) FROM mantenimiento")->fetchColumn();
  $administrativo = $pdo->query("SELECT COUNT(*) FROM administrativo")->fetchColumn();
  $total = $oficiales + $docentes + $mantenimiento + $administrativo;
} catch (PDOException $e) {
  $oficiales = $docentes = $mantenimiento = $administrativo = $total = 0;
}

// Personal veterano (top 5)
try {
  $veteranos = $pdo->query("
    SELECT nombre, rango AS cargo, YEAR(CURDATE()) - años_asignado AS anos, 'Oficial' AS tipo 
    FROM oficiales
    ORDER BY anos DESC LIMIT 5
  ")->fetchAll();
} catch (PDOException $e) {
  $veteranos = [];
}
?>

<div class="dashboard-container">
  
  <div class="dashboard-header">
    <div>
      <h1>Panel de Control</h1>
      <p>INTEGRA</p>
    </div>
    <div class="dashboard-date">
       <?= date('d/m/Y') ?>
        <div class="export-buttons">
      <button onclick="exportToPDF()" class="export-btn pdf-btn">
        PDF
      </button>
      <button onclick="exportToExcel()" class="export-btn excel-btn">
        Excel
      </button>
    </div>
  </div>
</div>

  <!-- Tarjetas principales -->
  <div class="stats-grid">
    
    <div class="stat-card stat-blue">
      <div class="stat-icon"></div>
      <div class="stat-content">
        <div class="stat-value"><?= $administrativo ?></div>
        <div class="stat-label">Administrativo</div>
      </div>
    </div>

    <div class="stat-card stat-green">
      <div class="stat-icon"></div>
      <div class="stat-content">
        <div class="stat-value"><?= $oficiales ?></div>
        <div class="stat-label">Oficiales</div>
      </div>
    </div>

    <div class="stat-card stat-orange">
      <div class="stat-icon"></div>
      <div class="stat-content">
        <div class="stat-value"><?= $docentes ?></div>
        <div class="stat-label">Docentes</div>
      </div>
    </div>

    <div class="stat-card stat-purple">
      <div class="stat-icon"></div>
      <div class="stat-content">
        <div class="stat-value"><?= $mantenimiento ?></div>
        <div class="stat-label">Mantenimiento</div>
      </div>
    </div>

  </div>

  <!-- Personal veterano -->
  <?php if (count($veteranos) > 0): ?>
  <div class="dashboard-card">
    <h3>Personal con Más Antigüedad</h3>
    <div class="veteranos-list">
      <?php foreach ($veteranos as $v): ?>
        <div class="veterano-item">
          <span>👤 <?= htmlspecialchars($v['nombre']) ?></span>
          <span class="veterano-cargo"><?= htmlspecialchars($v['cargo']) ?></span>
          <span class="veterano-anos"><?= $v['anos'] ?> años</span>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>

  <!-- Accesos rápidos -->
  <div class="dashboard-card">
    <h3>Accesos Rápidos</h3>
    <div class="quick-actions">
      <button onclick="mostrar('oficiales')" class="quick-btn">
        <br>Oficiales
      </button>
      <button onclick="mostrar('profesores')" class="quick-btn">
        <br>Docentes
      </button>
      <button onclick="mostrar('mantenimiento')" class="quick-btn">
        <br>Mantenimiento
      </button>
      <button onclick="mostrar('administrativo')" class="quick-btn">
        <br>Administrativo
      </button>
    </div>
  </div>

</div>
