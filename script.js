// Referencias a elementos del DOM
const loginSection   = document.getElementById("login");
const dashboard      = document.getElementById("dashboard");
const usuarioInput   = document.getElementById("usuario");
const claveInput     = document.getElementById("clave");
const errorMsg       = document.getElementById("error");
const contenido      = document.getElementById("contenido");

// Usuarios y contraseñas válidos
const usuariosVal = {
  rectoria: "1234",
  talento:  "abcd",
  admin:    "rinrom",
};

let usuarioActivo = '';

// Validar credenciales y mostrar dashboard
function validarLogin() {
  const user = usuarioInput.value.trim();
  const pass = claveInput.value.trim();

  if (usuariosVal[user] === pass) {
    usuarioActivo = user;
    errorMsg.innerText = "";
    loginSection.style.display = "none";
    dashboard.style.display = "block";
    dashboard.classList.add("active");
    mostrarBienvenida();
  } else {
    errorMsg.innerText = "Usuario o contraseña incorrectos";
  }
}

// Cerrar sesión y volver al login
function cerrarSesion() {
  dashboard.classList.remove("active");
  dashboard.style.display = "none";
  loginSection.style.display = "flex";
  usuarioInput.value = "";
  claveInput.value = "";
  errorMsg.innerText = "";
}

// Mostrar pantalla de bienvenida
function mostrarBienvenida() {
  const saludo = usuarioActivo === "director"
    ? "Bienvenido al Sistema INTEGRA"
    : "Bienvenido al Sistema INTEGRA";

  contenido.innerHTML = `
    <div class="bienvenida">
      <h2>🎖️ ${saludo}</h2>
      <p class="intro">
        Este sistema ha sido diseñado para fortalecer la gestión del personal del <strong>Liceo Militar de Honduras</strong>, 
        promoviendo la excelencia, la disciplina y el compromiso institucional.
      </p>
      <blockquote class="frase-motivacional">
        "La disciplina forma líderes, el talento los perfecciona."
      </blockquote>
      <div class="info-box">
        <p><strong>¿Qué puedes hacer aquí?</strong></p>
        <ul>
        <li> -  Consultar historial del personal</li>
        <li> -  Visualizar reportes</li>
        <li> -  Recibir notificaciones importantes</li>
      </ul>
      </div>
    </div>
  `;
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
    fetch('historial_oficiales.php')
      .then(res => res.text())
      .then(html => {
        contenido.innerHTML = html;
        contenido.classList.remove("fade-in");
        void contenido.offsetWidth;
        contenido.classList.add("fade-in");
        inicializarEventosOficiales();
      })
      .catch(err => console.error('Error cargando oficiales:', err));
  } else if (seccion === "mantenimiento") {
    fetch('personal_mantenimiento.php')
      .then(res => res.text())
      .then(html => {
        contenido.innerHTML = html;
        contenido.classList.remove("fade-in");
        void contenido.offsetWidth;
        contenido.classList.add("fade-in");
        inicializarEventosMantenimiento();
      })
      .catch(err => console.error('Error cargando mantenimiento:', err));
  } else if (seccion === "profesores") { 
    fetch('docentes.php')
      .then(res => res.text())
      .then(html => {
        contenido.innerHTML = html;
        contenido.classList.remove("fade-in");
        void contenido.offsetWidth;
        contenido.classList.add("fade-in");
        inicializarEventosDocentes();
      })
      .catch(err => console.error('Error cargando docentes:', err));
  } else if (seccion === "administrativo") { 
    fetch('personal_administrativo.php')
      .then(res => res.text())
      .then(html => {
        contenido.innerHTML = html;
        contenido.classList.remove("fade-in");
        void contenido.offsetWidth;
        contenido.classList.add("fade-in");
        inicializarEventosAdministrativo();
      })
      .catch(err => console.error('Error cargando administrativo:', err));
  } else if (seccion === "reportes") {
    fetch('dashboard.php')
      .then(res => res.text())
      .then(html => {
        contenido.innerHTML = html;
        contenido.classList.remove("fade-in");
        void contenido.offsetWidth;
        contenido.classList.add("fade-in");
      })
      .catch(err => console.error('Error cargando dashboard:', err)); 
  } else {
    mostrarBienvenida();
  }
}

