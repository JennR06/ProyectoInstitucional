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
    case "mantenimiento":
      break;

    case "profesores":
      break;

    case "administrativo":
      break;

    case "reportes":
      break;

    case "notificaciones":
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

// Mostrar la sección de navbar
function mostrar(seccion) {
  if (seccion === "oficiales") {
    // Cargar historial de oficiales por AJAX
    fetch('historial_oficiales.php')
      .then(res => res.text())
      .then(html => {
        contenido.innerHTML = html;
        contenido.classList.remove("fade-in");
        void contenido.offsetWidth;
        contenido.classList.add("fade-in");
      });
  } else if (seccion === "mantenimiento") {
    // Cargar personal de mantenimiento por AJAX
    fetch('personal_manteminiento.php')
      .then(res => res.text())
      .then(html => {
        contenido.innerHTML = html;
        contenido.classList.remove("fade-in");
        void contenido.offsetWidth;
        contenido.classList.add("fade-in");
      });
  } else {
    mostrarBienvenida();
  }
}

// ...al final de script.js...

// ----- Funciones para oficiales -----
window.mostrarFormOficial = function() {
  document.getElementById('formDivOficial').style.display = 'block';
  document.getElementById('ofId').value = '';
  document.getElementById('ofNombre').value = '';
  document.getElementById('ofRango').value = '';
  document.getElementById('ofaños_asignado').value = '';
}

window.cerrarFormOficial = function() {
  document.getElementById('formDivOficial').style.display = 'none';
}

window.editarOficial = function(id, nombre, rango, anio) {
  document.getElementById('formDivOficial').style.display = 'block';
  document.getElementById('ofId').value = id;
  document.getElementById('ofNombre').value = nombre;
  document.getElementById('ofRango').value = rango;
  document.getElementById('ofaños_asignado').value = anio;
}

window.eliminarOficial = function(id) {
  if (!confirm('¿Eliminar este oficial?')) return;
  const datos = new FormData();
  datos.append('delete', id);
  fetch('historial_oficiales.php', {
    method: 'POST',
    body: datos
  })
  .then(res => res.text())
  .then(resp => {
    recargarOficiales();
  });
}

window.recargarOficiales = function() {
  fetch('historial_oficiales.php')
    .then(res => res.text())
    .then(html => {
      contenido.innerHTML = html;
      contenido.classList.remove("fade-in");
      void contenido.offsetWidth;
      contenido.classList.add("fade-in");
    });
}

// AJAX para guardar oficial
document.addEventListener('submit', function(e) {
  if (e.target && e.target.id === 'oficialForm') {
    e.preventDefault();
    const form = e.target;
    const datos = new FormData(form);
    datos.append('ajax', '1');
    fetch('historial_oficiales.php', {
      method: 'POST',
      body: datos
    })
    .then(res => res.text())
    .then(resp => {
      cerrarFormOficial();
      recargarOficiales();
    });
  }
});

// ----- Funciones para mantenimiento -----
window.mostrarFormMantenimiento = function() {
  document.getElementById('formDivMantenimiento').style.display = 'block';
  document.getElementById('pmId').value = '';
  document.getElementById('pmNombre').value = '';
  document.getElementById('pmCargo').value = '';
  document.getElementById('pmAñoIngreso').value = '';
}

window.cerrarFormMantenimiento = function() {
  document.getElementById('formDivMantenimiento').style.display = 'none';
}

window.editarMantenimiento = function(id, nombre, cargo, anio) {
  document.getElementById('formDivMantenimiento').style.display = 'block';
  document.getElementById('pmId').value = id;
  document.getElementById('pmNombre').value = nombre;
  document.getElementById('pmCargo').value = cargo;
  document.getElementById('pmAñoIngreso').value = anio;
}

window.eliminarMantenimiento = function(id) {
  if (!confirm('¿Eliminar este personal?')) return;
  const datos = new FormData();
  datos.append('delete', id);
  fetch('personal_manteminiento.php', {
    method: 'POST',
    body: datos
  })
  .then(res => res.text())
  .then(resp => {
    recargarMantenimiento();
  });
}

window.recargarMantenimiento = function() {
  fetch('personal_manteminiento.php')
    .then(res => res.text())
    .then(html => {
      contenido.innerHTML = html;
      contenido.classList.remove("fade-in");
      void contenido.offsetWidth;
      contenido.classList.add("fade-in");
    });
}

// AJAX para guardar mantenimiento
document.addEventListener('click', function(e) {
  if (e.target.closest('#mantenimientoForm')) {
    const form = document.getElementById('mantenimientoForm');
    if (form) {
      form.onsubmit = function(ev) {
        ev.preventDefault();
        const datos = new FormData(form);
        datos.append('ajax', '1');
        fetch('personal_manteminiento.php', {
          method: 'POST',
          body: datos
        })
        .then(res => res.text())
        .then(resp => {
          cerrarFormMantenimiento();
          recargarMantenimiento();
        });
      };
    }
  }
});