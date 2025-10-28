// Referencias a elementos del DOM
const loginSection = document.getElementById("login");
const dashboard = document.getElementById("dashboard");
const usuarioInput = document.getElementById("usuario");
const claveInput = document.getElementById("clave");
const errorMsg = document.getElementById("error");
const contenido = document.getElementById("contenido");

// Usuarios y contraseñas válidos
const usuariosVal = {
  rectoria: "1234",
  talento: "abcd",
  admin: "rinrom",
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
  const saludo = "Bienvenido al Sistema INTEGRA";
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
          <li> - Consultar historial del personal</li>
          <li> - Visualizar reportes</li>
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

// Mostrar la sección correspondiente
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
// FUNCIONES PARA OFICIALES
// ========================================
// === FUNCIONES PARA OFICIALES ===

function mostrarFormOficial() {
  const modal = document.getElementById('formDivOficial');
  if (modal) {
    modal.style.display = 'flex';
    const form = document.getElementById('oficialForm');
    if (form) form.reset();
    document.getElementById('ofId').value = '';
    // Reiniciar campos condicionales
    document.getElementById('ofCampoAlergias').style.display = 'none';
    document.getElementById('ofCampoAccidente').style.display = 'none';
  }
}

function cerrarFormOficial() {
  const modal = document.getElementById('formDivOficial');
  if (modal) modal.style.display = 'none';
}

function editarOficial(data) {
  const modal = document.getElementById('formDivOficial');
  if (!modal) return;

  modal.style.display = 'flex';

  // Campos generales
  const fields = [
    'id', 'nombre', 'rango', 'anio', 'notas', 'numero_identificacion',
    'fecha_nacimiento', 'numero_telefono', 'direccion', 'estado_civil',
    'departamento', 'genero', 'alergias', 'enfermedades_cronicas',
    'tipo_sangre', 'ultima_evaluacion', 'accidentes_laborales'
  ];

  fields.forEach(field => {
    const el = document.getElementById('of' + field.charAt(0).toUpperCase() + field.slice(1));
    if (el) el.value = data[field] || '';
  });

  // Checkbox EPP
  const eppCheckbox = document.getElementById('ofUsaEPP');
  if (eppCheckbox) eppCheckbox.checked = data.usa_epp == 1;

  // Checkbox Alergias (condicional)
  const alergiasCheck = document.getElementById('ofTieneAlergias');
  if (alergiasCheck) {
    const tieneAlergias = !!data.alergias; // true si hay valor
    alergiasCheck.checked = tieneAlergias;
    document.getElementById('ofCampoAlergias').style.display = tieneAlergias ? 'block' : 'none';
  }

  // Checkbox Accidentes (condicional)
  const accidenteCheck = document.getElementById('ofTieneAccidente');
  if (accidenteCheck) {
    const tieneAccidente = !!data.accidentes_laborales;
    accidenteCheck.checked = tieneAccidente;
    document.getElementById('ofCampoAccidente').style.display = tieneAccidente ? 'block' : 'none';
    
    // Si hay accidente, intentar cargar la fecha desde un campo separado (opcional)
    // Si no tienes `fecha_accidente` en tu DB, puedes omitir esto
    if (tieneAccidente && data.fecha_accidente) {
      document.getElementById('ofFechaAccidente').value = data.fecha_accidente;
    }
  }

  // Estado
  const estadoSelect = document.getElementById('ofEstado');
  if (estadoSelect) estadoSelect.value = data.estado || 'activo';
}

// === Eventos para campos médicos condicionales ===
function inicializarEventosMedicosOficiales() {
  const alergiasCheck = document.getElementById('ofTieneAlergias');
  const accidenteCheck = document.getElementById('ofTieneAccidente');

  if (alergiasCheck) {
    alergiasCheck.addEventListener('change', () => {
      const campo = document.getElementById('ofCampoAlergias');
      if (campo) {
        campo.style.display = alergiasCheck.checked ? 'block' : 'none';
        if (!alergiasCheck.checked) {
          document.getElementById('ofAlergias').value = '';
        }
      }
    });
  }

  if (accidenteCheck) {
    accidenteCheck.addEventListener('change', () => {
      const campo = document.getElementById('ofCampoAccidente');
      if (campo) {
        campo.style.display = accidenteCheck.checked ? 'block' : 'none';
        if (!accidenteCheck.checked) {
          document.getElementById('ofFechaAccidente').value = '';
          document.getElementById('ofAccidentes').value = '';
        }
      }
    });
  }
}

// Inicializar eventos médicos al cargar la página (si el formulario ya está presente)
document.addEventListener('DOMContentLoaded', () => {
  inicializarEventosMedicosOficiales();
});

// También inicializar si el contenido se carga dinámicamente (vía fetch)
function inicializarEventosOficiales() {
  inicializarEventosMedicosOficiales();
}

  // Checkbox EPP
  const eppCheckbox = document.getElementById('ofUsaEPP');
  if (eppCheckbox) eppCheckbox.checked = data.usa_epp || false;

  // Estado
  document.getElementById('ofEstado').value = data.estado || 'activo';

  mostrarFormOficial();

function eliminarOficial(id) {
  if (!confirm('¿Eliminar oficial? Esta acción no se puede deshacer.')) return;
  fetch('', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'delete=' + id
  }).then(() => location.reload());
}

