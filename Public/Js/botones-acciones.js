/**
 * Script unificado para manejar todas las acciones de botones en el Sistema NAS
 * Garantiza que los botones de editar y eliminar funcionen correctamente
 */

// Función para abrir el modal de confirmación de eliminación
function confirmarEliminar(ruta, nombre, tipo) {
    console.log("Confirmando eliminación:", tipo, ruta, nombre);
    
    // Verificar si el modal existe
    if ($("#modalConfirmarEliminar").length === 0) {
        console.error("ERROR: El modal de confirmación de eliminación no existe en el DOM");
        
        // Si no existe el modal, usar confirm() nativo como fallback
        if (confirm("¿Estás seguro de que deseas eliminar " + (tipo === "carpeta" ? "la carpeta" : "el archivo") + " '" + nombre + "'? Esta acción no se puede deshacer.")) {
            // Crear un formulario dinámico para enviar la solicitud
            var form = document.createElement("form");
            form.method = "POST";
            form.action = "/Sistema-NASv3/Src/funciones/eliminar.php";
            
            var inputRuta = document.createElement("input");
            inputRuta.type = "hidden";
            inputRuta.name = "ruta";
            inputRuta.value = ruta;
            form.appendChild(inputRuta);
            
            var inputTipo = document.createElement("input");
            inputTipo.type = "hidden";
            inputTipo.name = "tipo";
            inputTipo.value = tipo;
            form.appendChild(inputTipo);
            
            var inputAccion = document.createElement("input");
            inputAccion.type = "hidden";
            inputAccion.name = "accion";
            inputAccion.value = "eliminar";
            form.appendChild(inputAccion);
            
            document.body.appendChild(form);
            form.submit();
        }
        return;
    }
    
    // Obtener referencias a los elementos del formulario
    const rutaInput = document.getElementById("rutaEliminar");
    const tipoInput = document.getElementById("tipoElemento");
    const nombreElemento = document.getElementById("nombreElementoEliminar");
    
    // Verificar que los elementos existan antes de usarlos
    if (!rutaInput || !tipoInput || !nombreElemento) {
        console.error("ERROR: Algunos elementos del modal de eliminación no existen en el DOM");
        alert("Error: No se pudo preparar el formulario de eliminación. Recargue la página e intente nuevamente.");
        return;
    }
    
    // Establecer valores en el formulario
    rutaInput.value = ruta || "";
    tipoInput.value = tipo || "";
    
    // Mostrar el nombre del elemento a eliminar
    nombreElemento.textContent = nombre || "Desconocido";
    
    // Actualizar el título del modal según el tipo de elemento
    const modalTitle = document.getElementById("modalConfirmarEliminarLabel");
    if (modalTitle) {
        modalTitle.textContent = "Confirmar eliminación de " + (tipo === "carpeta" ? "carpeta" : "archivo");
    }
    
    // Abrir el modal usando jQuery para mayor compatibilidad
    $("#modalConfirmarEliminar").modal("show");
}

// Función para abrir el modal de renombrar carpetas
function abrirModalRenombrar(tipo, ruta, nombre) {
    console.log("Abriendo modal para renombrar carpeta:", ruta, nombre);
    
    // Verificar si el modal existe
    if ($("#modalRenombrar").length === 0) {
        console.error("ERROR: El modal de renombrar carpeta no existe en el DOM");
        alert("Error: No se pudo abrir el modal de renombrar carpeta. Recargue la página e intente nuevamente.");
        return;
    }
    
    // Obtener referencias a los elementos del formulario
    const rutaInput = document.getElementById("rutaRenombrar");
    const nombreInput = document.getElementById("nuevo_nombre");
    const rutaActualDiv = document.getElementById("rutaActual");
    
    // Verificar que los elementos existan antes de usarlos
    if (!rutaInput) {
        console.error("ERROR: El elemento rutaRenombrar no existe en el DOM");
        alert("Error: No se pudo encontrar el campo de ruta. Recargue la página e intente nuevamente.");
        return;
    }
    
    if (!nombreInput) {
        console.error("ERROR: El elemento nuevo_nombre no existe en el DOM");
        alert("Error: No se pudo encontrar el campo de nombre. Recargue la página e intente nuevamente.");
        return;
    }
    
    // Establecer valores en el formulario
    rutaInput.value = ruta || "";
    nombreInput.value = nombre || "";
    
    // Mostrar la ruta actual con formato si el elemento existe
    if (rutaActualDiv) {
        if (ruta && ruta.trim() !== "") {
            rutaActualDiv.innerHTML = "<strong>Ruta:</strong> <span class=\"text-success\">" + ruta + "</span>";
        } else {
            rutaActualDiv.innerHTML = "<strong>Ruta:</strong> <span style=\"color:red\">¡VACÍA! No se podrá renombrar.</span>";
        }
    }
    
    // Abrir el modal usando jQuery para mayor compatibilidad
    $("#modalRenombrar").modal("show");
}

