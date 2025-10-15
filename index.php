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

  <!-- Script con defer para que se ejecute tras cargar el DOM -->
  <script src="script.js" defer></script>
</head>
<body>

  <!-- LOGIN -->
  <section id="login">
    <div class="login-box">
      <img src="img/LMH_LOGO.png"
           alt="Liceo Militar de Honduras"
           class="logo">
      <h2>Talento Humano</h2>

      <!-- Campos de usuario y contraseña -->
      <input type="text" id="usuario" placeholder="Usuario">
      <input type="password" id="clave" placeholder="Contraseña">

      <!-- Botón que dispara la validación en script.js -->
      <button type="button" onclick="validarLogin()">Entrar</button>

      <!-- Mensaje de error -->
      <p id="error" class="error"></p>
    </div>
  </section>

  <!-- DASHBOARD (oculto hasta login exitoso) -->
  <section id="dashboard">
    <!-- Barra superior -->
    <div class="navbar">
      <img src="img/LMH_LOGO.png"
           alt="Liceo Militar"
           class="logo-navbar">
      Sistema de Talento Humano - Liceo Militar de Honduras
    </div>

    <!-- Menú lateral -->
    <aside class="sidebar">
      <button type="button" onclick="mostrar('oficiales')">
        Historial de Oficiales
      </button>
      <button type="button" onclick="mostrar('mantenimiento')">
        Personal de Mantenimiento
      </button>
      <button type="button" onclick="mostrar('profesores')">
        Docentes
      </button>
      <button type="button" onclick="mostrar('administrativo')">
        Personal Administrativo
      </button>
      <button type="button" onclick="mostrar('reportes')">
        Reportes
      </button>
      <button type="button" onclick="mostrar('notificaciones')">
        Notificaciones
      </button>
      <button type="button" onclick="cerrarSesion()">
        Cerrar Sesión
      </button>
    </aside>

    <!-- Contenedor dinámico -->
   <main class="content" id="contenido">
  <div class="bienvenida">
    <h2>🎖️ Bienvenidos al Sistema de Talento Humano</h2>
    
    <p class="intro">
      Este sistema ha sido diseñado para fortalecer la gestión del personal del <strong>Liceo Militar de Honduras</strong>, 
      promoviendo la excelencia, la disciplina y el compromiso institucional.
    </p>

    <blockquote class="frase-motivacional">
      “La disciplina forma líderes, el talento los perfecciona.”
    </blockquote>

    <div class="info-box">
      <p><strong>¿Qué puedes hacer aquí?</strong></p>
      <ul>
        <li>📁 Consultar historial de oficiales, docentes y personal administrativo</li>
        <li>📊 Visualizar reportes y evaluaciones por año</li>
        <li>🔔 Recibir notificaciones importantes</li>
      </ul>
    </div>

    <p class="sugerencia">
      👉 Usa el menú lateral para comenzar tu recorrido.
    </p>
  </div>
</main>


</body>
</html>