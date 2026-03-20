/**
 * Script para manejar la funcionalidad de renombrar archivos y carpetas
 */

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
    
    if (!rutaActualDiv) {
        console.error("ERROR: El elemento rutaActual no existe en el DOM");
        // Continuar sin mostrar la ruta actual
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
    
    if (!rutaActualDiv) {
        console.error("ERROR: El elemento rutaActualArchivo no existe en el DOM");
        // Continuar sin mostrar la ruta actual
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
            rutaActualDiv.innerHTML = "<strong>Archivo:</strong> <span class=\"text-success\">" + nombre + "</span>";
        } else {
            rutaActualDiv.innerHTML = "<strong>Ruta:</strong> <span style=\"color:red\">¡VACÍA! No se podrá renombrar.</span>";
        }
    }
    
    // Abrir el modal usando jQuery para mayor compatibilidad
    $("#modalRenombrarArchivo").modal("show");
}

// Función para configurar todos los botones de renombrar
function configurarBotonesRenombrar() {
    console.log("Configurando botones de renombrar...");
    
    // Para botones de carpetas
    var botonesCarpeta = document.querySelectorAll(".btn-editar-carpeta");
    console.log("Botones de carpeta encontrados:", botonesCarpeta.length);
    
    for (var i = 0; i < botonesCarpeta.length; i++) {
        var btn = botonesCarpeta[i];
        var ruta = btn.getAttribute("data-ruta");
        var nombre = btn.getAttribute("data-nombre");
        
        // Añadir evento click directamente
        btn.onclick = (function(r, n) {
            return function(e) {
                e.preventDefault();
                e.stopPropagation();
                console.log("Click en botón de carpeta:", r, n);
                abrirModalRenombrar('carpeta', r, n);
                return false;
            };
        })(ruta, nombre);
    }
    
    // Para botones de archivos
    var botonesArchivo = document.querySelectorAll(".btn-editar-archivo");
    console.log("Botones de archivo encontrados:", botonesArchivo.length);
    
    for (var i = 0; i < botonesArchivo.length; i++) {
        var btn = botonesArchivo[i];
        var ruta = btn.getAttribute("data-ruta");
        var nombre = btn.getAttribute("data-nombre");
        
        // Añadir evento click directamente
        btn.onclick = (function(r, n) {
            return function(e) {
                e.preventDefault();
                e.stopPropagation();
                console.log("Click en botón de archivo:", r, n);
                abrirModalRenombrarArchivo(r, n);
                return false;
            };
        })(ruta, nombre);
    }
    
    console.log("Configuración de botones completada");
}

// Ejecutar cuando el DOM esté completamente cargado
document.addEventListener("DOMContentLoaded", function() {
    console.log("DOM cargado, configurando botones...");
    
    // Configurar los botones iniciales
    configurarBotonesRenombrar();
    
    // Configurar validación para el formulario de carpetas
    const formRenombrar = document.getElementById("formRenombrar");
    if (formRenombrar) {
        formRenombrar.addEventListener("submit", function(e) {
            const rutaInput = document.getElementById("rutaRenombrar");
            console.log("Formulario de carpeta enviado. Valor de ruta:", rutaInput.value);
            
            if (!rutaInput.value || rutaInput.value.trim() === "") {
                e.preventDefault();
                alert("Error: No se pudo obtener la ruta de la carpeta a renombrar.");
                console.error("Envío del formulario bloqueado: ruta vacía");
                return false;
            }
            console.log("Formulario de carpeta enviado correctamente");
            return true;
        });
    }
    
    // Configurar validación para el formulario de archivos
    const formRenombrarArchivo = document.getElementById("formRenombrarArchivo");
    if (formRenombrarArchivo) {
        formRenombrarArchivo.addEventListener("submit", function(e) {
            const rutaInput = document.getElementById("rutaRenombrarArchivo");
            console.log("Formulario de archivo enviado. Valor de ruta:", rutaInput.value);
            
            if (!rutaInput.value || rutaInput.value.trim() === "") {
                e.preventDefault();
                alert("Error: No se pudo obtener la ruta del archivo a renombrar.");
                console.error("Envío del formulario bloqueado: ruta vacía");
                return false;
            }
            console.log("Formulario de archivo enviado correctamente");
            return true;
        });
    }
    
    // Botones de prueba
    const testModalCarpeta = document.getElementById("testModalCarpeta");
    if (testModalCarpeta) {
        testModalCarpeta.addEventListener("click", function() {
            abrirModalRenombrar("carpeta", "test/ruta", "nombre_test");
        });
    }
    
    const testModalArchivo = document.getElementById("testModalArchivo");
    if (testModalArchivo) {
        testModalArchivo.addEventListener("click", function() {
            abrirModalRenombrarArchivo("test/ruta/archivo.txt", "archivo.txt");
        });
    }
});

// También configurar los botones cuando la ventana termine de cargar
window.addEventListener("load", function() {
    console.log("Ventana cargada, reconfigurando botones...");
    configurarBotonesRenombrar();
    
    // Verificar si estamos en una vista de carpeta
    if (window.location.href.indexOf("carpeta=") > -1) {
        console.log("Detectada vista de carpeta, aplicando configuración especial...");
        setTimeout(configurarBotonesCarpetaEspecial, 500);
    }
});

// Función específica para configurar los botones en la vista de carpetas
function configurarBotonesCarpetaEspecial() {
    console.log("Configurando botones en vista de carpeta...");
    
    // Seleccionar todos los botones con clase btn-outline-primary que contengan el texto "Renombrar"
    var botones = document.querySelectorAll(".btn-outline-primary");
    var botonesConfigurados = 0;
    
    for (var i = 0; i < botones.length; i++) {
        var btn = botones[i];
        
        // Verificar si el botón contiene el texto "Renombrar"
        if (btn.textContent.indexOf("Renombrar") > -1) {
            var ruta = btn.getAttribute("data-ruta");
            var nombre = btn.getAttribute("data-nombre");
            var tipo = btn.getAttribute("data-tipo");
            
            if (ruta && nombre) {
                // Determinar si es carpeta o archivo basado en clases o atributos
                if (tipo === "carpeta" || btn.classList.contains("btn-editar-carpeta")) {
                    btn.onclick = (function(r, n) {
                        return function(e) {
                            e.preventDefault();
                            e.stopPropagation();
                            console.log("Click en botón de carpeta (vista especial):", r, n);
                            abrirModalRenombrar('carpeta', r, n);
                            return false;
                        };
                    })(ruta, nombre);
                    botonesConfigurados++;
                } else {
                    btn.onclick = (function(r, n) {
                        return function(e) {
                            e.preventDefault();
                            e.stopPropagation();
                            console.log("Click en botón de archivo (vista especial):", r, n);
                            abrirModalRenombrarArchivo(r, n);
                            return false;
                        };
                    })(ruta, nombre);
                    botonesConfigurados++;
                }
            }
        }
    }
    
    console.log("Botones configurados en vista especial: " + botonesConfigurados);
    
    // Si no se configuraron botones, intentar de nuevo en 1 segundo
    if (botonesConfigurados === 0) {
        console.log("No se encontraron botones, reintentando en 1 segundo...");
        setTimeout(configurarBotonesCarpetaEspecial, 1000);
    }
}