// Función específica para preparar el modal de renombrar archivos
function abrirModalRenombrarArchivo(ruta, nombre) {
    console.log("Abriendo modal para renombrar archivo:", ruta, nombre);
    
    // Verificar si el modal existe
    if ($("#modalRenombrarArchivo").length === 0) {
        console.error("ERROR: El modal de renombrar archivo no existe en el DOM");
        alert("Error: No se pudo abrir el modal de renombrar archivo. Recargue la página e intente nuevamente.");
        return;
    }
    
    // Obtener referencias a los elementos del formulario
    const rutaInput = document.getElementById("rutaRenombrarArchivo");
    const nombreInput = document.getElementById("nuevo_nombre_archivo");
    const rutaActualDiv = document.getElementById("rutaActualArchivo");
    
    // Verificar que los elementos existan antes de usarlos
    if (!rutaInput) {
        console.error("ERROR: El elemento rutaRenombrarArchivo no existe en el DOM");
        alert("Error: No se pudo encontrar el campo de ruta. Recargue la página e intente nuevamente.");
        return;
    }
    
    if (!nombreInput) {
        console.error("ERROR: El elemento nuevo_nombre_archivo no existe en el DOM");
        alert("Error: No se pudo encontrar el campo de nombre. Recargue la página e intente nuevamente.");
        return;
    }
    
    // Establecer valores en el formulario
    rutaInput.value = ruta || "";
    
    // Extraer el nombre sin la extensión para facilitar el renombrado
    let nombreSinExtension = nombre || "";
    const ultimoPunto = nombreSinExtension.lastIndexOf(".");
    if (ultimoPunto > 0) {
        nombreSinExtension = nombreSinExtension.substring(0, ultimoPunto);
    }
    
    nombreInput.value = nombreSinExtension;
    
    // Mostrar la ruta actual con formato si el elemento existe
    if (rutaActualDiv) {
        if (ruta && ruta.trim() !== "") {
            rutaActualDiv.innerHTML = "<strong>Ruta:</strong> <span class=\"text-success\">" + ruta + "</span>";
        } else {
            rutaActualDiv.innerHTML = "<strong>Ruta:</strong> <span style=\"color:red\">¡VACÍA! No se podrá renombrar.</span>";
        }
    }
    
    // Abrir el modal usando jQuery para mayor compatibilidad
    $("#modalRenombrarArchivo").modal("show");
}

// Función para configurar todos los botones de acciones
function configurarBotonesAcciones() {
    console.log("Configurando todos los botones de acciones...");
    
    // Configurar botones de eliminar
    configurarBotonesEliminar();
    
    // Configurar botones de renombrar
    configurarBotonesRenombrar();
    
    // Si estamos en una vista de carpeta, aplicar configuración especial
    if (window.location.href.indexOf("carpeta=") > -1) {
        setTimeout(function() {
            configurarBotonesEliminarEspecial();
            configurarBotonesCarpetaEspecial();
        }, 500);
    }
}