// ========================================
// FUNCIONES PARA OFICIALES (ACTUALIZADO)
// ========================================

window.mostrarFormOficial = function() {
  const modal = document.getElementById('formDivOficial');
  if (modal) {
    modal.style.display = 'flex';
    document.getElementById('ofId').value = '';
    document.getElementById('ofNombre').value = '';
    document.getElementById('ofRango').value = '';
    document.getElementById('ofAniosAsignado').value = '';
    document.getElementById('ofNotas').value = '';
    document.getElementById('ofFoto').value = '';
    document.getElementById('ofDocumento').value = '';
  }
}

window.cerrarFormOficial = function() {
  const modal = document.getElementById('formDivOficial');
  if (modal) {
    modal.style.display = 'none';
  }
}

window.editarOficial = function(datos) {
  const modal = document.getElementById('formDivOficial');
  if (modal) {
    modal.style.display = 'flex';
    document.getElementById('ofId').value = datos.id;
    document.getElementById('ofNombre').value = datos.nombre;
    document.getElementById('ofRango').value = datos.rango;
    document.getElementById('ofAniosAsignado').value = datos.anio;
    document.getElementById('ofNotas').value = datos.notas || '';
  }
}

window.eliminarOficial = function(id) {
  if (!confirm('¿Está seguro de eliminar este oficial?\n\nEsto también eliminará sus archivos asociados.')) return;
  
  const datos = new FormData();
  datos.append('delete', id);
  
  fetch('historial_oficiales.php', {
    method: 'POST',
    body: datos
  })
  .then(res => res.text())
  .then(resp => {
    alert('✅ Oficial eliminado correctamente');
    recargarOficiales();
  })
  .catch(err => {
    console.error('Error:', err);
    alert('❌ Error al eliminar');
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
      inicializarEventosOficiales();
    });
}

function inicializarEventosOficiales() {
  const form = document.getElementById('oficialForm');
  if (form) {
    form.onsubmit = null;
    
    form.onsubmit = function(e) {
      e.preventDefault();
      
      console.log('Formulario de oficial enviado');
      
      const datos = new FormData(form);
      datos.append('ajax', '1');
      
      fetch('historial_oficiales.php', {
        method: 'POST',
        body: datos
      })
      .then(res => res.text())
      .then(resp => {
        console.log('Respuesta del servidor:', resp);
        if (resp.trim() === 'OK') {
          alert('✅ Oficial guardado correctamente');
          cerrarFormOficial();
          recargarOficiales();
        } else {
          alert('⚠️ Error: ' + resp);
        }
      })
      .catch(err => {
        console.error('Error en fetch:', err);
        alert('❌ Error de conexión');
      });
      
      return false;
    };
  }
}

// ========================================
// FUNCIONES PARA MANTENIMIENTO (ACTUALIZADO)
// ========================================

window.mostrarFormMantenimiento = function() {
  const modal = document.getElementById('formDivMantenimiento');
  if (modal) {
    modal.style.display = 'flex';
    document.getElementById('pmId').value = '';
    document.getElementById('pmNombre').value = '';
    document.getElementById('pmCargo').value = '';
    document.getElementById('pmAnioIngreso').value = '';
    document.getElementById('pmNotas').value = '';
    document.getElementById('pmFoto').value = '';
    document.getElementById('pmDocumento').value = '';
  }
}

window.cerrarFormMantenimiento = function() {
  const modal = document.getElementById('formDivMantenimiento');
  if (modal) {
    modal.style.display = 'none';
  }
}

window.editarMantenimiento = function(datos) {
  const modal = document.getElementById('formDivMantenimiento');
  if (modal) {
    modal.style.display = 'flex';
    document.getElementById('pmId').value = datos.id;
    document.getElementById('pmNombre').value = datos.nombre;
    document.getElementById('pmCargo').value = datos.cargo;
    document.getElementById('pmAnioIngreso').value = datos.anio;
    document.getElementById('pmNotas').value = datos.notas || '';
  }
}