function inicializarEventosOficiales() {
  document.getElementById('oficialForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    formData.append('ajax', '1');
    fetch('', { method: 'POST', body: formData })
      .then(res => res.text())
      .then(txt => {
        if (txt === 'OK') {
          cerrarFormOficial();
          location.reload();
        } else {
          alert('Error: ' + txt);
        }
      });
  });

  // Filtro por estado (si existe el contenedor)
  const lista = document.getElementById('listaOficiales');
  const resultContainer = document.getElementById('statusResult');
  if (lista && resultContainer) {
    let statusFilter = '';
    function aplicarFiltro() {
      const tarjetas = Array.from(lista.querySelectorAll('.perfil-card'));
      const matched = tarjetas.filter(card => {
        const status = (card.dataset.status || 'activo').toLowerCase();
        return !statusFilter || status === statusFilter;
      });
      tarjetas.forEach(card => card.style.display = matched.includes(card) ? 'block' : 'none');
      resultContainer.textContent = statusFilter 
        ? `Mostrando oficiales en estado "${statusFilter}" (${matched.length})`
        : `Total de oficiales: ${matched.length}`;
    }
    aplicarFiltro();
  }
}

// ========================================
// FUNCIONES PARA MANTENIMIENTO
// ========================================
function inicializarEventosMedicos() {
  const alergias = document.getElementById('pmTieneAlergias');
  const epp = document.getElementById('pmUsaEPP');
  const accidente = document.getElementById('pmTieneAccidente');
  if (alergias) {
    alergias.addEventListener('change', () => {
      const campo = document.getElementById('pmCampoAlergias');
      if (campo) campo.style.display = alergias.checked ? 'block' : 'none';
    });
  }
  if (epp) {
    epp.addEventListener('change', () => {
      const campo = document.getElementById('pmCampoEPP');
      if (campo) campo.style.display = epp.checked ? 'block' : 'none';
    });
  }
  if (accidente) {
    accidente.addEventListener('change', () => {
      const campo = document.getElementById('pmCampoAccidente');
      if (campo) campo.style.display = accidente.checked ? 'block' : 'none';
    });
  }
}

window.mostrarFormMantenimiento = function() {
  const modal = document.getElementById('formDivMantenimiento');
  if (modal) {
    modal.style.display = 'flex';
    const form = document.getElementById('mantenimientoForm');
    if (form) form.reset();
    document.getElementById('pmId').value = '';
    setTimeout(inicializarEventosMedicos, 100);
  }
}

window.cerrarFormMantenimiento = function() {
  const modal = document.getElementById('formDivMantenimiento');
  if (modal) modal.style.display = 'none';
}

window.editarMantenimiento = function(datos) {
  const modal = document.getElementById('formDivMantenimiento');
  if (modal) {
    modal.style.display = 'flex';
    const campos = ['id','nombre','cargo','anio','notas','estado_laboral','area_asignada','supervisor','turno','horario','telefono','correo'];
    campos.forEach(campo => {
      const el = document.getElementById('pm'+campo.charAt(0).toUpperCase() + campo.slice(1));
      if (el) el.value = datos[campo] || '';
    });
    const medicos = [
      {key: 'estado_salud', id: 'pmEstadoSalud'},
      {key: 'detalle_alergias', id: 'pmDetalleAlergias'},
      {key: 'tipo_epp', id: 'pmTipoEPP'},
      {key: 'ultima_evaluacion', id: 'pmUltimaEvaluacion'},
      {key: 'proxima_evaluacion', id: 'pmProximaEvaluacion'},
      {key: 'fecha_accidente', id: 'pmFechaAccidente'},
      {key: 'detalle_accidente', id: 'pmDetalleAccidente'}
    ];
    medicos.forEach(m => {
      const el = document.getElementById(m.id);
      if (el) el.value = datos[m.key] || '';
    });
    const checkboxes = [
      {key: 'tiene_alergias', id: 'pmTieneAlergias'},
      {key: 'usa_epp', id: 'pmUsaEPP'},
      {key: 'tiene_accidente', id: 'pmTieneAccidente'}
    ];
    checkboxes.forEach(cb => {
      const el = document.getElementById(cb.id);
      if (el) el.checked = datos[cb.key] == 1;
    });
    setTimeout(() => {
      inicializarEventosMedicos();
      if (document.getElementById('pmTieneAlergias')?.checked) document.getElementById('pmCampoAlergias').style.display = 'block';
      if (document.getElementById('pmUsaEPP')?.checked) document.getElementById('pmCampoEPP').style.display = 'block';
      if (document.getElementById('pmTieneAccidente')?.checked) document.getElementById('pmCampoAccidente').style.display = 'block';
    }, 100);
  }
}