// Función para configurar los botones de eliminar
function configurarBotonesEliminar() {
    // Seleccionar todos los botones con clase btn-eliminar o btn-danger
    var botones = document.querySelectorAll(".btn-eliminar, .btn-danger");
    var contador = 0;
    
    for (var i = 0; i < botones.length; i++) {
        var btn = botones[i];
        var ruta = btn.getAttribute("data-ruta");
        var nombre = btn.getAttribute("data-nombre");
        var tipo = btn.getAttribute("data-tipo");
        
        if (ruta && nombre) {
            // Reemplazar el evento onclick existente
            btn.setAttribute("onclick", "confirmarEliminar('" + ruta + "', '" + nombre + "', '" + (tipo || "archivo") + "'); return false;");
            
            // También añadir un evento de clic usando addEventListener para mayor compatibilidad
            (function(b, r, n, t) {
                b.addEventListener("click", function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    confirmarEliminar(r, n, t || "archivo");
                    return false;
                });
            })(btn, ruta, nombre, tipo);
            
            contador++;
        }
    }
    
    console.log("Configurados " + contador + " botones de eliminar");
}

// Función para configurar los botones de renombrar
function configurarBotonesRenombrar() {
    // Seleccionar todos los botones con clases relacionadas con renombrar
    var botones = document.querySelectorAll(".btn-editar, .btn-editar-carpeta, .btn-editar-archivo, .renombrar-carpeta-btn, .renombrar-archivo-btn, .btn-outline-primary");
    var contador = 0;
    
    for (var i = 0; i < botones.length; i++) {
        var btn = botones[i];
        var ruta = btn.getAttribute("data-ruta");
        var nombre = btn.getAttribute("data-nombre");
        var tipo = btn.getAttribute("data-tipo");
        
        if (ruta && nombre) {
            // Determinar si es carpeta o archivo basado en clases o atributos
            if (tipo === "carpeta" || btn.classList.contains("btn-editar-carpeta") || btn.classList.contains("renombrar-carpeta-btn")) {
                // Configurar botón para carpetas
                btn.setAttribute("onclick", "abrirModalRenombrar('carpeta', '" + ruta + "', '" + nombre + "'); return false;");
                
                // También añadir un evento de clic usando addEventListener para mayor compatibilidad
                (function(b, r, n) {
                    b.addEventListener("click", function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        abrirModalRenombrar('carpeta', r, n);
                        return false;
                    });
                })(btn, ruta, nombre);
                
                contador++;
            } else {
                // Configurar botón para archivos
                btn.setAttribute("onclick", "abrirModalRenombrarArchivo('" + ruta + "', '" + nombre + "'); return false;");
                
                // También añadir un evento de clic usando addEventListener para mayor compatibilidad
                (function(b, r, n) {
                    b.addEventListener("click", function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        abrirModalRenombrarArchivo(r, n);
                        return false;
                    });
                })(btn, ruta, nombre);
                
                contador++;
            }
        }
    }
    
    console.log("Configurados " + contador + " botones de renombrar");
}

// Función específica para configurar los botones en la vista de carpetas (eliminar)
function configurarBotonesEliminarEspecial() {
    // Seleccionar todos los botones que contengan el texto "Eliminar"
    var botones = document.querySelectorAll("button:contains('Eliminar')");
    var contador = 0;
    
    for (var i = 0; i < botones.length; i++) {
        var btn = botones[i];
        
        // Verificar si el botón contiene el texto "Eliminar"
        if (btn.textContent.indexOf("Eliminar") > -1) {
            var ruta = btn.getAttribute("data-ruta");
            var nombre = btn.getAttribute("data-nombre");
            var tipo = btn.getAttribute("data-tipo");
            
            if (ruta && nombre) {
                // Reemplazar el evento onclick existente
                btn.setAttribute("onclick", "confirmarEliminar('" + ruta + "', '" + nombre + "', '" + (tipo || "archivo") + "'); return false;");
                
                // También añadir un evento de clic usando addEventListener para mayor compatibilidad
                (function(b, r, n, t) {
                    b.addEventListener("click", function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        confirmarEliminar(r, n, t || "archivo");
                        return false;
                    });
                })(btn, ruta, nombre, tipo);
                
                contador++;
            }
        }
    }
    
    console.log("Configurados " + contador + " botones de eliminar en vista especial");
}

