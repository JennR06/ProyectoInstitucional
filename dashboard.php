<?php
require 'db.php';

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

// Obtener parámetros de filtro
$filtro_tipo = $_GET['tipo'] ?? 'todos';
$filtro_genero = $_GET['genero'] ?? 'todos';
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

  <!-- Personal veterano con filtros AJAX -->
  <div class="dashboard-card">
    <div class="filtros-header">
      <h3>Personal</h3>
      <div class="filtros-container">
        <div class="filtros-form">
          <div class="filtro-group">
            <label for="tipo">Tipo de Personal:</label>
            <select name="tipo" id="tipo" onchange="aplicarFiltros()">
              <option value="todos" <?= $filtro_tipo == 'todos' ? 'selected' : '' ?>>Todos</option>
              <option value="Oficial" <?= $filtro_tipo == 'Oficial' ? 'selected' : '' ?>>Oficiales</option>
              <option value="Docente" <?= $filtro_tipo == 'Docente' ? 'selected' : '' ?>>Docentes</option>
              <option value="Mantenimiento" <?= $filtro_tipo == 'Mantenimiento' ? 'selected' : '' ?>>Mantenimiento</option>
              <option value="Administrativo" <?= $filtro_tipo == 'Administrativo' ? 'selected' : '' ?>>Administrativo</option>
            </select>
          </div>
          
          <div class="filtro-group">
            <label for="genero">Género:</label>
            <select name="genero" id="genero" onchange="aplicarFiltros()">
              <option value="todos" <?= $filtro_genero == 'todos' ? 'selected' : '' ?>>Todos</option>
              <option value="M" <?= $filtro_genero == 'M' ? 'selected' : '' ?>>Masculino</option>
              <option value="F" <?= $filtro_genero == 'F' ? 'selected' : '' ?>>Femenino</option>
            </select>
          </div>
          
          <button type="button" onclick="limpiarFiltros()" class="btn-limpiar">Limpiar Filtros</button>
        </div>
      </div>
    </div>
    
    <!-- Contenedor para los resultados AJAX -->
    <div id="resultados-veteranos">
      <?php include 'obtener_veteranos.php'; ?>
    </div>
  </div>

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


<script>
// Variable global para almacenar los filtros actuales
let filtrosActuales = {
    tipo: '<?= $filtro_tipo ?>',
    genero: '<?= $filtro_genero ?>'
};

function aplicarFiltros() {
    // Mostrar loading
    const contenedor = document.getElementById('resultados-veteranos');
    contenedor.innerHTML = '<div class="loading">🔄 Cargando resultados...</div>';
    
    // Obtener valores actuales
    const tipo = document.getElementById('tipo').value;
    const genero = document.getElementById('genero').value;
    
    // Actualizar filtros globales
    filtrosActuales.tipo = tipo;
    filtrosActuales.genero = genero;
    
    // Actualizar URL sin recargar la página
    const params = new URLSearchParams();
    if (tipo !== 'todos') params.set('tipo', tipo);
    if (genero !== 'todos') params.set('genero', genero);
    
    const newUrl = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
    window.history.replaceState({}, '', newUrl);
    
    // Realizar petición AJAX
    fetch(`obtener_veteranos.php?tipo=${tipo}&genero=${genero}`)
        .then(response => response.text())
        .then(html => {
            contenedor.innerHTML = html;
        })
        .catch(error => {
            console.error('Error:', error);
            contenedor.innerHTML = '<div class="error">❌ Error al cargar los datos</div>';
        });
}

function limpiarFiltros() {
    document.getElementById('tipo').value = 'todos';
    document.getElementById('genero').value = 'todos';
    aplicarFiltros();
}

// Funciones de exportación que usan los filtros actuales
function exportToPDF() {
    const { tipo, genero } = filtrosActuales;
    
    // Mostrar loading en el botón
    const btn = event.target;
    const originalText = btn.innerHTML;
    btn.innerHTML = '⏳ Generando PDF...';
    btn.disabled = true;
    
    // Abrir exportación con filtros
    const url = `export_pdf.php?tipo=${tipo}&genero=${genero}`;
    window.open(url, '_blank');
    
    // Restaurar botón después de 2 segundos
    setTimeout(() => {
        btn.innerHTML = originalText;
        btn.disabled = false;
    }, 2000);
}

function exportToExcel() {
    const { tipo, genero } = filtrosActuales;
    
    // Mostrar loading en el botón
    const btn = event.target;
    const originalText = btn.innerHTML;
    btn.innerHTML = '⏳ Generando Excel...';
    btn.disabled = true;
    
    // Forzar descarga con filtros
    const link = document.createElement('a');
    link.href = `export_excel.php?tipo=${tipo}&genero=${genero}`;
    link.download = `reporte_personal_${new Date().toISOString().split('T')[0]}.xls`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    // Restaurar botón después de 2 segundos
    setTimeout(() => {
        btn.innerHTML = originalText;
        btn.disabled = false;
    }, 2000);
}

// Aplicar filtros al cargar la página si hay parámetros en URL
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const tipo = urlParams.get('tipo');
    const genero = urlParams.get('genero');
    
    if (tipo || genero) {
        if (tipo) document.getElementById('tipo').value = tipo;
        if (genero) document.getElementById('genero').value = genero;
        aplicarFiltros();
    }
});

// Función para mostrar secciones (ya existente en tu script)
function mostrar(seccion) {
    // Tu código existente para mostrar secciones
    if (seccion === 'reportes') {
        window.location.reload(); // Recargar para ver el dashboard
    }
    // ... resto de tu código existente
}
</script>