window.eliminarMantenimiento = function(id) {
  if (!confirm('¿Está seguro de eliminar este personal?\n\nEsto también eliminará sus archivos asociados.')) return;
  
  const datos = new FormData();
  datos.append('delete', id);
  
  fetch('personal_mantenimiento.php', {
    method: 'POST',
    body: datos
  })
  .then(res => res.text())
  .then(resp => {
    alert('✅ Personal eliminado correctamente');
    recargarMantenimiento();
  })
  .catch(err => {
    console.error('Error:', err);
    alert('❌ Error al eliminar');
  });
}

window.recargarMantenimiento = function() {
  fetch('personal_mantenimiento.php')
    .then(res => res.text())
    .then(html => {
      contenido.innerHTML = html;
      contenido.classList.remove("fade-in");
      void contenido.offsetWidth;
      contenido.classList.add("fade-in");
      inicializarEventosMantenimiento();
    });
}

function inicializarEventosMantenimiento() {
  const form = document.getElementById('mantenimientoForm');
  if (form) {
    form.onsubmit = null;
    
    form.onsubmit = function(e) {
      e.preventDefault();
      
      console.log('Formulario de mantenimiento enviado');
      
      const datos = new FormData(form);
      datos.append('ajax', '1');
      
      fetch('personal_mantenimiento.php', {
        method: 'POST',
        body: datos
      })
      .then(res => res.text())
      .then(resp => {
        console.log('Respuesta del servidor:', resp);
        
        if (resp.trim() === 'OK') {
          alert('✅ Personal guardado correctamente');
          cerrarFormMantenimiento();
          recargarMantenimiento();
        } else {
          alert('⚠️ Error: ' + resp);
        }
      })
      .catch(err => {
        console.error('Error en fetch:', err);
        alert('❌ Error de conexión');
      });
      
      return false;
    };
  }
}

// ========================================
// FUNCIONES PARA DOCENTES
// ========================================

window.mostrarFormDocente = function() {
  const modal = document.getElementById('formDivDocente');
  if (modal) {
    modal.style.display = 'flex';
    document.getElementById('docId').value = '';
    document.getElementById('docNombre').value = '';
    document.getElementById('docEspecialidad').value = '';
    document.getElementById('docAnioIngreso').value = '';
    document.getElementById('docNotas').value = '';
    document.getElementById('docNivelEducativo').value = '';
    document.getElementById('docHorario').value = '';
    document.getElementById('docFoto').value = '';
    document.getElementById('docDocumento').value = '';
  }
}

window.cerrarFormDocente = function() {
  const modal = document.getElementById('formDivDocente');
  if (modal) {
    modal.style.display = 'none';
  }
}

window.editarDocente = function(datos) {
  const modal = document.getElementById('formDivDocente');
  if (modal) {
    modal.style.display = 'flex';
    document.getElementById('docId').value = datos.id;
    document.getElementById('docNombre').value = datos.nombre;
    document.getElementById('docEspecialidad').value = datos.especialidad;
    document.getElementById('docAnioIngreso').value = datos.anio;
    document.getElementById('docNotas').value = datos.notas || '';
    document.getElementById('docNivelEducativo').value = datos.nivel_educativo || '';
    document.getElementById('docHorario').value = datos.horario || '';
  }
}

window.eliminarDocente = function(id) {
  if (!confirm('¿Está seguro de eliminar este docente?\n\nEsto también eliminará sus archivos asociados.')) return;
  
  const datos = new FormData();
  datos.append('delete', id);
  
  fetch('docentes.php', {
    method: 'POST',
    body: datos
  })
  .then(res => res.text())
  .then(resp => {
    alert('✅ Docente eliminado correctamente');
    recargarDocentes();
  })
  .catch(err => {
    console.error('Error:', err);
    alert('❌ Error al eliminar');
  });
}

window.recargarDocentes = function() {
  fetch('docentes.php')
    .then(res => res.text())
    .then(html => {
      contenido.innerHTML = html;
      contenido.classList.remove("fade-in");
      void contenido.offsetWidth;
      contenido.classList.add("fade-in");
      inicializarEventosDocentes();
    });
}