// Función específica para configurar los botones en la vista de carpetas (renombrar)
function configurarBotonesCarpetaEspecial() {
    // Seleccionar todos los botones que contengan el texto "Renombrar"
    var botones = document.querySelectorAll("button:contains('Renombrar')");
    var contador = 0;
    
    for (var i = 0; i < botones.length; i++) {
        var btn = botones[i];
        
        // Verificar si el botón contiene el texto "Renombrar"
        if (btn.textContent.indexOf("Renombrar") > -1) {
            var ruta = btn.getAttribute("data-ruta");
            var nombre = btn.getAttribute("data-nombre");
            var tipo = btn.getAttribute("data-tipo");
            
            if (ruta && nombre) {
                // Determinar si es carpeta o archivo basado en clases o atributos
                if (tipo === "carpeta" || btn.classList.contains("btn-editar-carpeta") || btn.classList.contains("renombrar-carpeta-btn")) {
                    // Configurar botón para carpetas
                    btn.setAttribute("onclick", "abrirModalRenombrar('carpeta', '" + ruta + "', '" + nombre + "'); return false;");
                    
                    // También añadir un evento de clic usando addEventListener para mayor compatibilidad
                    (function(b, r, n) {
                        b.addEventListener("click", function(e) {
                            e.preventDefault();
                            e.stopPropagation();
                            abrirModalRenombrar('carpeta', r, n);
                            return false;
                        });
                    })(btn, ruta, nombre);
                    
                    contador++;
                } else {
                    // Configurar botón para archivos
                    btn.setAttribute("onclick", "abrirModalRenombrarArchivo('" + ruta + "', '" + nombre + "'); return false;");
                    
                    // También añadir un evento de clic usando addEventListener para mayor compatibilidad
                    (function(b, r, n) {
                        b.addEventListener("click", function(e) {
                            e.preventDefault();
                            e.stopPropagation();
                            abrirModalRenombrarArchivo(r, n);
                            return false;
                        });
                    })(btn, ruta, nombre);
                    
                    contador++;
                }
            }
        }
    }
    
    console.log("Configurados " + contador + " botones de renombrar en vista especial");
}

// Función auxiliar para jQuery para seleccionar elementos por texto
jQuery.expr[':'].contains = function(a, i, m) {
    return jQuery(a).text().toUpperCase().indexOf(m[3].toUpperCase()) >= 0;
};

// Ejecutar cuando el DOM esté completamente cargado
document.addEventListener("DOMContentLoaded", function() {
    console.log("DOM cargado, configurando botones de acciones...");
    configurarBotonesAcciones();
    
    // Configurar validación para el formulario de carpetas
    const formRenombrar = document.getElementById("formRenombrar");
    if (formRenombrar) {
        formRenombrar.addEventListener("submit", function(e) {
            const rutaInput = document.getElementById("rutaRenombrar");
            if (!rutaInput.value || rutaInput.value.trim() === "") {
                e.preventDefault();
                alert("Error: No se pudo obtener la ruta de la carpeta a renombrar.");
                return false;
            }
            return true;
        });
    }
    
    // Configurar validación para el formulario de archivos
    const formRenombrarArchivo = document.getElementById("formRenombrarArchivo");
    if (formRenombrarArchivo) {
        formRenombrarArchivo.addEventListener("submit", function(e) {
            const rutaInput = document.getElementById("rutaRenombrarArchivo");
            if (!rutaInput.value || rutaInput.value.trim() === "") {
                e.preventDefault();
                alert("Error: No se pudo obtener la ruta del archivo a renombrar.");
                return false;
            }
            return true;
        });
    }
});

// También configurar los botones cuando la ventana termine de cargar
window.addEventListener("load", function() {
    console.log("Ventana cargada, reconfigurando botones de acciones...");
    setTimeout(configurarBotonesAcciones, 500);
});

// Reconfigurar botones después de cada cambio en el DOM
// Esto es útil para cuando se cargan nuevos elementos dinámicamente
const observer = new MutationObserver(function(mutations) {
    mutations.forEach(function(mutation) {
        if (mutation.addedNodes.length > 0) {
            setTimeout(configurarBotonesAcciones, 500);
        }
    });
});

// Iniciar la observación del DOM
observer.observe(document.body, { childList: true, subtree: true });