window.eliminarMantenimiento = function(id) {
  if (!confirm('¿Está seguro de eliminar este personal?\nEsto también eliminará sus archivos asociados.')) return;
  const datos = new FormData();
  datos.append('delete', id);
  fetch('personal_mantenimiento.php', { method: 'POST', body: datos })
    .then(() => {
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
    form.onsubmit = function(e) {
      e.preventDefault();
      const datos = new FormData(form);
      datos.append('ajax', '1');
      fetch('personal_mantenimiento.php', { method: 'POST', body: datos })
        .then(res => res.text())
        .then(resp => {
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
    };
  }
  inicializarEventosMedicos();
}

// ========================================
// FUNCIONES PARA DOCENTES
// ========================================
window.mostrarFormDocente = function() {
  const modal = document.getElementById('formDivDocente');
  if (modal) {
    modal.style.display = 'flex';
    ['docId','docNombre','docEspecialidad','docAnioIngreso','docNotas','docNivelEducativo','docHorario']
      .forEach(f => { const el = document.getElementById(f); if (el) el.value = ''; });
    document.getElementById('docFoto').value = '';
    document.getElementById('docDocumento').value = '';
  }
}

window.cerrarFormDocente = function() {
  const modal = document.getElementById('formDivDocente');
  if (modal) modal.style.display = 'none';
}

window.editarDocente = function(datos) {
  const modal = document.getElementById('formDivDocente');
  if (modal) {
    modal.style.display = 'flex';
    ['id','nombre','especialidad','anio','notas','nivel_educativo','horario']
      .forEach(f => {
        const el = document.getElementById('doc'+f.charAt(0).toUpperCase() + f.slice(1));
        if (el) el.value = datos[f] || '';
      });
  }
}

window.eliminarDocente = function(id) {
  if (!confirm('¿Está seguro de eliminar este docente?\nEsto también eliminará sus archivos asociados.')) return;
  fetch('docentes.php', { method: 'POST', body: new FormData().append('delete', id) })
    .then(() => {
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
    form.onsubmit = function(e) {
      e.preventDefault();
      const datos = new FormData(form);
      datos.append('ajax', '1');
      fetch('docentes.php', { method: 'POST', body: datos })
        .then(res => res.text())
        .then(resp => {
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
    ['pmId','pmNombre','pmCargo','pmAnioIngreso','pmNotas']
      .forEach(f => { const el = document.getElementById(f); if (el) el.value = ''; });
    document.getElementById('pmFoto').value = '';
    document.getElementById('pmDocumento').value = '';
  }
}

window.cerrarFormAdministrativo = function() {
  const modal = document.getElementById('formDivAdministrativo');
  if (modal) modal.style.display = 'none';
}

window.editarAdministrativo = function(datos) {
  const modal = document.getElementById('formDivAdministrativo');
  if (modal) {
    modal.style.display = 'flex';
    ['id','nombre','cargo','anio','notas']
      .forEach(f => {
        const el = document.getElementById('pm'+f.charAt(0).toUpperCase() + f.slice(1));
        if (el) el.value = datos[f] || '';
      });
  }
}

window.eliminarAdministrativo = function(id) {
  if (!confirm('¿Está seguro de eliminar este personal?\nEsto también eliminará sus archivos asociados.')) return;
  fetch('personal_administrativo.php', { method: 'POST', body: new FormData().append('delete', id) })
    .then(() => {
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
    form.onsubmit = function(e) {
      e.preventDefault();
      const datos = new FormData(form);
      datos.append('ajax', '1');
      fetch('personal_administrativo.php', { method: 'POST', body: datos })
        .then(res => res.text())
        .then(resp => {
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
    };
  }
}

// ========================================
// FUNCIONES GENERALES
// ========================================
window.Inicio = function() {
  if (loginSection) loginSection.style.display = 'none';
  if (dashboard) {
    dashboard.style.display = 'block';
    dashboard.classList.add('active');
  }
  mostrarBienvenida();
  if (contenido) {
    contenido.classList.remove('fade-in');
    void contenido.offsetWidth;
    contenido.classList.add('fade-in');
    contenido.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }
};

function imprimirDocumento(url) {
  if (!url) return alert('Documento no disponible');
  const win = window.open('print.php?f=' + encodeURIComponent(url), '_blank');
  if (!win) {
    alert('Ventanas emergentes bloqueadas. Permite popups para imprimir.');
  }
}