function inicializarEventosDocentes() {
  const form = document.getElementById('docenteForm');
  if (form) {
    form.onsubmit = null;
    
    form.onsubmit = function(e) {
      e.preventDefault();
      
      console.log('Formulario de docente enviado');
      
      const datos = new FormData(form);
      datos.append('ajax', '1');
      
      fetch('docentes.php', {
        method: 'POST',
        body: datos
      })
      .then(res => res.text())
      .then(resp => {
        console.log('Respuesta del servidor:', resp);
        
        if (resp.trim() === 'OK') {
          alert('✅ Docente guardado correctamente');
          cerrarFormDocente();
          recargarDocentes();
        } else {
          alert('⚠️ Error: ' + resp);
        }
      })
      .catch(err => {
        console.error('Error en fetch:', err);
        alert('❌ Error de conexión');
      });
      
      return false;
    };
  }
}

// ========================================
// FUNCIONES PARA ADMINISTRATIVO
// ========================================

window.mostrarFormAdministrativo = function() {
  const modal = document.getElementById('formDivAdministrativo');
  if (modal) {
    modal.style.display = 'flex';
    document.getElementById('pmId').value = '';
    document.getElementById('pmNombre').value = '';
    document.getElementById('pmCargo').value = '';
    document.getElementById('pmAnioIngreso').value = '';
    document.getElementById('pmNotas').value = '';
    document.getElementById('pmFoto').value = '';
    document.getElementById('pmDocumento').value = '';
  }
}

window.cerrarFormAdministrativo = function() {
  const modal = document.getElementById('formDivAdministrativo');
  if (modal) {
    modal.style.display = 'none';
  }
}

window.editarAdministrativo = function(datos) {
  const modal = document.getElementById('formDivAdministrativo');
  if (modal) {
    modal.style.display = 'flex';
    document.getElementById('pmId').value = datos.id;
    document.getElementById('pmNombre').value = datos.nombre;
    document.getElementById('pmCargo').value = datos.cargo;
    document.getElementById('pmAnioIngreso').value = datos.anio;
    document.getElementById('pmNotas').value = datos.notas || '';
  }
}

window.eliminarAdministrativo = function(id) {
  if (!confirm('¿Está seguro de eliminar este personal?\n\nEsto también eliminará sus archivos asociados.')) return;
  
  const datos = new FormData();
  datos.append('delete', id);

  fetch('personal_administrativo.php', {
    method: 'POST',
    body: datos
  })
  .then(res => res.text())
  .then(resp => {
    alert('✅ Personal eliminado correctamente');
    recargarAdministrativo();
  })
  .catch(err => {
    console.error('Error:', err);
    alert('❌ Error al eliminar');
  });
}

window.recargarAdministrativo = function() {
  fetch('personal_administrativo.php')
    .then(res => res.text())
    .then(html => {
      contenido.innerHTML = html;
      contenido.classList.remove("fade-in");
      void contenido.offsetWidth;
      contenido.classList.add("fade-in");
      inicializarEventosAdministrativo();
    });
}

function inicializarEventosAdministrativo() {
  const form = document.getElementById('administrativoForm');
  if (form) {
    form.onsubmit = null;
    
    form.onsubmit = function(e) {
      e.preventDefault();

      console.log('Formulario de Administrativo enviado');

      const datos = new FormData(form);
      datos.append('ajax', '1');
      
      fetch('personal_administrativo.php', {
        method: 'POST',
        body: datos
      })
      .then(res => res.text())
      .then(resp => {
        console.log('Respuesta del servidor:', resp);
        
        if (resp.trim() === 'OK') {
          alert('✅ Personal guardado correctamente');
          cerrarFormAdministrativo();
          recargarAdministrativo();
        } else {
          alert('⚠️ Error: ' + resp);
        }
      })
      .catch(err => {
        console.error('Error en fetch:', err);
        alert('❌ Error de conexión');
      });
      
      return false;
    };
  }
}


// FUNCIONES PARA NOOTIFICACIONES


window.mostrarFormNotificacion = function() {
  document.getElementById('formDivNotificacion').style.display = 'flex';
};

window.cerrarFormNotificacion = function() {
  document.getElementById('formDivNotificacion').style.display = 'none';
};

