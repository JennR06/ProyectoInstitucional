<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>INTEGRA</title>
  <!-- Hoja de estilos principal -->
  <link rel="stylesheet" href="styles.css">

  <!-- Script con defer para que se ejecute tras cargar el DOM -->
  <script src="script.js" defer></script>
</head> 
<body>

  <!-- LOGIN -->
  <section id="login" class="login-page">
    <div class="login-container">
      <div class="login-right">
        <h1>BIENVENIDOS AL SISTEMA INTEGRA</h1>
  
      </div>

      <div class="login-right">
        <div class="login-box">
          <img src="img/LMH_LOGO.png" alt="Liceo Militar" class="logo">
          <h2>Iniciar Sesión</h2>

          <input type="text" id="usuario" placeholder="Email Address">
          <input type="password" id="clave" placeholder="Password">

          <label class="recuerdame">
            <input type="checkbox" id="remember"> Recuerdame
          </label>

          <button type="button" onclick="validarLogin()" class="btn btn-signin">Iniciar Sesión</button>

          <p id="error" class="error"></p>
          <a href="#" class="olvide">¿Olvidaste tu contraseña?</a>
        </div>
      </div>
    </div>
  </section>

  <!-- DASHBOARD (oculto hasta login exitoso) -->
  <section id="dashboard">
    <!-- Barra superior -->
    <div class="navbar">
    </div>

    <!-- Menú lateral mejorado -->
    <aside class="sidebar" aria-label="Menú principal">
      <div class="sidebar-top">
        <img src="img/LMH_LOGO.png" alt="Logo" class="sidebar-logo">
        <span class="sidebar-title">INTEGRA</span>
      </div>

      <ul class="nav-list">
        <li><button class="nav-btn" type="button" onclick="Inicio()">
          <!-- Inicio SVG -->
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden>
            <path d="M3 9.5L12 3l9 6.5V21a1 1 0 0 1-1 1h-5v-7H9v7H4a1 1 0 0 1-1-1V9.5z" fill="currentColor"/>
          </svg>
          <span>Inicio</span>
        </button></li>

        <li><button class="nav-btn" type="button" onclick="mostrar('oficiales')">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden>
            <path d="M12 2L2 7v7c0 5 4 8 10 8s10-3 10-8V7l-10-5z" fill="currentColor"/>
          </svg>
          <span>Historial de Oficiales</span>
        </button></li>

        <li><button class="nav-btn" type="button" onclick="mostrar('mantenimiento')">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden>
            <path d="M21 13v7a1 1 0 0 1-1 1h-6v-6h6zM3 6h18v4H3z" fill="currentColor"/>
          </svg>
          <span>Personal de Mantenimiento</span>
        </button></li>

        <li><button class="nav-btn" type="button" onclick="mostrar('profesores')">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden>
            <path d="M12 2a4 4 0 1 0 0 8 4 4 0 0 0 0-8zM4 20a8 8 0 0 1 16 0H4z" fill="currentColor"/>
          </svg>
          <span>Docentes</span>
        </button></li>

        <li><button class="nav-btn" type="button" onclick="mostrar('administrativo')">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden>
            <path d="M3 6h18v2H3zM5 10h14v10H5z" fill="currentColor"/>
          </svg>
          <span>Personal Administrativo</span>
        </button></li>

        <li><button class="nav-btn" type="button" onclick="mostrar('reportes')">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden>
            <path d="M3 3h18v4H3zM6 11h3v9H6zM11 7h3v13h-3zM16 13h3v7h-3z" fill="currentColor"/>
          </svg>
          <span>Reportes</span>
        </button></li>


      <div class="sidebar-footer">
        <button class="nav-btn danger" type="button" onclick="cerrarSesion()">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden>
            <path d="M16 13v-2H7V8l-5 4 5 4v-3zM20 3h-8v2h8v14h-8v2h8a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2z" fill="currentColor"/>
          </svg>
          <span>Cerrar Sesión</span>
        </button>
      </div>
    </aside>

    <!-- Contenedor dinámico -->
   <main class="content" id="contenido">
    <div class="bienvenida">
    <h2> Bienvenidos al Sistema INTEGRA</h2>
    
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
        <li> - Consultar historial del personal</li>
        <li> -  Visualizar reportes</li>
      </ul>
    </div>

</main>


</body>
</html>