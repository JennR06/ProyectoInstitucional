// js/script.js

// Referencias a elementos del DOM
const loginSection   = document.getElementById("login");
const dashboard      = document.getElementById("dashboard");
const usuarioInput   = document.getElementById("usuario");
const claveInput     = document.getElementById("clave");
const errorMsg       = document.getElementById("error");
const contenido      = document.getElementById("contenido");

// Usuarios y contraseñas válidos
const usuariosVal = {
  director: "1234",
  talento:  "abcd",
  admin:    "rinrom",
};

// Validar credenciales y mostrar dashboard
function validarLogin() {
  const user = usuarioInput.value.trim();
  const pass = claveInput.value.trim();

  if (usuariosVal[user] === pass) {
    errorMsg.innerText = "";
    loginSection.style.display   = "none";
    dashboard.style.display      = "block";
    dashboard.classList.add("active");
    // Restaurar contenido inicial
  } else {
    errorMsg.innerText = "Usuario o contraseña incorrectos";
  }
}

// Cerrar sesión y volver al login
function cerrarSesion() {
  dashboard.classList.remove("active");
  dashboard.style.display    = "none";
  loginSection.style.display = "flex";

  usuarioInput.value = "";
  claveInput.value   = "";
  errorMsg.innerText = "";

}

// Mostrar la sección correspondiente en el main#contenido
function mostrar(seccion) {
  let html = "";

  switch (seccion) {
    case "oficiales":
      html = `
        <h2>Historial de Oficiales</h2>
        <table>
          <tr><th>Nombre</th><th>Rango</th><th>Años de Servicio</th></tr>
          <tr><td>Cap. Juan Pérez</td><td>Capitán</td><td>12</td></tr>
          <tr><td>Tte. María López</td><td>Teniente</td><td>8</td></tr>
        </table>`;
      break;

    case "mantenimiento":
      html = `
        <h2>Personal de Mantenimiento</h2>
        <table>
          <tr><th>Nombre</th><th>Área</th></tr>
          <tr><td>Carlos Mejía</td><td>Jardinería</td></tr>
          <tr><td>Ana Torres</td><td>Limpieza</td></tr>
        </table>`;
      break;

    case "profesores":
      html = `
        <h2>Profesores</h2>
        <table>
          <tr><th>Nombre</th><th>Materia</th></tr>
          <tr><td>José Martínez</td><td>Matemáticas</td></tr>
          <tr><td>Laura Ramírez</td><td>Historia</td></tr>
        </table>`;
      break;

    case "administrativo":
      html = `
        <h2>Personal Administrativo</h2>
        <table>
          <tr><th>Nombre</th><th>Puesto</th></tr>
          <tr><td>Pedro Gómez</td><td>Secretario</td></tr>
          <tr><td>Sofía Hernández</td><td>Recepcionista</td></tr>
        </table>`;
      break;

    case "reportes":
      html = `
        <h2>Reportes</h2>
        <p>Aquí se mostrarán los reportes de personal y actividades.</p>`;
      break;

    case "notificaciones":
      html = `
        <h2>Notificaciones</h2>
        <ul>
          <li>📢 Reunión de docentes el viernes.</li>
          <li>📢 Inspección de limpieza el lunes.</li>
        </ul>`;
      break;

let usuarioActivo = ""; // Guarda el usuario que inició sesión

function validarLogin() {
  const user = usuarioInput.value.trim();
  const pass = claveInput.value.trim();

  if (usuariosVal[user] === pass) {
    usuarioActivo = user; // Guarda el nombre del usuario
    errorMsg.innerText = "";
    loginSection.style.display   = "none";
    dashboard.style.display      = "block";
    dashboard.classList.add("active");

    mostrarBienvenida(); // Muestra saludo personalizado
  } else {
    errorMsg.innerText = "Usuario o contraseña incorrectos";
  }
}

function mostrarBienvenida() {
  const saludo = usuarioActivo === "director"
    ? "Bienvenido, Director"
    : "Bienvenida, Talento Humano";

  contenido.innerHTML = `
    <div class="bienvenida">
      <h2>🎖️ ${saludo}</h2>
      <p class="intro">
        Este sistema ha sido diseñado para fortalecer la gestión del personal del <strong>Liceo Militar de Honduras</strong>, promoviendo la excelencia, la disciplina y el compromiso institucional.
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
  `;
}
  }

  // Inyectar y animar
  contenido.innerHTML = html;
  contenido.classList.remove("fade-in");
  void contenido.offsetWidth;              // fuerza reflow
  contenido.classList.add("fade-in");
}

// Disparar login con la tecla Enter
[usuarioInput, claveInput].forEach(el => {
  el.addEventListener("keyup", e => {
    if (e.key === "Enter") validarLogin();
  });
});