window.eliminarNotificacion = function(id) {
  if (!confirm('¿Eliminar esta notificación?')) return;
  const datos = new FormData();
  datos.append('delete', id);

  fetch('notificaciones.php', {
    method: 'POST',
    body: datos
  })
  .then(res => res.text())
  .then(resp => {
    alert('✅ Eliminada');
    mostrar('notificaciones');
  });
};

function inicializarEventosNotificaciones() {
  const form = document.getElementById('notificacionForm');
  if (form) {
    form.onsubmit = function(e) {
      e.preventDefault();
      const datos = new FormData(form);
      datos.append('ajax', '1');

      fetch('notificaciones.php', {
        method: 'POST',
        body: datos
      })
      .then(res => res.text())
      .then(resp => {
        if (resp.trim() === 'OK') {
          alert('✅ Notificación guardada');
          cerrarFormNotificacion();
          mostrar('notificaciones');
        } else {
          alert('⚠️ Error: ' + resp);
        }
      });
    };
  }
}

// ========================================
// FUNCIONES PARA REPORTES
// ========================================
function inicializarEventosReportes() {
  const form = document.getElementById('formReporte');
  if (form) {
    form.onsubmit = function(e) {
      e.preventDefault();
      const datos = new FormData(form);

      fetch('reportes.php', {
        method: 'POST',
        body: datos
      })
      .then(res => res.text())
      .then(html => {
        document.getElementById('contenido').innerHTML = html;
        inicializarEventosReportes(); // Reasigna eventos tras recarga
      })
      .catch(err => {
        console.error('Error en reporte:', err);
        alert('❌ Error al generar el reporte');
      });
    };
  }
}
function recargarReportes() {
  fetch('reportes.php')
    .then(res => res.text())
    .then(html => {
      contenido.innerHTML = html; 
      contenido.classList.remove("fade-in");
      void contenido.offsetWidth;
      contenido.classList.add("fade-in");
      inicializarEventosReportes();
    }); 
}

// Opción A — redirigir / recargar la página principal
// Reemplazamos por comportamiento SPA: mostrar la bienvenida sin recargar
window.Inicio = function() {
  // Asegurar que el dashboard esté visible
  if (loginSection) loginSection.style.display = 'none';
  if (dashboard) {
    dashboard.style.display = 'block';
    dashboard.classList.add('active');
  }

  // Mostrar contenido de bienvenida y animar
  mostrarBienvenida();

  if (contenido) {
    contenido.classList.remove('fade-in');
    void contenido.offsetWidth; // reflow para reiniciar animación
    contenido.classList.add('fade-in');
    contenido.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }
};
// ...existing code...

function imprimirDocumento(url) {
  if (!url) return alert('Documento no disponible');
  // usar el endpoint intermedio que muestra el PDF y lanza print()
  const win = window.open('print.php?f=' + encodeURIComponent(url), '_blank');
  if (!win) {
    alert('Ventanas emergentes bloqueadas. Permite popups para imprimir.');
  }
}


// En tu archivo JavaScript o dentro de <script> tags en dashboard.php
function exportToPDF() {
    // Mostrar loading
    const btn = event.target;
    const originalText = btn.innerHTML;
    btn.innerHTML = '⏳ Generando...';
    btn.disabled = true;
    
    // Abrir en nueva pestaña o descargar
    window.open('export_pdf.php', '_blank');
    
    // Restaurar botón después de 2 segundos
    setTimeout(() => {
        btn.innerHTML = originalText;
        btn.disabled = false;
    }, 2000);
}

function exportToExcel() {
    // Mostrar loading
    const btn = event.target;
    const originalText = btn.innerHTML;
    btn.innerHTML = '⏳ Generando...';
    btn.disabled = true;
    
    // Forzar descarga
    const link = document.createElement('a');
    link.href = 'export_excel.php';
    link.download = 'reporte_dashboard_' + new Date().toISOString().split('T')[0] + '.xls';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    // Restaurar botón después de 2 segundos
    setTimeout(() => {
        btn.innerHTML = originalText;
        btn.disabled = false;
    }, 2000);
}