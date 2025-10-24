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
      <h2>${saludo}</h2>
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
  if (!modal) return;
  // guardar elemento que tenía foco para restaurarlo luego
  window.__lastFocused = document.activeElement;

  // mostrar modal y marcar accesibilidad
  modal.style.display = 'flex';
  modal.removeAttribute('aria-hidden');

  const dialog = modal.querySelector('.modal-form') || modal;
  if (dialog) {
    dialog.setAttribute('tabindex', '-1');
    // mover foco al primer control o al dialog si no hay controles
    const focusables = getFocusableElements(dialog);
    const first = focusables.length ? focusables[0] : dialog;
    first.focus();
  }

  // instalar focus-trap
  const container = dialog || modal;
  window.__focusTrapHandler = function(e) {
    if (e.key !== 'Tab') return;
    const focusables = getFocusableElements(container);
    if (!focusables.length) {
      e.preventDefault();
      return;
    }
    const firstEl = focusables[0];
    const lastEl = focusables[focusables.length - 1];
    if (e.shiftKey) {
      if (document.activeElement === firstEl) {
        e.preventDefault();
        lastEl.focus();
      }
    } else {
      if (document.activeElement === lastEl) {
        e.preventDefault();
        firstEl.focus();
      }
    }
  };
  document.addEventListener('keydown', window.__focusTrapHandler);
};

window.cerrarFormOficial = function() {
  const modal = document.getElementById('formDivOficial');
  if (!modal) return;

  // quitar listener de focus-trap
  if (window.__focusTrapHandler) {
    document.removeEventListener('keydown', window.__focusTrapHandler);
    window.__focusTrapHandler = null;
  }

  // ocultar modal y actualizar accesibilidad
  // antes de esconder, mover foco fuera del modal (a opener si existe)
  const last = window.__lastFocused;
  try {
    if (last && typeof last.focus === 'function') last.focus();
  } catch (e) { /* ignore */ }

  // forzar blur dentro del modal para evitar warning aria-hidden sobre elemento con foco
  const activeInside = modal.contains(document.activeElement) ? document.activeElement : null;
  if (activeInside && typeof activeInside.blur === 'function') activeInside.blur();

  modal.setAttribute('aria-hidden', 'true');
  modal.style.display = 'none';
  window.__lastFocused = null;
};

window.editarOficial = function(datos) {
  const modal = document.getElementById('formDivOficial');
  if (modal) {
    modal.style.display = 'flex';
    document.getElementById('ofId').value = datos.id;
    document.getElementById('ofNombre').value = datos.nombre;
    document.getElementById('ofRango').value = datos.rango;
    document.getElementById('ofAniosAsignado').value = datos.anio;
    document.getElementById('ofNumeroIdentificacion').value = datos.numero_identificacion || '';
    document.getElementById('ofFechaNacimiento').value = datos.fecha_nacimiento || '';
    document.getElementById('ofNumeroTelefono').value = datos.numero_telefono || '';
    document.getElementById('ofDireccion').value = datos.direccion || '';
    document.getElementById('ofEstadoCivil').value = datos.estado_civil || '';
    document.getElementById('ofDepartamento').value = datos.departamento || '';
    document.getElementById('ofFoto').value = datos.foto || '';
    document.getElementById('ofDocumento').value = datos.documento || '';
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

// foco/modal management helpers
window.__lastFocused = null;
window.__focusTrapHandler = null;

function getFocusableElements(container) {
  if (!container) return [];
  return Array.from(container.querySelectorAll(
    'a[href], area[href], input:not([disabled]):not([type="hidden"]), select:not([disabled]), textarea:not([disabled]), button:not([disabled]), iframe, object, embed, [tabindex]:not([tabindex="-1"]), [contenteditable]'
  )).filter(el => el.offsetWidth || el.offsetHeight || el.getClientRects().length);
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
    btn.innerHTML = 'Generando...';
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
    btn.innerHTML = 'Generando...';
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

// Variable global para almacenar los filtros actuales
let filtrosActuales = {
    tipo: '<?= $filtro_tipo ?>',
    genero: '<?= $filtro_genero ?>'
};

function aplicarFiltros() {
    // Mostrar loading
    const contenedor = document.getElementById('resultados-veteranos');
    contenedor.innerHTML = '<div class="loading">Cargando...</div>';
    
    // Obtener valores actuales
    const tipo = document.getElementById('tipo').value;
    const genero = document.getElementById('genero').value;
    
    // Actualizar filtros globales
    filtrosActuales.tipo = tipo;
    filtrosActuales.genero = genero;
    
    // Realizar petición AJAX
    const xhr = new XMLHttpRequest();
    xhr.open('GET', `obtener_veteranos.php?tipo=${tipo}&genero=${genero}`, true);
    
    xhr.onload = function() {
        if (xhr.status === 200) {
            contenedor.innerHTML = xhr.responseText;
        } else {
            contenedor.innerHTML = '<div class="error">Error al cargar los datos</div>';
        }
    };
    
    xhr.onerror = function() {
        contenedor.innerHTML = '<div class="error">Error de conexión</div>';
    };
    
    xhr.send();
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
    btn.innerHTML = 'Generando...';
    btn.disabled = true;
    
    // Abrir exportación con filtros
    window.open(`export_pdf.php?tipo=${tipo}&genero=${genero}`, '_blank');
    
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
    btn.innerHTML = 'Generando...';
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


